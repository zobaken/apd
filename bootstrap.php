<?php

if(!defined('APD_ROOT')) {
    define('APD_ROOT', realpath(__DIR__));
    if (file_exists(APD_ROOT . '/vendor/autoload.php')) {
        require_once APD_ROOT . '/vendor/autoload.php';
    } elseif(file_exists(APD_ROOT . '/../autoload.php')) {
        require_once APD_ROOT . '/../vendor/autoload.php';
    }
}
