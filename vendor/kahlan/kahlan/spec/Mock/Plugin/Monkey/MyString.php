<?php
namespace Kahlan\Spec\Mock\Plugin\Monkey;

class MyString
{
    public static function dump($value)
    {
        return 'myhashvalue';
    }
}
