<?php

namespace Apd\Structure;

/**
 * Class Project
 * @package Apd\Structure
 */
class Project {

    /** @var string Project name */
    var $name;

    /** @var string Project title */
    var $title;

    /** @var string Project version */
    var $version;

    /** @var string Project base url */
    var $baseUrl;

    /** @var string Project description */
    var $description;

    /** @var Section[] Sections */
    var $sections = [];

    /** @var Object[] Registered classes */
    var $classes = [];

}