<?php
/**
 * Created by PhpStorm.
 * User: tom
 * Date: 18/11/14
 * Time: 19:21
 */
namespace tomverran\Viewgen\Library;

interface FileSystem
{
    const ONLY_DIRECTORIES = 'pfft';

    public function getChildren( $path, $flag = false );

    public function directoryExists( $path );

    public function splitPath( $path );
} 