<?php

namespace Apd;
use Apd\Structure\Project;
use Apd\Structure\Section;
use Apd\Structure\Field;
use Apd\Structure\Entry;
use Apd\Structure\Object;

/** Apd Parser */
class Parser {

    /** Detect comment pattern */
    const COMMENT_PATTERN = '#/\*\*.+?\*/#ms';

    /** Detect comment line pattern */
    const COMMENT_LINE_PATTERN = '/\*([^*]*)\n/';

    /** Detect comment tag pattern */
    const TAG_PATTERN = '/\@(\w+)\s+(.+)/';

    /** Get first word of line  pattern */
    const FIRST_WORD_PATTERN = '/^([^ ]+)\s+(.+)$/';

    /** Get field name default value pattern */
    const FIELD_TYPE_DEFAULT_VALUE_PATTERN = '/([^ ]+)=([^ \"]+)\s+(.+)/';

    /** Get field name default string value pattern */
    const FIELD_TYPE_DEFAULT_VALUE_STRING_PATTERN = '/([^ ]+)=\"([^\"]+)\"\s+(.+)/';

    /** @var array Files to parse */
    protected $files = [];

    /** @var string Default project name */
    protected $defaultProject;

    /** @var array Projects */
    protected $projects = [];

    /** @var Project Current project */
    protected $currentProject;

    /** @var array Sections */
    protected $sections = [];

    /** @var Section current section */
    protected $currentSection;

    /** @var Entry Current entry */
    protected $currentEntry;

    /** @var array Not used now */
    protected $objectStack = [];

    /** @var  Field Current array or object field */
    protected $currentObject;

    /** @var  mixed Last parsed entry */
    protected $lastTagEntry;

    /**
     * Parser constructor.
     * @param string $defaultProject Default endpont name
     */
    public function __construct($defaultProject = 'default') {
        $this->defaultProject = $defaultProject;
    }

    /**
     * Add a path to the parser
     * @param $path
     * @throws ParserException
     */
    public function path($path) {
        $this->debug('');
        $path = realpath($path);
        if (is_file($path)) {
            $this->files []= $path;
        } elseif (is_dir($path)) {
            $files = glob(realpath($path) . '/*');
            foreach ($files as $file) {
                $this->path($file);
            }
        } else {
            throw new ParserException('Path not found: ' . $path);
        }
    }

    /**
     * Parse files
     */
    public function parse() {
        foreach ($this->files as $file) {
            $this->parseFile($file);
        }
        return $this->getProjects();
    }

    /**
     * Get parsed projects
     * @return array
     */
    public function getProjects() {
        return array_values($this->projects);
    }

    /**
     * Parse a file
     * @param string $file
     */
    protected function parseFile($file) {
        $this->debug("Parsing {$file}");
        $body = file_get_contents($file);
        preg_match_all(static::COMMENT_PATTERN, $body, $m);
        if (empty($m[0])) return;
        foreach ($m[0] as $comment) {
            $this->parseComment($comment);
        }
    }

    /**
     * Parse comment
     * @param string $comment
     */
    protected function parseComment($comment) {
        $this->debug("Found comment");
        $this->debug($comment);
        $this->lastTagEntry = null;
        preg_match_all(static::COMMENT_LINE_PATTERN, $comment, $m);
        if (empty($m[1])) return;
        foreach ($m[1] as $i=>$line) {
            if (preg_match(self::TAG_PATTERN, trim($line), $m)) {
                $this->parseTag($m[1], $m[2]);
            } else {
                $this->parseCommentLine(trim($line));
            }
        }
    }

    /**
     * Parse tag
     * @param string $tag
     * @param string $line
     */
    protected function parseTag($tag, $line) {
        $this->lastTagEntry = null;
        $method = 'tag' . ucfirst($tag);
        if (method_exists($this, $method)){
            $this->debug("Found tag {$tag}");
            $this->lastTagEntry = call_user_func([ $this, $method], $line);
        }
    }

    /**
     * Parse comment line
     * @param $line
     */
    protected function parseCommentLine($line) {
        if (!$this->lastTagEntry) return;

        if (!$line && $this->currentObject) {
            $this->currentObject = null;
        } elseif ($this->currentObject) {
            $field = $this->parseField($line, $service);
            if ($service == 'open') {
                $this->objectStack[count($this->objectStack) - 1]->fields []= $field;
            } elseif ($service != 'close') {
                $this->currentObject->fields [] = $field;
            }
        } elseif (trim($line, "\n") && $this->lastTagEntry->description) {
            $this->lastTagEntry->description .= "\n" . $line;
        } elseif($line) {
            $this->lastTagEntry->description = $line;
        }
    }

    /**
     * Section tag handler
     * @param string $line
     * @return Section
     */
    protected function tagSection($line) {
        list($name, $title) = $this->firstWords($line);
        $section = $this->getSection($name);
        if ($title) {
            $section->title = $title;
        }
        $this->currentSection = $section;
        return $section;
    }

    /**
     * Call tag handler
     * @param string $line
     * @return Entry
     */
    protected function tagCall($line) {
        list($method, $uri, $title) = $this->firstWords($line, 2);
        $section = $this->getCurrentSection();
        $entry = new Entry();
        $entry->uri = $uri;
        $entry->method = $method;
        $entry->title = $title;
        $section->entries []= $entry;
        $this->currentEntry = $entry;
        return $entry;
    }

    /**
     * Project tag handler
     * @param string $line
     * @return Project|mixed
     */
    protected function tagProject($line) {
        list($name, $line) = $this->firstWords($line);
        list($version, $title) = $this->firstWords($line);
        $project = $this->getProject($name, $version);
        if ($title) {
            $project->title = $title;
        }
        $this->currentProject = $project;
        return $project;
    }

    /**
     * Parse request or response field
     * @param string $line
     * @param string $service 'open', 'close' or null
     * @return Field
     */
    protected function parseField($line, &$service, $register = false) {
        if ($register) {
            $type = 'object';
            $field = new Object();
        } else {
            list($type, $line) = $this->firstWords($line);
            // Close object special case (no type)
            if ($type == '}') {
                $service = 'close';
                $result = $this->currentObject;
                $this->currentObject = array_pop($this->objectStack);
                return $result;
            }
            $field = new Field();
            $field->isRequired = true;
            if (preg_match('/^(\w+)\|null$/',$type, $m)) {
                $field->isRequired = false;
                $type = $m[1];
            }
        }
        $field->type = $type;

        if (preg_match(static::FIELD_TYPE_DEFAULT_VALUE_STRING_PATTERN, $line, $m)
                || preg_match(static::FIELD_TYPE_DEFAULT_VALUE_PATTERN, $line, $m)) {
            $field->name = trim($m[1]);
            $field->defaultValue = $m[2];
            $line = $m[3];
            $field->isRequired = false;
            $field->title = $m[3];
        } else {
            list($field->name, $line) = $this->firstWords($line);
            $field->title = $line;
        }
        list($open, $titleNew) = $this->firstWords($line);
        if ($field->type == 'object') {
            $service = 'open';
            if ($open == '{') {
                $field->title = $titleNew;
            }
            if ($this->currentObject) {
                array_push($this->objectStack, $this->currentObject);
            }
            $this->currentObject = $field;
        }
        if ($open == '}') {
            $service = 'close';
            $field->title = $titleNew;
            $this->currentObject = array_pop($this->objectStack);
        }
        return $field;
    }

    /**
     * Request tag handler
     * @param string $line
     * @return Field
     */
    protected function tagRequest($line) {
        $entry = $this->getCurrentEntry();
        if (!$entry) {
            return null;
        }
        $this->currentObject = null;
        $this->objectStack = [];
        $field = $this->parseField($line, $service);
        $entry->request [] = $field;
        return $field;
    }

    /**
     * Response tag handler
     * @param string $line
     * @return Field
     */
    protected function tagResponse($line) {
        $entry = $this->getCurrentEntry();
        if (!$entry) {
            return null;
        }
        $this->currentObject = null;
        $this->objectStack = [];
        $field = $this->parseField($line, $service);
        $entry->response [] = $field;
        return $field;
    }

    /**
     * Register tag handler
     * @param $line
     * @return Field
     */
    protected function tagRegister($line) {
        $field = $this->parseField($line, $service, true);
        $project = $this->getCurrentProject();
        $project->classes[$field->name] = $this->currentObject;
        return $field;
    }

    /**
     * Return current project
     * @return Project
     */
    protected function getCurrentProject() {
        if (!$this->currentProject) {
            $this->currentProject = $this->getProject($this->defaultProject);
        }
        return $this->currentProject;
    }

    /**
     * Return current section
     * @return Section
     */
    protected function getCurrentSection() {
        if (!$this->currentSection) {
            $this->currentSection = $this->getSection('default');
        }
        return $this->currentSection;
    }

    /**
     * Get current entry
     * @return Entry
     */
    protected function getCurrentEntry() {
        if (!$this->currentEntry) {
            $this->currentEntry = new Entry();
        }
        return $this->currentEntry;
    }

    /**
     * Get section by name
     * @param string $name
     * @return Section
     */
    protected function getSection($name) {
        if (isset($this->sections[$name])) {
            return $this->sections[$name];
        } else {
            $section = new Section();
            $section->name = $name;
            $this->sections[$name] = $section;
            $project = $this->getCurrentProject();
            $project->sections[$name]= $section;
            return $section;
        }
    }

    /**
     * Get project by name
     * @param string $name
     * @return Project
     */
    protected function getProject($name, $version = null) {
        if (isset($this->projects[$name])) {
            return $this->projects[$name];
        } else {
            $this->currentSection = null;
            $this->sections = null;
            $this->currentEntry = null;
            $project = new Project();
            $project->name = $name;
            $project->version = $version;
            $this->projects[$name] = $project;
            return $project;
        }
    }

    /**
     * Get first n words from a line and the rest
     * @param string $line
     * @param int $count
     * @return array
     */
    protected function firstWords($line, $count = 1) {
        $result = [];
        $i = 0;
        while($i++ < $count) {
            if ($line && preg_match(static::FIRST_WORD_PATTERN, $line, $m)) {
                $result [] = $m[1];
                $line = $m[2];
            } elseif($line) {
                $result []= $line;
                $line = null;
            } else {
                $result [] = null;
                $line = null;
            }
        }
        $result [] = $line;
        return $result;
    }

    /**
     * Print debug info
     * @param $line
     */
    protected function debug($line) {
//        echo "$line\n";
    }

}