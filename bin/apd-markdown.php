<?php

require_once __DIR__ . '/../bootstrap.php';

$script = basename(__FILE__);

use Apd\Parser;
use Apd\Export\Markdown;

if ($argc != 2) {
    exit("Usage: {$script} <path to php files>\n");
}

$path = realpath($argv[1]);
$parser = new Parser();
$parser->path($path);
$parser->parse();
$export = new Markdown();

$markdown = $export->export($parser);
echo $markdown;