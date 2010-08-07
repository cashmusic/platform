<?php
/**
* @author Kaloyan K. Tsvetkov <kaloyan@kaloyan.info>
* @license http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License Version 2.1
* @package Asido
* @subpackage Asido.Driver.GD
* @version $Id: class.driver.gd.php 7 2007-04-09 21:09:09Z mrasnika $
*/

/////////////////////////////////////////////////////////////////////////////

/**
* Quality factor for saving JPEG files
* @see Asido_Driver_GD::Save()
*/
if (!defined('ASIDO_GD_JPEG_QUALITY')) {
	define('ASIDO_GD_JPEG_QUALITY', 80);
	}

/////////////////////////////////////////////////////////////////////////////

/**
* Asido GD(GD2) driver
*
* @package Asido
* @subpackage Asido.Driver.GD
*/
Class Asido_Driver_GD Extends Asido_Driver {

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
	/**
	* Maps to supported mime types
	* @var array
	* @access protected
	*/
	var $__mime = array(

		// support reading
		//
		'read' => array(
		
			// GIF
			//
			'image/gif',
			
			// JPEG
			//
			'application/jpg',
			'application/x-jpg',
			'image/jpg',
			'image/jpeg',
			
			// WBMP
			//
			'image/wbmp',
			
			// XPM
			//
			'image/x-xpixmap',
			'image/x-xpm',
			
			// XBM
			//
			'image/x-xbitmap',
			'image/x-xbm',
			
			// PNG
			//
			'application/png',
			'application/x-png',
			'image/x-png',
			'image/png',
		
			),
		
		// support writing
		//
		'write' => array(
		
			// GIF
			//
			'image/gif',
			
			// JPEG
			//
			'application/jpg',
			'application/x-jpg',
			'image/jpg',
			'image/jpeg',
			
			// WBMP
			//
			'image/wbmp',
			
			// PNG
			//
			'application/png',
			'application/x-png',
			'image/x-png',
			'image/png',
			),	

		);
	
	/**
	* Metaphone map for detecting image file extensions
	* @var array
	* @access private
	*/
	var $__mime_metaphone = array(
			'JPK' => 'image/jpeg',
			'JP' => 'image/jpeg',
			'JF' => 'image/gif',
			'NK' => 'image/png',
			'BMP' => 'image/wbmp',
			'SPM' => 'image/x-xbm',
			// 'SBM' => 'image/x-xpm',
				// ^
				// XPM is read-only and this map is used for
				// saving files, so this XPM entry is useless
		);

	/**
	* Soundex map for detecting image file extensions
	* @var array
	* @access private
	*/
	var $__mime_soundex = array(
			'J120' => 'image/jpeg',
			'J100' => 'image/jpeg',
			'G100' => 'image/gif',
			'P520' => 'image/png',
			'B510' => 'image/wbmp',
			'W151' => 'image/wbmp',
		);

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* Checks whether the environment is compatible with this driver
	*
	* @return boolean
	* @access public
	*/
	function is_compatible() {
		
		if (!extension_loaded('gd')) {
			trigger_error(
				'The Asido_Driver_GD driver is unnable to be '
					. ' initialized, because the GD (php_gd2) '
					. ' module is not installed',
				E_USER_ERROR
				);
			return false;
			}
		
		// give access to all the memory
		//
		@ini_set("memory_limit", -1);
		
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

		// create new target
		//
		$_ = imageCreateTrueColor($width, $height);
		imageSaveAlpha($_, true);
		imageAlphaBlending($_, false);

		$r = imageCopyResized(
			$_, $tmp->target,
				0,0,
				0,0,
				$width, $height,
				$tmp->image_width, $tmp->image_height
			);

		// set new target
		//
		$this->__destroy_target($tmp);
		$tmp->target = $_;

		return $r;
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

		imageAlphaBlending($tmp_target->target, true);
		$r = imageCopy($tmp_target->target, $tmp_source->source,
			$destination_x, $destination_y,
			0, 0,
			$tmp_source->image_width, $tmp_source->image_height
			);
		imageAlphaBlending($tmp_target->target, false);
		
		return $r;
		}

	/**
	* Make the image greyscale: supported only for PHP => 5.* and PHP => 4.0.1 except for PHP 4.3.11
	*
	* @param Asido_TMP &$tmp
	* @return boolean
	* @access protected
	*/
	function __grayscale(&$tmp) {

		// the shorter path: function already exists
		//
		if (function_exists('imagefilter')) {
			return imagefilter($tmp->target, IMG_FILTER_GRAYSCALE);
			return true;
			}

		// a bit wicked path: PHP 4.3.11 has a bug in this function
		//
		if (!in_array(PHP_VERSION, array('4.3.11'))) {
			return imageCopyMergeGray($tmp->target, $tmp->target,
				0, 0, 0, 0,
				$tmp->image_width, $tmp->image_height, 0);
			}
		
		return false;
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
		
		list($r, $g, $b) = $color->get();
		$rotate_color = imageColorAllocate($tmp->target, $r, $g, $b); 
		
		if ($t = imageRotate($tmp->target, $angle * -1, $rotate_color)) {
			imageDestroy($tmp->target);
			$tmp->target = $t;
			
			$tmp->image_width = imageSX($tmp->target);
			$tmp->image_height = imageSY($tmp->target);
			
			return true;
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
		
		$t = imageCreateTrueColor($width, $height);
		imageAlphaBlending($t, true);
		$r = imageCopy($t, $tmp->target,
			0, 0,
			$x, $y,
			$width, $height
			);
		imageAlphaBlending($t, false);
		
		$this->__destroy_target($tmp);
		$tmp->target = $t;
		$tmp->image_width = $width;
		$tmp->image_height = $height;
		
		return $r;
		}

	/**
	* Vertically mirror (flip) the image: not supported
	* 
	* @param Asido_TMP &$tmp
	* @return boolean
	* @access protected
	*/
	function __flip(&$tmp) {
		return false;
		}

	/**
	* Horizontally mirror (flop) the image: not supported
	* 
	* @param Asido_Image &$image
	* @return boolean
	* @access protected
	*/
	function __flop(&$tmp) {
		return false;
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
		
		$t = new Asido_TMP;
		$t->target = imageCreateTrueColor($width, $height);
		
		list($r, $g, $b) = $color->get();
		imageFill($t->target, 1, 1, 
			imageColorAllocate($t->target, $r, $g, $b)
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

		imageAlphaBlending($handler, 0);
		imageSaveAlpha($handler, 1); 
		imagePNG($handler, $filename);
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

		$error_source = false;
		$error_target = false;

		// get image dimensions
		//
		if ($i = @getImageSize($tmp->source_filename)) {
			$tmp->image_width = $i[0];
			$tmp->image_height = $i[1];
			}
		
		// image type ?
		//
		switch(@$i[2]) {
			
			case 1:	// GIF
				$error_source = (false == (
					$tmp->source = @imageCreateFromGIF(
						$tmp->source_filename
						)
					));

				$error_target = false == (
					$tmp->target = imageCreateTrueColor(
						$tmp->image_width, $tmp->image_height
						)
					);
				$error_target &= imageCopyResampled(
					$tmp->target, $tmp->source, 
					0, 0, 0, 0,
					$tmp->image_width, $tmp->image_height,
					$tmp->image_width, $tmp->image_height
					);
				
				break;

			case 2: // JPG
				$error_source = (false == (
					$tmp->source = imageCreateFromJPEG(
						$tmp->source_filename
						)
					));
				
				$error_target = (false == (
					$tmp->target = imageCreateFromJPEG(
						$tmp->source_filename
						)
					));
				break;

			case 3: // PNG
				$error_source = (false == (
					$tmp->source = @imageCreateFromPNG(
						$tmp->source_filename
						)
					));

				$error_target = (false == (
					$tmp->target = @imageCreateFromPNG(
						$tmp->source_filename
						)
					));
				break;

			case 15: // WBMP
				$error_source = (false == (
					$tmp->source = @imageCreateFromWBMP(
						$tmp->source_filename
						)
					));

				$error_target = (false == (
					$tmp->target = @imageCreateFromWBMP(
						$tmp->source_filename
						)
					));
				break;

			case 16: // XBM
				$error_source = (false == (
					$tmp->source = @imageCreateFromXBM(
						$tmp->source_filename
						)
					));

				$error_target = (false == (
					$tmp->target = @imageCreateFromXBM(
						$tmp->source_filename
						)
					));
				break;

			case 4: // SWF

			case 5: // PSD

			case 6: // BMP

			case 7: // TIFF(intel byte order)

			case 8: // TIFF(motorola byte order)

			case 9: // JPC

			case 10: // JP2

			case 11: // JPX

			case 12: // JB2

			case 13: // SWC

			case 14: // IFF
				
			default:
				
				$error_source = (false == (
					$tmp->source = @imageCreateFromString(
						file_get_contents(
							$tmp->source_filename
							)
						)
					));

				$error_target = (false == (
					$tmp->source = @imageCreateFromString(
						file_get_contents(
							$tmp->source_filename
							)
						)
					));
				break;
			}

		return !($error_source || $error_target);
		}

	/**
	* Write the image after being processed
	*
	* @param Asido_TMP &$tmp
	* @return boolean
	* @access protected
	*/
	function __write(&$tmp) {

		// try to guess format from extension
		//
		if (!$tmp->save) {
			$p = pathinfo($tmp->target_filename);

			($tmp->save = $this->__mime_metaphone[metaphone($p['extension'])])
				|| ($tmp->save = $this->__mime_soundex[soundex($p['extension'])]);
			}

		$result = false;
		switch($tmp->save) {

			case 'image/gif' :
				imageTrueColorToPalette($tmp->target, true, 256);
				$result = @imageGIF($tmp->target, $tmp->target_filename);
				break;
				
			case 'image/jpeg' :
				$result = @imageJPEG($tmp->target, $tmp->target_filename, ASIDO_GD_JPEG_QUALITY);
				break;
				
			case 'image/wbmp' :
				$result = @imageWBMP($tmp->target, $tmp->target_filename);
				break;
			
			default :
			case 'image/png' :

				imageSaveAlpha($tmp->target, true);
				imageAlphaBlending($tmp->target, false);
			
				$result = @imagePNG($tmp->target, $tmp->target_filename);
				break;
			}
		
		@$this->__destroy_source($tmp);
		@$this->__destroy_target($tmp);

		return $result;
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
		return imageDestroy($tmp->source);
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
		return imageDestroy($tmp->target);
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

//--end-of-class--	
}

/////////////////////////////////////////////////////////////////////////////

?>