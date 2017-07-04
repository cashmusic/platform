<?php
namespace Kahlan\Spec\Fixture\Reporter\Coverage;

class ImplementsCoverage implements ImplementsCoverageInterface
{
    public function foo($a = null)
    {
        return $a;
    }
}
