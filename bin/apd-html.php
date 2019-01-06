<?php

require_once __DIR__ . '/../bootstrap.php';

$script = basename(__FILE__);

use Apd\Parser;
use Apd\Export\Markdown;

if ($argc < 2) {
    error_log("Usage: {$script} <path to php files> [--wrap-html]\n");
    exit(1);
}

if (isset($argv[2]) && $argv[2] != '--wrap-html') {
    error_log("Usage: {$script} <path to php files> [--wrap-html]\n");
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
$export = new Markdown();
$markdown = $export->export($parser);

$parser = new \cebe\markdown\GithubMarkdown()   ;
$parser->html5 = true;
$body = $parser->parse($markdown);
if (isset($argv[2]) && $argv[2] == '--wrap-html') {
    ob_start();
    require APD_ROOT . '/templates/output.phtml';
    $html = ob_get_clean();
    echo $html;
} else {
    echo $body;
}
