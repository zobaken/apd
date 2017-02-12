<?php

namespace Apd;

/**
 * Interface Exportable
 * @package Apd
 */
interface Exportable {

    /**
     * @param Parser $parser
     * @return mixed
     */
    function  export(Parser $parser);

}