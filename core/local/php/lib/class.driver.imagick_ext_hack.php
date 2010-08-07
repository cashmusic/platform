<?php
/**
* @author Kaloyan K. Tsvetkov <kaloyan@kaloyan.info>
* @license http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License Version 2.1
* @package Asido
* @subpackage Asido.Driver.Imagick_Extension
* @version $Id: class.driver.imagick_ext_hack.php 7 2007-04-09 21:09:09Z mrasnika $
*/

/////////////////////////////////////////////////////////////////////////////

/**
* @see Asido_Driver_Imagick_Ext
*/
require_once ASIDO_DIR . "/class.driver.imagick_ext.php";

/////////////////////////////////////////////////////////////////////////////

/**
* Asido "Imagick" driver (as extension) with some of the unsupported methods hacked via some work-arounds.
*
* @package Asido
* @subpackage Asido.Driver.Imagick_Extension
*/
Class Asido_Driver_Imagick_Ext_Hack Extends Asido_Driver_Imagick_Ext {

	/**
	* Make the image greyscale: not supported
	*
	* @param Asido_TMP &$tmp
	* @return boolean
	* @access protected
	*/
	function __grayscale(&$tmp) {
		return imagick_ordereddither($tmp->target);
		}

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
		$t = $this->__canvas($b + 2*$c, $a + 2*$d, $color);

		// copy the image
		//
		imagick_composite(
			$t->target, IMAGICK_COMPOSITE_OP_OVER,
			$tmp->target,
			$c, $d);

		
		// rotate the whole thing
		//
		imagick_rotate($t->target, $angle);
		
		// `final` result
		//
		$f = $this->__canvas($b2, $a2, $color);

		imagick_composite(
			$f->target, IMAGICK_COMPOSITE_OP_OVER,
			$t->target,
			-(floor($b4) - $b2)/2,
			-(floor($a4) - $a2)/2
			);

		$this->__destroy_target($t);
		$this->__destroy_target($tmp);
		$tmp->target = $f->target;

		$tmp->image_width = $b2;
		$tmp->image_height = $a2;
		return true;
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
//--end-of-class--	
}

/////////////////////////////////////////////////////////////////////////////

?>