<?php

namespace App\Util;

class LogOutput
{
    public static function print($class, $message)
    {
        fwrite(fopen('php://stdout', 'wb'), $class . ': ' . $message . "\n");
    }
}
