<?php

namespace PHP_Parallel_Lint\PhpConsoleColor;

class InvalidStyleException extends \Exception
{
    public function __construct($styleName)
    {
        parent::__construct("Invalid style $styleName.");
    }
}
