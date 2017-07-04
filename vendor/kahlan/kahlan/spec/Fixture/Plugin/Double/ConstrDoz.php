<?php
namespace Kahlan\Spec\Fixture\Plugin\Double;

class ConstrDoz
{
    public $a;

    public $b;

    public function __construct($a, $b)
    {
        $this->a = $a;
        $this->b = $b;
    }
}
