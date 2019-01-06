<?php

namespace Apd\Export;

use Apd\Parser;
use Apd\Exportable;
use Apd\Structure\Field;
use Apd\Structure\Object;

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
        $projects = $parser->getProjects();
        foreach ($projects as $project) {
            if ($project->title) {
                $text .= sprintf("# %s\n", $project->title);
            }
            if ($version) {
                $project->version = $version;
            }
            if ($project->version) {
                $text .= sprintf("\nVersion: **%s**\n", $project->version);
            }
            if ($baseUrl) {
                $project->baseUrl = $baseUrl;
            }
            if ($project->baseUrl) {
                $text .= sprintf("\nBase Url: `%s`\n", $project->baseUrl);
            }
            if ($project->description) {
                $text .= sprintf("\n%s\n", $project->description);
            }
            if ($project->classes) {
                $text .= "## Data types\n";
            }
            foreach ($project->classes as $object) {
                $text .= $this->renderClass($object);
            }
            foreach ($project->sections as $section) {
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
                    $text .= sprintf("*%s* `%s%s`\n", strtoupper($entry->method), $baseUrl, $entry->uri);
                    if ($entry->description) {
                        $text .= sprintf("\n  %s\n", trim($entry->description));
                    }
                    $text .= sprintf("\n#### Request parameters\n");
                    $text .= sprintf("|Type|Name|Description|Required|Default value|\n");
                    $text .= sprintf("|---|---|---|---|---|\n");
                    foreach ($entry->request as $field) {
                        $text .= $this->renderField($field, '', 'request');
                    }
                    $text .= sprintf("\n#### Response fields\n");
                    $text .= sprintf("|Type|Name|Description|\n");
                    $text .= sprintf("|---|---|---|\n");
                    foreach ($entry->response as $field) {
                        $text .= $this->renderField($field, '', 'response');
                    }
                }
            }
        }
        return $text;
    }

    protected function renderField(Field $field, string $indent, string $type) {
        $text = '';
        if ($type == 'request') {
            $text .= sprintf("|_%s_|%s__%s__|%s|%s|%s|\n", $field->type, $indent, $field->name, $field->title, $field->isRequired ? '_required_' : '_optional_', $field->defaultValue);
        } else {
            $text .= sprintf("|_%s_|%s__%s__|%s|\n", $field->type, $indent, $field->name, $field->title);
        }
        if ($field->type == 'object' && $field->fields) {
            foreach ($field->fields as $fieldInner) {
                $text .= $this->renderField($fieldInner, $indent . $field->name . '/', $type);
            }
        }
        return $text;
    }

    protected function renderClass(Object $class) {
        $text = '';
        $text .= sprintf("### %s\n", $class->name);
        $text .= sprintf("#### %s\n", $class->title);
        if ($class->description) {
            $text .= sprintf("\n  %s\n\n", trim($class->description));
        }
        $text .= sprintf("|Type|Name|Description|\n");
        $text .= sprintf("|---|---|---|\n");
        foreach ($class->fields as $field) {
            $text .= $this->renderField($field, '', 'response');
        }
        return $text;
    }

    protected function renderObject(Field $object) {

    }

}