<?php

declare(strict_types=1);

namespace App\Parser;

use App\Exception\FilePathNotFoundException;
use App\Model\File;
use Test\TestCase;

class ParserTest extends TestCase
{
    public function testItThrowsIfFileDoesNotExist()
    {
        $this->expectException(FilePathNotFoundException::class);

        $this->container->get(Parser::class)->parse('/path/to/nowhere', 'hash');
    }

    public function testItExtractsAttributesFromAPicture()
    {
        $parser = $this->container->get(Parser::class);

        $path = __DIR__ . '/../fixtures/IMG_9970.jpeg';
        $file = $parser->parse(
            $path,
            'a8246e4f7f7344cc822270728fe69d42f433462160a8dc330572bbc3d4ac5b9c'
        );

        $this->assertInstanceOf(File::class, $file);
        $this->assertSame('IMG_9970.jpeg', $file->name);
        $this->assertSame(46293, $file->size);
        $this->assertSame($path, $file->path);
        $this->assertSame('image/jpeg', $file->mime);
        $this->assertSame('2021-11-21 16:33:06', $file->date);
        $this->assertSame(37.08, round($file->lat, 2));
        $this->assertSame(-7.66, round($file->lon, 2));
        $this->assertNull($file->removed_at);
    }
}
