<?php
/**
* @author Kaloyan K. Tsvetkov <kaloyan@kaloyan.info>
* @license http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License Version 2.1
* @package Asido
* @subpackage Asido.Driver.Imagick_Extension
* @version $Id: class.driver.imagick_ext.php 7 2007-04-09 21:09:09Z mrasnika $
*/

/////////////////////////////////////////////////////////////////////////////

/**
* @see Asido_IMagick
*/
require_once ASIDO_DIR . "/class.imagick.php";

/////////////////////////////////////////////////////////////////////////////

/**
* Asido "Imagick" driver (as extension)
*
* @package Asido
* @subpackage Asido.Driver.Imagick_Extension
*/
Class Asido_Driver_Imagick_Ext Extends Asido_Driver {

	/**
	* Maps to supported mime types for saving files
	* @var array
	*/
	var $__mime = array(

		// support reading
		//
		'read' => array(

			),

		// support writing
		//
		'write' => array(

			)
		);

	/**
	* MIME-type to image format map
	*
	* This is used for conversion and saving ONLY, so  
	* read-only file formats should not appear here
	*
	* @var array
	* @access private
	*/
	var $__mime_map = array();

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
	/**
	* Constructor
	*/
	function Asido_Driver_Imagick_Ext() {
		$imagick = new Asido_IMagick;
		$this->__mime = $imagick->__mime;
		$this->__mime_map = $imagick->__mime_map;
		}
	
	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* Checks whether the environment is compatible with this driver
	*
	* @return boolean
	* @access public
	*/
	function is_compatible() {

		if (!extension_loaded('imagick')) {
			trigger_error(
				'The Asido_Driver_Imagick_Ext driver is '
					. ' unnable to be initialized, '
					. ' because the IMagick (php_imagick) '
					. ' module is not installed',
				E_USER_ERROR
				);
			return false;
			}
		
		// give access to all the memory
		//
		@ini_set("memory_limit", -1);
		
		// no time limit
		//
		@set_time_limit(-1);
		
		return true;
		}
	
	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* Do the actual resize of an image
	*
	* @param Asido_TMP &$tmp
	* @param integer $width
	* @param integer $height
	* @return boolean
	* @access protected
	*/
	function __resize(&$tmp, $width, $height) {
		return imagick_resize($tmp->target,
			$width, $height, IMAGICK_FILTER_UNKNOWN, 0);
		}

	/**
	* Copy one image to another
	*
	* @param Asido_TMP &$tmp_target
	* @param Asido_TMP &$tmp_source
	* @param integer $destination_x
	* @param integer $destination_y
	* @return boolean
	* @access protected
	*/
	function __copy(&$tmp_target, &$tmp_source, $destination_x, $destination_y) {
		return imagick_composite(
			$tmp_target->target, IMAGICK_COMPOSITE_OP_OVER,
			$tmp_source->source,
			$destination_x, $destination_y);
		}

	/**
	* Make the image greyscale: not supported
	*
	* @param Asido_TMP &$tmp
	* @return boolean
	* @access protected
	*/
	function __grayscale(&$tmp) {
		return false;
		}

	/**
	* Rotate the image clockwise: only rectangular rotates are supported (90,180,270)
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

		// rectangular rotates are OK
		//
		if (($angle % 90) == 0) {
			if (imagick_rotate($tmp->target, $angle)) {
				$tmp->image_width = imagick_getWidth($tmp->target);
				$tmp->image_height = imagick_getHeight($tmp->target);
				return true;
				}
			}
		
		return false;
		}

	/**
	* Crop the image 
	*
	* @param Asido_TMP &$tmp
	* @param integer $x
	* @param integer $y
	* @param integer $width
	* @param integer $height
	* @return boolean
	* @access protected
	*/
	function __crop(&$tmp, $x, $y, $width, $height) {
		if (!imagick_crop($tmp->target, $x, $y, $width, $height)) {
			return false;
			}
		return true;
		}

	/**
	* Vertically mirror (flip) the image
	* 
	* @param Asido_TMP &$tmp
	* @return boolean
	* @access protected
	*/
	function __flip(&$tmp) {
		return imagick_flip($tmp->target);
		}

	/**
	* Horizontally mirror (flop) the image
	* 
	* @param Asido_Image &$image
	* @return boolean
	* @access protected
	*/
	function __flop(&$tmp) {
		return imagick_flop($tmp->target);
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* Get canvas
	*
	* @param integer $width
	* @param integer $height
	* @param Asido_Color &$color
	* @return Asido_TMP
	* @access protected
	*/
	function __canvas($width, $height, &$color) {
		
		list($r, $g, $b) = $color->get();
		
		$t = new Asido_TMP;
		$t->target = imagick_getCanvas(
			"rgb($r, $g, $b)",
			$width, $height
			);
		
		$t->image_width = $width;
		$t->image_height = $height;

		return $t;
		}

	/**
	* Generate a temporary object for the provided argument
	*
	* @param mixed &$handler
	* @param string $filename the filename will be automatically generated 
	*	on the fly, but if you want you can use the filename provided by 
	*	this argument
	* @return Asido_TMP
	* @access protected
	*/
	function __tmpimage(&$handler, $filename=null) {

		if (!isset($filename)) {
			$filename = $this->__tmpfile();
			}

		imagick_convert($handler, "PNG");
		imagick_writeImage($handler, $filename);
			// ^
			// PNG: no pixel losts

		return $this->prepare(
			new Asido_Image($filename)
			);
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* Open the source and target image for processing it
	*
	* @param Asido_TMP &$tmp
	* @return boolean
	* @access protected
	*/
	function __open(&$tmp) {

		$error_open = !($tmp->source = imagick_readImage(realpath($tmp->source_filename)));
		$error_open &= !($tmp->target = imagick_cloneHandle($tmp->source));
			
		// get width & height of the image
		//
		if (!$error_open) {
			$tmp->image_width = imagick_getWidth($tmp->source);
			$tmp->image_height = imagick_getHeight($tmp->source);
			}

		return !$error_open;
		}

	/**
	* Write the image after being processed
	*
	* @param Asido_Image &$image
	* @return boolean
	* @access protected
	*/
	function __write(&$tmp) {
		
		$ret = false;

		if ($tmp->save) {

			// convert, then save
			//
			imagick_convert(
				$tmp->target, $this->__mime_map[$tmp->save]
				);

			$t = $this->__tmpfile();
			if (!imagick_writeImage($tmp->target, $t)) {
				return false;
				}
			
			$ret = @copy($t, $tmp->target_filename);
			@unlink($t);

			} else {

			// weird ... only works with absolute names
			//
			fclose(fopen($tmp->target_filename, 'w'));

			// no convert, just save
			//
			$ret = imagick_writeImage(
				$tmp->target, realpath($tmp->target_filename)
				);
			}
		
		// dispose
		//
		@$this->__destroy_source($tmp);
		@$this->__destroy_target($tmp);

		return $ret;
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* Destroy the source for the provided temporary object
	*
	* @param Asido_TMP &$tmp
	* @return boolean
	* @access protected
	* @abstract
	*/	
	function __destroy_source(&$tmp) {
		return imagick_free($tmp->source);
		}

	/**
	* Destroy the target for the provided temporary object
	*
	* @param Asido_TMP &$tmp
	* @return boolean
	* @access protected
	* @abstract
	*/	
	function __destroy_target(&$tmp) {
		return imagick_free($tmp->target);
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
//--end-of-class--	
}

/////////////////////////////////////////////////////////////////////////////

?>