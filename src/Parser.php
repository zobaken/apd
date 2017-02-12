<?php

namespace Apd;
use Apd\Structure\Endpoint;
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
    const FIRST_WORD = '/^([^ ]+)\s+(.+)$/';

    /** @var array Files to parse */
    protected $files = [];

    /** @var string Default endpoint name */
    protected $defaultEndpoint;

    /** @var array Endpoints */
    protected $endpoints = [];

    /** @var Endpoint Current endpoint */
    protected $currentEndpoint;

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
     * @param string $defaultEndpoint Default endpont name
     */
    public function __construct($defaultEndpoint = 'api') {
        $this->defaultEndpoint = $defaultEndpoint;
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
        return $this->getEndpoints();
    }

    /**
     * Get parsed endpoints
     * @return array
     */
    public function getEndpoints() {
        return array_values($this->endpoints);
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
        if ($this->lastTagEntry) {
            if ($this->lastTagEntry->description) {
                $this->lastTagEntry->description .= "\n" . $line;
            } elseif($line) {
                $this->lastTagEntry->description = $line;
            }
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
     * Api tag handler
     * @param string $line
     * @return Entry
     */
    protected function tagApi($line) {
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
     * Endpoint tag handler
     * @param string $line
     * @return Endpoint|mixed
     */
    protected function tagEndpoint($line) {
        list($name, $title) = $this->firstWords($line);
        $endpoint = $this->getEndpoint($name);
        if ($title) {
            $endpoint->title = $title;
        }
        $this->currentEndpoint = $endpoint;
        return $endpoint;
    }

    /**
     * Parse request or response field
     * @param string $line
     * @param string $service 'open', 'close' or null
     * @return Field
     */
    protected function parseField($line, &$service) {
        $field = new Field();
        list($type, $name, $title) = $this->firstWords($line, 2);
        list($open, $titleNew) = $this->firstWords($title);
        // TODO: apply stack to objects
        if (($type == 'object' || $type == 'array') && $open == '{') {
            $service = 'open';
            $title = $titleNew;
        }
        if (($type == 'object' || $type == 'array') && $open == '}') {
            $service = 'close';
            $this->currentObject = null;
            $title = $titleNew;
        }
        $field->isRequired = true;
        $field->name = $name;
        $field->type = $type;
        $field->title = $title;
        if (preg_match('/^(\w+)\|null$/', $type, $m)) {
            $field->isRequired = false;
            $field->type = $m[1];
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
        $field = $this->parseField($line, $service);
        if ($service != 'close') {
            if ($this->currentObject) {
                $this->currentObject->fields [] = $field;
            } else {
                $entry->request [] = $field;
            }
        }
        if ($service == 'open') {
            $this->currentObject = $field;
        } elseif ($service == 'close') {
            $this->currentObject = null;
        }
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
            return;
        }
        $field = $this->parseField($line, $service);
        if ($service != 'close') {
            if ($this->currentObject) {
                $this->currentObject->fields [] = $field;
            } else {
                $entry->response [] = $field;
            }
        }
        if ($service == 'open') {
            $this->currentObject = $field;
        } elseif ($service == 'close') {
            $this->currentObject = null;
        }
        return $field;
    }

    /**
     * Return current endpoint
     * @return Endpoint
     */
    protected function getCurrentEndpoint() {
        if (!$this->currentEndpoint) {
            $this->currentEndpoint = $this->getEndpoint($this->defaultEndpoint);
        }
        return $this->currentEndpoint;
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
            $endpoint = $this->getCurrentEndpoint();
            $endpoint->sections[$name]= $section;
            return $section;
        }
    }

    /**
     * Get endpoint by name
     * @param string $name
     * @return Endpoint
     */
    protected function getEndpoint($name) {
        if (isset($this->endpoints[$name])) {
            return $this->endpoints[$name];
        } else {
            $this->currentSection = null;
            $this->sections = null;
            $this->currentEntry = null;
            $endpoint = new Endpoint();
            $endpoint->name = $name;
            $this->endpoints[$name] = $endpoint;
            return $endpoint;
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
            if ($line && preg_match(static::FIRST_WORD, $line, $m)) {
                $result [] = strtolower($m[1]);
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