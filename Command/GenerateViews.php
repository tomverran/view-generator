<?php
namespace tomverran\Viewgen\Command;
use RecursiveDirectoryIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use tomverran\Viewgen\Library\DiskFileSystem;
use tomverran\Viewgen\Library\PathExpander;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Created by PhpStorm.
 * User: tom
 * Date: 17/11/14
 * Time: 21:26
 */

class GenerateViews extends Command
{
    protected function configure()
    {
        $this->setName('generate-views')
             ->setDescription('Generates objects for code completion view creation')
             ->addArgument('input', InputArgument::REQUIRED, 'Directories to find views in' )
             ->addArgument('output', InputArgument::REQUIRED, 'Directories to place objects in, relative' );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inDir = getcwd() . DIRECTORY_SEPARATOR . $input->getArgument( 'input' );
        $outDir = $input->getArgument( 'output' );

        //find every path to recurse down into, expanding wildcards
        $paths = ( new PathExpander( new DiskFileSystem ) )->expand( $inDir );

        foreach( $paths as $path ) {
            $path = $this->realpath( $path );
            $viewScripts = $this->findViewScripts( $path );
            foreach ( $viewScripts as $containingFolder => $scripts ) {
                $outPath = $this->realpath( $path . DIRECTORY_SEPARATOR . $outDir );
                $output->writeln('writing scripts under ' . $path . ' to ' . $outPath );
                $this->generateViewObjects( $outPath, $scripts );
            }
        }
    }

    /**
     * @param $from
     * @return array
     */
    protected function findViewScripts( $from )
    {
        $byContainingFolder = [];
        $directoryIterator = new RecursiveDirectoryIterator( $from );

        foreach ( new \RecursiveIteratorIterator( $directoryIterator ) as $file ) {
            if ( strpos( $file->getFilename(), '.phtml' ) !== false ) {

                $containingFolder = realpath( dirname( $file->getPathname() ) . '/../' );
                $relativePath = str_replace( $containingFolder, '', $file->getPathname() );

                if ( !isset( $byContainingFolder[$containingFolder] ) ) {
                    $byContainingFolder[$containingFolder] = [];
                }

                $byContainingFolder[$containingFolder][] = $relativePath;
            }
        }

        return $byContainingFolder;
    }

    /**
     * Get a list of paths -> namespaces
     * @return mixed
     */
    protected function getNamespaces()
    {
        if ( !file_exists( 'composer.json' ) ) {
            throw new \LogicException('Please run this on your project root containing composer.json');
        }

        $projectComposer = json_decode( file_get_contents('composer.json'), true );
        $autoLoad = $projectComposer['autoload'];

        if ( isset( $autoLoad['psr-0'] ) || !isset( $autoLoad['psr-4'] ) ) {
            throw new \LogicException( 'I am snobbish and only like PSR-4' );
        }

        $pathsToNamespaces = [];
        foreach ( $autoLoad['psr-4'] as $ns => $path ) {
            $pathsToNamespaces[realpath(getcwd()) . DIRECTORY_SEPARATOR . $path] = $ns;
        }

        return $pathsToNamespaces;
    }

    /**
     * Get a namespace from an absolute file path
     * @param string $path The file path
     * @return mixed
     */
    private function getNamespaceFromPath( $path )
    {
        foreach ( $this->getNamespaces() as $nsPath => $namespace ) {
            $path = str_replace( $nsPath, $namespace, $path );
        }
        return str_replace( DIRECTORY_SEPARATOR, '\\', $path );
    }

    /**
     * Get the name for a class from its path
     * @param string $path A path to the file to make a class for
     * @return string the class name
     */
    private function getClassNameFromPath( $path )
    {
        return basename( $path, '.phtml' );
    }

    /**
     * Get fields used in a given file
     * @param string $file The absolute filename
     * @return string[]
     */
    private function getFieldsUsedInFile( $file )
    {
        $linesUsedByField = [];
        $lines = explode( "\n", file_get_contents( $file ) );

        foreach( $lines as $lineNumber => $line ) {
            $matches = [];
            preg_match_all( '/\$this->([A-Z0-9_]+)/i', $line, $matches );
            foreach ( $matches[1] as $variable ) {
                if (!isset( $linesUsedByField[$variable])) {
                    $linesUsedByField[$variable] = [];
                }
                $actualLine = $lineNumber + 1;
                $linesUsedByField[$variable][$actualLine] = $actualLine;
            }

        }

        return $linesUsedByField;
    }

    /**
     * Given some named fields generate code that assigns some local variables
     * with the same names as the fields to the fields
     * @param string[] $fields
     * @return string
     */
    private function generateFieldAssignments($fields)
    {
        $buffer = [];
        foreach ( $fields as $field ) {
            $buffer[] = '$this->' . $field . ' = $' . $field . ';';
        }
        return implode( "\n", $buffer );
    }

    /**
     * Generate a method to render the given ViewScript
     * @param string $script The relative path to the script
     * @return string
     */
    private function generateRenderMethod( $script )
    {
        $buffer = [
            'ob_start();',
            'require __DIR__ . "/..' . $script . '";',
            'return ob_get_clean();'
        ];
        return implode( "\n", $buffer );
    }

    /**
     * Generate view objects
     * @param string $containingFolder Where to put the objects
     * @param array $scripts An array of script paths relative to the directory
     */
    private function generateViewObjects( $containingFolder, $scripts )
    {
        $namespace = $this->getNamespaceFromPath( $containingFolder );

        //loop through scripts, gen classes
        foreach ( $scripts as $script ) {

            $className = $this->getClassNameFromPath( $script );
            $gen = new ClassGenerator( $className, $namespace, ClassGenerator::FLAG_FINAL );
            $gen->setDocBlock( new DocBlockGenerator( 'Generated on ' . date( 'd M Y' ) ) );
            $fields = $this->getFieldsUsedInFile( $containingFolder . '/..' . $script );

            foreach ( $fields as $field => $lines ) {
                $gen->addProperty( $field, null, PropertyGenerator::FLAG_PRIVATE );
                $gen->getProperty($field)->setDocBlock('Seen on line(s): ' . implode( ', ', $lines ) );
            }

            $fieldAssigningCode = $this->generateFieldAssignments( array_keys( $fields ) );
            $viewRenderingCode = $this->generateRenderMethod( $script );

            $gen->addMethod('__construct', array_keys( $fields ), MethodGenerator::FLAG_PUBLIC, $fieldAssigningCode, 'Construct this ' . $className );
            $gen->addMethod('__toString', [], MethodGenerator::FLAG_PUBLIC, $viewRenderingCode, 'Render this script' );

            $withAnnoyingPhpTag = "<?php\n" . $gen->generate();
            file_put_contents( $containingFolder . DIRECTORY_SEPARATOR . $className . '.php', $withAnnoyingPhpTag );
        }
    }

    private function realpath($rawPath)
    {
        $out = realpath( $rawPath );
        if ( !$out ) {
            throw new \LogicException('Bad path: ' . $rawPath );
        }
        return $out;
    }
} 