<?php

require_once __DIR__ . '/../bootstrap.php';

$script = basename(__FILE__);

use Apd\Parser;
use Apd\Export\Markdown;

if ($argc < 2) {
    exit("Usage: {$script} <path to php files> [--wrap-html]\n");
}

if (isset($argv[2]) && $argv[2] != '--wrap-html') {
    exit("Usage: {$script} <path to php files> [--wrap-html]\n");
}

$path = realpath($argv[1]);
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
