<?php

namespace EddTurtle\DirectUpload;

class InvalidOptionException extends \Exception
{

    public function __construct($message = '')
    {
        parent::__construct($message);
    }

}