<?php
/**
* @author Kaloyan K. Tsvetkov <kaloyan@kaloyan.info>
* @license http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License Version 2.1
* @package Asido
* @subpackage Asido.Driver.GD
* @version $Id: class.driver.gd_hack.php 7 2007-04-09 21:09:09Z mrasnika $
*/

/////////////////////////////////////////////////////////////////////////////

/**
* @see Asido_Driver_GD
*/
require_once ASIDO_DIR . "/class.driver.gd.php";

/////////////////////////////////////////////////////////////////////////////

/**
* Asido GD(GD2) driver with some of the unsupported methods hacked via some work-arounds.
*
* @package Asido
* @subpackage Asido.Driver.GD
*/
Class Asido_Driver_GD_Hack Extends Asido_Driver_GD {

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* Make the image greyscale
	*
	* @param Asido_TMP &$tmp
	* @return boolean
	* @access protected
	*/
	function __grayscale(&$tmp) {

		// the longer path: do it pixel by pixel
		// 
		if (parent::__grayscale(&$tmp)) {
			return true;
			}

		// create 256 color palette
		//
		$palette = array();
		for ($c=0; $c<256; $c++) {
			$palette[$c] = imageColorAllocate($tmp->target, $c, $c, $c);
			}

		// read origonal colors pixel by pixel
		//
		for ($y=0; $y<$tmp->image_height; $y++) {
			for ($x=0; $x<$tmp->image_width; $x++) {

				$rgb = imageColorAt($tmp->target, $x, $y);

				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;

				$gs = (($r*0.299)+($g*0.587)+($b*0.114));
				imageSetPixel($tmp->target, $x, $y, $palette[$gs]);
				}
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
		
		$t = imageCreateTrueColor($tmp->image_width, $tmp->image_height);
		imageAlphaBlending($t, true);

		for ($y = 0; $y < $tmp->image_height; ++$y) {
			imageCopy(
				$t, $tmp->target,
				0, $y,
				0, $tmp->image_height - $y - 1,
				$tmp->image_width, 1
				);
			}
		imageAlphaBlending($t, false);

		$this->__destroy_target($tmp);
		$tmp->target = $t;

		return true;
		}

	/**
	* Horizontally mirror (flop) the image
	* 
	* @param Asido_Image &$image
	* @return boolean
	* @access protected
	*/
	function __flop(&$tmp) {

		$t = imageCreateTrueColor($tmp->image_width, $tmp->image_height);
		imageAlphaBlending($t, true);

		for ($x = 0; $x < $tmp->image_width; ++$x) {
			imageCopy(
				$t,
				$tmp->target,
				$x, 0,
                		$tmp->image_width - $x - 1, 0,
                		1, $tmp->image_height
                		);
			}
		imageAlphaBlending($t, false);

		$this->__destroy_target($tmp);
		$tmp->target = $t;

		return true;
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