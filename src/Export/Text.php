<?php

namespace Apd\Export;

use Apd\Parser;
use Apd\Exportable;

/**
 * Class for exporting text
 * @package Apd\Export
 */
class Text implements Exportable {

    public function export(Parser $parser, $baseUrl = '', $version = '') {
        $text = '';
        $endpoints = $parser->getEndpoints();
        //        print_r($endpoints);die();
        foreach ($endpoints as $endpoint) {
            if ($endpoint->title) {
                $text .= sprintf("%s\n", $endpoint->title);
            }
            $text .= sprintf("\nEndpoint: \n%s/%s\n", $baseUrl, $endpoint->name);
            if ($version) {
                $text .= sprintf("Version: %s\n", $version);
            }
            foreach ($endpoint->sections as $section) {
                $text .= sprintf("\n");
                if ($section->title) {
                    $text .= sprintf("%s\n", $section->title);
                }
                if ($section->description) {
                    $text .= sprintf("\n%s\n", trim($section->description));
                }
                $text .= sprintf("\n");
                foreach ($section->entries as $entry) {
                    $text .= sprintf("%s:\n", $entry->title);
                    $text .= sprintf("%s %s/%s%s\n", strtoupper($entry->method), $baseUrl, $endpoint->name, $entry->uri);
                    if ($entry->description) {
                        $text .= sprintf("\n  %s\n", trim($entry->description));
                    }
                    $text .= sprintf("\nRequest:\n");
                    foreach ($entry->request as $field) {
                        if ($field->isRequired) {
                            $text .= sprintf("%s %s %s [required]\n", $field->type, $field->name, $field->title);
                        } else {
                            $text .= sprintf("%s %s %s\n", $field->type, $field->name, $field->title);
                        }
                    }
                    $text .= sprintf("\nResponse:\n");
                    $doField = function ($field, $indent) use (&$text, &$doField) {
                        $text .= sprintf("%s%s %s %s\n", $indent, $field->type, $field->name, $field->title);
                        if (($field->type == 'object' || $field->type == 'array') && $field->fields) {
                            foreach ($field->fields as $field) {
                                $doField($field, $indent . '  ');
                            }
                        }
                    };
                    foreach ($entry->response as $field) {
                        $doField($field, '');
                    }
                }
            }
        }
        return $text;
    }

}