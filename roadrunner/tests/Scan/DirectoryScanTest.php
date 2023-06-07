<?php declare(strict_types=1);

namespace App\Scan;

use Test\TestCase;
use Test\Traits\HasJobsMock;

class DirectoryScanTest extends TestCase
{
    use HasJobsMock;

    public function testItWalksADirectoryIgnoringDotFiles(): void
    {
        $path = sys_get_temp_dir() . '/' . uniqid('DirectoryScanTest-');
        mkdir($path, 0777, true);
        touch($path . '/file-1.txt');
        touch($path . '/file-2.txt');
        touch($path . '/.dotfile-1');
        mkdir($path . '/subdir-1');
        touch($path . '/subdir-1/file-3.txt');
        touch($path . '/subdir-1/.dotfile-2');

        $this->injectQueueExpectation(3);

        $directory_scan = $this->container->get(DirectoryScan::class);

        $this->assertNull(
            $directory_scan->process($path)
        );
    }

    public function testItDoesNothingIfPathDoesNotExist()
    {
        $directory_scan = $this->container->get(DirectoryScan::class);

        $this->assertNull(
            $directory_scan->process(sys_get_temp_dir() . '/' . uniqid('DirectoryScanTest-'))
        );
    }

    public function testItDoesNothingIfPathIsAFile()
    {
        $path = sys_get_temp_dir() . '/' . uniqid('DirectoryScanTest-');
        touch($path);

        $directory_scan = $this->container->get(DirectoryScan::class);

        $this->assertNull(
            $directory_scan->process($path)
        );
    }
}
