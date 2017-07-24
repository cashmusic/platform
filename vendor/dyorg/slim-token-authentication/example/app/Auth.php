<?php

namespace app;

class Auth
{
    /**
     * It's only a validation example!
     * You should search user (on your database) by authorization token
     */
    public function getUserByToken($token)
    {
        if ($token != 'usertokensecret') {

            /**
             * The throwable class must implement UnauthorizedExceptionInterface
             */
            throw new UnauthorizedException('Invalid Token');
        }

        $user = [
            'name' => 'Dyorg',
            'id' => 1,
            'permisssion' => 'admin'
        ];

        return $user;
    }

}