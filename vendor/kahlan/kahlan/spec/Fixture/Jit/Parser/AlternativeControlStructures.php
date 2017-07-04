<?php

$shallNotPass = false;
if (true):
    $shallNotPass = true;
endif;

$i = 0;
foreach ([1, 2] as $value):
    $i++;
endforeach;

while (false):
    $i = 0;
endwhile;

switch ($i):
    case 0:
        $i = 1;
        break;
    default:
        $i = 0;
        break;
endswitch;
