<?php

namespace Apd\Export;

use Apd\Parser;
use Apd\Exportable;

/**
 * Class for exporting apd parsed data as markdown syntax
 * @package Apd\Export
 */
class Markdown implements Exportable {

    /**
     * Export markdown
     *
     * @param Parser $parser
     * @param string $baseUrl
     * @param string $version
     * @return string
     */
    public function export(Parser $parser, $baseUrl = '', $version = '') {
        $text = '';
        $endpoints = $parser->getEndpoints();
        foreach ($endpoints as $endpoint) {
            if ($endpoint->title) {
                $text .= sprintf("# %s\n", $endpoint->title);
            }
            $text .= sprintf("\nEndpoint: `%s/%s`\n", $baseUrl, $endpoint->name);
            if ($version) {
                $text .= sprintf("Version: %s\n", $version);
            }
            foreach ($endpoint->sections as $section) {
                $text .= sprintf("\n");
                if ($section->title) {
                    $text .= sprintf("## %s\n", $section->title);
                }
                if ($section->description) {
                    $text .= sprintf("\n%s\n", trim($section->description));
                }
                $text .= sprintf("\n");
                foreach ($section->entries as $entry) {
                    $text .= sprintf("### %s\n", $entry->title);
                    $text .= sprintf("*%s* `%s/%s%s`\n", strtoupper($entry->method), $baseUrl, $endpoint->name, $entry->uri);
                    if ($entry->description) {
                        $text .= sprintf("\n  %s\n", trim($entry->description));
                    }
                    $doField = function($field, $indent, $type) use (&$text, &$doField) {
                        if ($type == 'request') {
                            $text .= sprintf("|_%s_|%s__%s__|%s|%s|%s|\n", $field->type, $indent, $field->name, $field->title, $field->isRequired ? '_required_' : '_optional_', $field->defaultValue);
                        } else {
                            $text .= sprintf("|_%s_|%s__%s__|%s|\n", $field->type, $indent, $field->name, $field->title);
                        }
                        if (($field->type == 'object' || $field->type == 'array') && $field->fields) {
                            foreach ($field->fields as $fieldInner) {
                                $doField($fieldInner, $indent . $field->name . '/', $type);
                            }
                        }
                    };
                    $text .= sprintf("\n#### Request parameters\n");
                    $text .= sprintf("|Type|Name|Description|Required|Default value|\n");
                    $text .= sprintf("|---|---|---|---|---|\n");
                    foreach ($entry->request as $field) {
                        $doField($field, '', 'request');
                    }
                    $text .= sprintf("\n#### Response fields\n");
                    $text .= sprintf("|Type|Name|Description|\n");
                    $text .= sprintf("|---|---|---|\n");
                    foreach ($entry->response as $field) {
                        $doField($field, '', 'response');
                    }
                }
            }
        }
        return $text;
    }

}