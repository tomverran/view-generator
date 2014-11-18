<?php
/**
 * Created by PhpStorm.
 * User: tom
 * Date: 18/11/14
 * Time: 19:06
 */
namespace tomverran\Viewgen\Library;

class PathExpander
{
    /**
     * @var FileSystem
     */
    private $fs;



    public function __construct( FileSystem $files )
    {
        $this->fs = $files;
    }

    protected function addSubdirectory( $subDir )
    {

    }

    private function isVisible( $directory )
    {
        return strpos( $directory, '.' ) !== 0;
    }

    public function expand( $path )
    {
        $fullPaths = ['/'];
        $pathParts = $this->fs->splitPath( $path );
        $pathPartCount = count( $pathParts );

        foreach ( $this->fs->splitPath( $path ) as $partIndex => $directory ) {
            $currentFullPaths = $fullPaths;

            foreach ( $currentFullPaths as $index => $fullPath ) {
                if ( $directory !=  '*' ) {
                    $fullPaths[$index] .=  $directory . DIRECTORY_SEPARATOR;
                } else {

                    //are we the last path part? If so we need files
                    $isLastPathPart = $partIndex == $pathPartCount - 1;
                    $flag = $isLastPathPart ? false : FileSystem::ONLY_DIRECTORIES;

                    //find the children of this directory then remove the parent dir
                    $subDirectories = $this->fs->getChildren( $fullPath, $flag );
                    unset( $fullPaths[$index] );

                    //add every parent/child combo
                    while ( !empty( $subDirectories ) ) {
                        $newFullPath = $fullPath . array_shift( $subDirectories );
                        $appendTrailingSlash = $this->fs->directoryExists( $newFullPath );
                        $fullPaths[] = $newFullPath . ( $appendTrailingSlash  ? DIRECTORY_SEPARATOR : '' );
                    }
                }
            }
        }
        return $fullPaths;
    }
} 