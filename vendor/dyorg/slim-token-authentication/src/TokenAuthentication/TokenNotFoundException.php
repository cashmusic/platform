<?php

/*
 * This file is part of Slim Token Authentication Middleware
 *
 * Copyright (c) 2016 Dyorg Almeida
 *
 * Licensed under the MIT license
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace Slim\Middleware\TokenAuthentication;

class TokenNotFoundException extends \Exception implements UnauthorizedExceptionInterface
{

}