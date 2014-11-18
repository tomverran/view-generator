<?php
/**
 * Created by PhpStorm.
 * User: tom
 * Date: 18/11/14
 * Time: 19:22
 */
namespace tomverran\Viewgen\Library;

class DiskFileSystem implements FileSystem
{

    public function getChildren($path, $flag = false)
    {
        $globFlag = $flag == FileSystem::ONLY_DIRECTORIES ? GLOB_ONLYDIR : null;
        return array_filter( glob('*', $globFlag ), function( $file ) {
            return strpos( $file, '.' ) !== 0;
        } );
    }

    public function directoryExists($path)
    {
        return is_dir( $path );
    }

    public function splitPath($path)
    {
        return preg_split('/'.preg_quote( DIRECTORY_SEPARATOR , '/').'/', $path, -1, PREG_SPLIT_NO_EMPTY );
    }
}