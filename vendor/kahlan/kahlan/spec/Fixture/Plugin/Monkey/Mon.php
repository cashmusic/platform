<?php
namespace Kahlan\Spec\Fixture\Plugin\Monkey;

use Kahlan\Util\Text;

function rand($min, $max)
{
    return ($max - $min) / 2;
}

class Mon
{
    public function time()
    {
        return time();
    }

    public function rand($min = 0, $max = 100)
    {
        return rand($min, $max);
    }

    public function datetime($datetime = 'now')
    {
        return new \DateTime($datetime);
    }

    public function dump($value)
    {
        return Text::dump($value);
    }

    public function loadFile($path = '')
    {
        return file_get_contents($path);
    }
}
