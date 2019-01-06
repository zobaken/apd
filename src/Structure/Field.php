<?php

namespace Apd\Structure;

/**
 * Class Field
 * @package Apd\Structure
 */
class Field {

    /** @var string Field name */
    var $name;

    /** @var string Field type */
    var $type;

    /** @var string Field title */
    var $title;

    /** @var string Field description*/
    var $description;

    /** @var bool Field required*/
    var $isRequired = false;

    /** @var string Field default value */
    var $defaultValue;

    /** @var array Object fields */
    var $fields = [];

}