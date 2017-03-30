<?php

namespace EddTurtle\DirectUpload;

class InvalidAclException extends \Exception
{

    public function __construct()
    {
        parent::__construct("The AWS acl specified is not valid. Try checking: http://amzn.to/1SSOgwO");
    }

}