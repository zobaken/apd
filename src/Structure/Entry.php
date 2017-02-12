<?php

namespace Apd\Structure;

/**
 * Class Entry
 * @package Apd\Structure
 */
class Entry {

    /** @var string Entry uri*/
    var $uri;

    /** @var string Entry method*/
    var $method;

    /** @var string Entry title*/
    var $title;

    /** @var string Entry description*/
    var $description;

    /** @var array Entry request*/
    var $request = [];

    /** @var array Entry response */
    var $response = [];

}