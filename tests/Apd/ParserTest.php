<?php

namespace Tests\Apd;

use PHPUnit\Framework\TestCase;
use Apd\Parser;
use Apd\Export\Text;

/**
 * Class ParserTest
 * @package Tests\Apd
 */
class ParserTest extends TestCase
{
    /**
     * Apd parser test
     *
     * @return void
     */
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
