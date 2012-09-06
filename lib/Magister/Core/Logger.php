<?php

class Logger {

    const DEBUG = 1;
    const INFO = 2;
    const WARN = 4;
    const ERROR = 8;

    private static $names = array(
        self::DEBUG => 'debug.log',
        self::INFO => 'info.log',
        self::WARN => 'warn.log',
        self::ERROR => 'error.log'
    );
}