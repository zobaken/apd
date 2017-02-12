<?php

namespace Apd;

/**
 * Interface Exportable
 * @package Apd
 */
interface Exportable {

    /**
     * Export in some format
     * @param Parser $parser
     * @return mixed
     */
    function  export(Parser $parser);

}