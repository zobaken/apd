<?php

namespace Tests\Apd;

use Apd\Parser;
use Apd\Export\Text;
use PHPUnit\Framework\TestCase;

/**
 * Class ParserTest
 * @package Tests\Apd
 */
class ParserTest extends TestCase
{

    public function testParser()
    {
        $path = APD_ROOT . '/tests';
        $parser = new Parser();
        $parser->path($path);
        $parser->parse();
        $export = new Text();
        $text = trim($export->export($parser));
        $sample = trim(file_get_contents(APD_ROOT . '/tests/result.txt'));
        $this->assertTrue($text == $sample, 'Text export check failed');
    }

}
