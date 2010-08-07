<?php
/**
* @author Kaloyan K. Tsvetkov <kaloyan@kaloyan.info>
* @license http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License Version 2.1
* @package Asido
* @subpackage Asido.Driver.Imagick_Shell
* @version $Id: class.driver.imagick_shell_hack.php 7 2007-04-09 21:09:09Z mrasnika $
*/

/////////////////////////////////////////////////////////////////////////////

/**
* @see Asido_Driver_Imagick_Shell
*/
require_once ASIDO_DIR . "/class.driver.imagick_shell.php";

/////////////////////////////////////////////////////////////////////////////

/**
* Asido "Imagick" driver (via shell) with some of the unsupported methods hacked via some work-arounds.
*
* @package Asido
* @subpackage Asido.Driver.Imagick_Shell
*/
Class Asido_Driver_Imagick_Shell_Hack Extends Asido_Driver_Imagick_Shell {

	/**
	* Rotate the image clockwise
	*
	* @param Asido_TMP &$tmp
	* @param float $angle
	* @param Asido_Color &$color
	* @return boolean
	* @access protected
	*/
	function __rotate(&$tmp, $angle, &$color) {

		// skip full loops
		//
		if (($angle % 360) == 0) {
			return true;
			}

		$a = $tmp->image_height;
		$b = $tmp->image_width;

		// do the virtual `border`
		//
		$c = $a * cos(deg2rad($angle)) * sin(deg2rad($angle));
		$d = $b * cos(deg2rad($angle)) * sin(deg2rad($angle));
		
		// do the rest of the math
		//
		$a2 = $b * sin(deg2rad($angle)) + $a * cos(deg2rad($angle));
		$b2 = $a * sin(deg2rad($angle)) + $b * cos(deg2rad($angle));
			
		$a3 = 2 * $d + $a;
		$b3 = 2 * $c + $b;
		
		$a4 = $b3 * sin(deg2rad($angle)) + $a3 * cos(deg2rad($angle));
		$b4 = $a3 * sin(deg2rad($angle)) + $b3 * cos(deg2rad($angle));
		
		// create the `border` canvas
		//
		$t = $this->__canvas(ceil($b + 2*$c), ceil($a + 2*$d), $color);

		// copy the image
		//
		$cmd = $this->__command(
			'composite',
			" -geometry {$b}x{$a}+" . ceil($c) . "+" . ceil($d) . " "
                		. escapeshellarg(realpath($tmp->target))
                		. " "
                		. escapeshellarg(realpath($t->target))
                		. " "
                		. escapeshellarg(realpath($t->target))
                	);
                exec($cmd, $result, $errors);
                if ($errors) {
                	return false;
                	}

		// rotate the whole thing
		//
		$cmd = $this->__command(
			'convert',
			" -rotate {$angle} "
              			. escapeshellarg(realpath($t->target))
              			. " "
              			. escapeshellarg(realpath($t->target))
                	);
                exec($cmd, $result, $errors);
                if ($errors) {
                	return false;
                	}

		// `final` result
		//
		$cmd = $this->__command(
			'convert',
			" -crop " . ceil($b2) . "x" . ceil($a2) . "+" . ceil(($b4 - $b2)/2) . "+" . ceil(($a4 - $a2)/2)
				. " "
                		. escapeshellarg(realpath($t->target))
                		. " TIF:"
              			// ^ 
              			// GIF saving hack
                		. escapeshellarg(realpath($t->target))
                	);

                exec($cmd, $result, $errors);
                if ($errors) {
                	return false;
                	}

		$this->__destroy_target($tmp);
		$tmp->target = $t->target;

		$tmp->image_width = $b2;
		$tmp->image_height = $a2;
		return true;
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

//--end-of-class--	
}

/////////////////////////////////////////////////////////////////////////////

?>