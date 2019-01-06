<?php

require_once __DIR__ . '/../bootstrap.php';

$script = basename(__FILE__);

use Apd\Parser;
use Apd\Export\Text;

if ($argc != 2) {
    error_log("Usage: {$script} <path to php files>\n");
    exit(1);
}

$path = realpath($argv[1]);

if (!$path) {
    error_log("Path not found\n");
    exit(1);
}

$parser = new Parser();
$parser->path($path);
$parser->parse();
echo json_encode($parser->getProjects(), JSON_PRETTY_PRINT);