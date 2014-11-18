<?php
/**
 * Created by PhpStorm.
 * User: tom
 * Date: 18/11/14
 * Time: 19:28
 */
use tomverran\Viewgen\Library\ArrayFileSystem;
use tomverran\Viewgen\Library\PathExpander;

require_once "../vendor/autoload.php";

class PathExpanderTest extends PHPUnit_Framework_TestCase
{
    private function getMockPathExpander()
    {
        $fsArr = [
            'home' => [
                'tom' => [
                    'files' => [
                        'cats.txt' => 'meow',
                        'dogs.txt' => 'woof'
                    ],
                    'documents' => [
                        'cats.txt' => 'meow document',
                        'dogs.txt' => 'woof document'
                    ]
                ]
            ]
        ];

        $fs = new ArrayFileSystem( $fsArr );
        return new PathExpander( $fs );
    }

    public function testNoExpansion()
    {
        $pathExpander = $this->getMockPathExpander();
        $expandedPaths = $pathExpander->expand( $originalPath = '/home/tom/files/' );
        $this->assertCount( 1, $expandedPaths );
        $this->assertEquals( $originalPath, reset( $expandedPaths ) );
    }

    public function testFileWildcard()
    {
        $pathExpander = $this->getMockPathExpander();
        $expandedPaths = $pathExpander->expand( '/home/tom/files/*' );
        $this->assertEquals( '/home/tom/files/cats.txt', array_shift( $expandedPaths ) );
        $this->assertEquals( '/home/tom/files/dogs.txt', array_shift( $expandedPaths ) );
        $this->assertEmpty( $expandedPaths );
    }

    public function testDirectoryWildcard()
    {
        $pathExpander = $this->getMockPathExpander();
        $expandedPaths = $pathExpander->expand( '/home/tom/*/*' );
        $this->assertCount( 4, $expandedPaths );

        $this->assertContains( '/home/tom/files/cats.txt', $expandedPaths );
        $this->assertContains( '/home/tom/files/dogs.txt', $expandedPaths );
        $this->assertContains( '/home/tom/documents/dogs.txt', $expandedPaths );
        $this->assertContains( '/home/tom/documents/cats.txt', $expandedPaths );
    }
} 