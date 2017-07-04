<?php

$i = 5;
$values = array_rand([true, false, 'zero', 'one'], 1);

switch(current($values)) {
	case true:
		$i++;
	break;
	case false:
		$i--;
	break;
	case 'zero':
		$i = 0;
	break;
	default:
		$i = 1;
	break;
}
