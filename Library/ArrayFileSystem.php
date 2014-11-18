<?php
/**
 * Created by PhpStorm.
 * User: tom
 * Date: 18/11/14
 * Time: 19:22
 */
namespace tomverran\Viewgen\Library;

class ArrayFileSystem implements FileSystem
{
    private $files;

    public function __construct( array $files )
    {
        $this->files = $files;
    }

    public function directoryExists($path)
    {
        $currentArray = &$this->files;
        $pathParts = $this->splitPath( $path );
        foreach ( $pathParts as $currentPart ) {
            if ( !is_array( $currentArray[$currentPart] ) ) {
                return false;
            }
            $currentArray = &$currentArray[$currentPart];
        }
        return true;
    }

    public function splitPath($path)
    {
        return preg_split('/'.preg_quote( DIRECTORY_SEPARATOR , '/').'/', $path, -1, PREG_SPLIT_NO_EMPTY );
    }

    public function getChildren($path, $flag = false)
    {
        $currentArray = &$this->files;
        $pathParts = $this->splitPath( $path );
        foreach ( $pathParts as $currentPart ) {
            if ( !is_array( $currentArray[$currentPart] ) ) {
                return [];
            }
            $currentArray = &$currentArray[$currentPart];
        }
        return array_keys( $flag == FileSystem::ONLY_DIRECTORIES ? array_filter( $currentArray, 'is_array' ) : $currentArray );
    }
}