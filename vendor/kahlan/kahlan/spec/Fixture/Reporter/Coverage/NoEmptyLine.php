<?php
namespace Kahlan\Spec\Fixture\Reporter\Coverage;

class NoEmptyLine
{
    public function shallNotPass()
    {
        $shallNotPass = false;
        if (false) {
            $shallNotPass = true;
        }
        return $shallNotPass;
        $shallNotPass = true;
    }
}