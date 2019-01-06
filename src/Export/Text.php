<?php

namespace Apd\Export;

use Apd\Parser;
use Apd\Exportable;
use Apd\Structure\Field;
use Apd\Structure\Object;

/**
 * Class for exporting text
 * @package Apd\Export
 */
class Text implements Exportable {

    const OBJECT_INDENT = 2;

    /**
     * Return text
     * @param Parser $parser
     * @param string $baseUrl
     * @param string $version
     * @return string
     */
    public function export(Parser $parser, $baseUrl = '', $version = '') {
        $text = '';
        $projects = $parser->getProjects();
        //        print_r($projects);die();
        foreach ($projects as $project) {
            if ($project->title) {
                $text .= sprintf("%s\n", strtoupper($project->title));
            }
            if ($version) {
                $project->version = $version;
            }
            if ($project->version) {
                $text .= sprintf("Version: %s\n", $project->version);
            }

            if ($baseUrl) {
                $text .= sprintf("Base url: %s\n", $baseUrl);
            }

            if ($project->description) {
                $text .= sprintf("%s\n", $project->description);
            }

            if ($project->classes) {
                $text .= "\nDATA TYPES\n\n";
            }
            foreach ($project->classes as $object) {
                $text .= $this->renderClass($object);
            }
            foreach ($project->sections as $section) {
                $text .= sprintf("\n");
                if ($section->title) {
                    $text .= sprintf("%s\n", strtoupper($section->title));
                }
                if ($section->description) {
                    $text .= sprintf("\n%s\n", trim($section->description));
                }
                $text .= sprintf("\n");
                foreach ($section->entries as $entry) {
                    $text .= sprintf("%s %s%s%s\n", strtoupper($entry->method), $baseUrl, $baseUrl, $entry->uri);
                    $text .= sprintf("%s\n", $entry->title);
                    if ($entry->description) {
                        $text .= sprintf("\n%s\n", trim($entry->description));
                    }
                    $text .= sprintf("\nRequest:\n");
                    $text .= $this->renderFields($entry->request);
                    $text .= sprintf("\nResponse:\n");
                    $text .= $this->renderFields($entry->response);
                }
            }
        }
        return $text;
    }

    protected function renderObject(Field $object, $required = false, $indent = 0) {
        $text = "";
        $indentStr = str_pad('', $indent, ' ');
        $text .= sprintf("%s%s %s {%s%s\n", $indentStr, $object->type, $object->name, $object->title ? " \"{$object->title}\"" : '',
            $required ? ' (required)' : '');
        $text .= $this->renderFields($object->fields, $indent);
        $text .= sprintf("%s}\n", $indentStr);
        return $text;
    }

    protected function renderFields(array $fields, $indent = 0) {
        $text = '';
        $indentStr = str_pad('', $indent, ' ');
        foreach ($fields as $field) {
//            if ($field->description) {
//                $text .= sprintf("\n  %s\"%s\"\n", $indentStr, trim($field->description));
//            }
            $default = '';
            if ($field->defaultValue !== null) {
                $default = "(default=\"{$field->defaultValue}\")";
            }
            if ($field->type == 'object') {
                $text .= $this->renderObject($field, $field->isRequired, $indent + self::OBJECT_INDENT);
            } elseif ($field->isRequired) {
                $text .= sprintf("  %s%s %s (required) %s\n", $indentStr, $field->type, $field->name, $field->title);
            } else {
                $text .= sprintf("  %s%s %s %s %s\n", $indentStr, $field->type, $field->name, $default, $field->title);
            }
        }
        return $text;
    }

    protected function renderClass(Object $object) {
        $text = "";
        if ($object->title) {
            $text .= sprintf("%s\n", $object->title);
        }
        if ($object->description) {
            $text .= sprintf("%s\n", $object->description);
        }
        $text .= sprintf("\n  %s {\n", $object->name);
        $text .= $this->renderFields($object->fields, 2);
        $text .= "  }\n";
        return $text;
    }

}