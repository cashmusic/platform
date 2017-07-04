<?php
namespace Kahlan\Spec\Fixture\Reporter\Coverage;

function shallNotPass()
{
    $shallNotPass = false;
    if (false) {
        $shallNotPass = true;
    }
    return $shallNotPass;
    $shallNotPass = true;
}
