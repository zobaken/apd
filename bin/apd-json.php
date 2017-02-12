<?php

require_once __DIR__ . '/../bootstrap.php';

$script = basename(__FILE__);

use Apd\Parser;
use Apd\Export\Text;

if ($argc != 2) {
    exit("Usage: {$script} <path to php files>\n");
}

$path = realpath($argv[1]);
$parser = new Parser();
$parser->path($path);
$parser->parse();
echo json_encode($parser->getEndpoints(), JSON_PRETTY_PRINT);