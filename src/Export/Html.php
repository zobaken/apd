<?php

namespace Apd\Export;

use Apd\Export\Markdown;
use Apd\Parser;
use Apd\Exportable;

/**
 * Class for exporting apd parsed data as simple html using cebe/markdown
 * @package Apd\Export
 */
class Html implements Exportable {

    /**
     * Export markdown
     *
     * @param Parser $parser
     * @param string $baseUrl
     * @param string $version
     * @return string
     */
    public function export(Parser $parser, $baseUrl = '', $version = '') {
        $markdownExport = new Markdown();
        $markdownExport->export($parser, $baseUrl, $version);
        $markdown = $markdownExport->export($parser);
        $parser = new \cebe\markdown\GithubMarkdown()   ;
        $parser->html5 = true;
        $html = $parser->parse($markdown);
        return $html;
    }

}