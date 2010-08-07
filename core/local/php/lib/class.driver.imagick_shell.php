<?php
/**
* @author Kaloyan K. Tsvetkov <kaloyan@kaloyan.info>
* @license http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License Version 2.1
* @package Asido
* @subpackage Asido.Driver.Imagick_Shell
* @version $Id: class.driver.imagick_shell.php 7 2007-04-09 21:09:09Z mrasnika $
*/

/////////////////////////////////////////////////////////////////////////////

/**
* @see Asido_IMagick
*/
require_once ASIDO_DIR . "/class.imagick.php";

/////////////////////////////////////////////////////////////////////////////

/**
* @see Asido_Driver_Shell
*/
require_once ASIDO_DIR . "/class.driver.shell.php";

/////////////////////////////////////////////////////////////////////////////

/**
* This is the path to where the Image Magick executables are
*/
if (!defined('ASIDO_IMAGICK_SHELL_PATH')) {
	define('ASIDO_IMAGICK_SHELL_PATH', '');
	}

/////////////////////////////////////////////////////////////////////////////

/**
* Asido "Imagick" driver (via shell)
*
* @package Asido
* @subpackage Asido.Driver.Imagick_Shell
*/
Class Asido_Driver_Imagick_Shell Extends Asido_Driver_Shell {

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

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
	function Asido_Driver_Imagick_Shell() {
		
		/// supported files
		//
		$imagick = new Asido_IMagick;
		$this->__mime = $imagick->__mime;
		$this->__mime_map = $imagick->__mime_map;
		
		// executable 
		//
		if (ASIDO_IMAGICK_SHELL_PATH) {
			$this->__exec = ASIDO_IMAGICK_SHELL_PATH;
			} else {
            		$this->__exec = dirname($this->__exec('convert')) . DIRECTORY_SEPARATOR;
			}
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* Checks whether the environment is compatible with this driver
	*
	* @return boolean
	* @access public
	*/
	function is_compatible() {

		if (!$this->__exec) {
			trigger_error(
				'The Asido_Driver_Imagick_Shell driver is '
					. ' unable to be initialized, because '
					. ' the Image Magick (imagick) executables '
					. ' were not found. Please locate '
					. ' where those files are and set the '
					. ' path to them by defining the '
					. ' ASIDO_IMAGICK_SHELL_PATH constant.',
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
		
		// call `convert -geometry`
		//
		$cmd = $this->__command(
			'convert',
                	"-geometry {$width}x{$height}! "
                		. escapeshellarg(realpath($tmp->target))
                		. " "
                		. escapeshellarg(realpath($tmp->target))
                	);

                exec($cmd, $result, $errors);
		return ($errors == 0);
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

		// call `composite -geometry`
		//
		$cmd = $this->__command(
			'composite',
			" -geometry {$tmp_source->image_width}x{$tmp_source->image_height}+{$destination_x}+{$destination_y} "
                		. escapeshellarg(realpath($tmp_source->source))
                		. " "
                		. escapeshellarg(realpath($tmp_target->target))
                		. " "
                		. escapeshellarg(realpath($tmp_target->target))
                	);

                exec($cmd, $result, $errors);
		return ($errors == 0);
		}

	/**
	* Make the image greyscale: not supported
	*
	* @param Asido_TMP &$tmp
	* @return boolean
	* @access protected
	*/
	function __grayscale(&$tmp) {

		// call `convert -colorspace`
		//
		$cmd = $this->__command(
			'convert',
			" -colorspace GRAY "
              			. escapeshellarg(realpath($tmp->target))
                		. " "
                		. escapeshellarg(realpath($tmp->target))
                	);

                exec($cmd, $result, $errors);
		return ($errors == 0);
		}

	/**
	* Rotate the image clockwise: not supported
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

			// call `convert -rotate`
			//
			$cmd = $this->__command(
				'convert',
				" -rotate {$angle} "
	              			. escapeshellarg(realpath($tmp->target))
	              			. " TIF:"
	              			// ^ 
	              			// GIF saving hack
	              			. escapeshellarg(realpath($tmp->target))
	                	);
	                exec($cmd, $result, $errors);
			if ($errors) {
				return false;
				}

			$w1 = $tmp->image_width;
			$h1 = $tmp->image_height;
			$tmp->image_width = ($angle % 180) ? $h1 : $w1;
			$tmp->image_height = ($angle % 180) ? $w1 : $h1;
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

		// call `convert -crop`
		//
		$cmd = $this->__command(
			'convert',
			" -crop {$width}x{$height}" . ($x < 0 ? "-{$x}" : "+{$x}") . ($y < 0 ? "-{$y}" : "+{$y}")
				. " "
              			. escapeshellarg(realpath($tmp->target))
              			. " "
              			. escapeshellarg(realpath($tmp->target))
                	);

                exec($cmd, $result, $errors);
		return ($errors == 0);
		}

	/**
	* Vertically mirror (flip) the image
	* 
	* @param Asido_TMP &$tmp
	* @return boolean
	* @access protected
	*/
	function __flip(&$tmp) {

		// call `convert -flip`
		//
		$cmd = $this->__command(
			'convert',
			" -flip "
              			. escapeshellarg(realpath($tmp->target))
              			. " "
              			. escapeshellarg(realpath($tmp->target))
                	);

                exec($cmd, $result, $errors);
		return ($errors == 0);
		}

	/**
	* Horizontally mirror (flop) the image
	* 
	* @param Asido_Image &$image
	* @return boolean
	* @access protected
	*/
	function __flop(&$tmp) {

		// call `convert -flop`
		//
		$cmd = $this->__command(
			'convert',
			" -flop "
              			. escapeshellarg(realpath($tmp->target))
              			. " "
              			. escapeshellarg(realpath($tmp->target))
                	);

                exec($cmd, $result, $errors);
		return ($errors == 0);
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
		$t->target = $this->__tmpfile();
		$t->image_width = $width;
		$t->image_height = $height;


		// weird ... only works with absolute names
		//
		fclose(fopen($t->target, 'w'));

		// call `convert -fill`
		//
		$cmd = $this->__command(
			'convert',
                	"-size {$width}x{$height} xc:rgb($r,$g,$b) PNG:"
                		. escapeshellarg(realpath($t->target))
                	);
                exec($cmd, $result, $errors);
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

		// weird ... only works with absolute names
		//
		fclose(fopen($filename, 'w'));

		// call `convert`
		//
		$cmd = $this->__command(
			'convert',
                	escapeshellarg(realpath($handler))
                		. ' PNG:'
				// ^
				// PNG: no pixel losts
                		. escapeshellarg($filename)
                	);

                exec($cmd, $result, $errors);
		if ($errors) {
			return false;
			}

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

		$tmp->source = $this->__tmpfile();
		$tmp->target = $this->__tmpfile();

		// call `identify`
		//
		$cmd = $this->__command(
			'identify',
                	'-format %w:%h:%m '
                		. escapeshellarg(
                			realpath($tmp->source_filename)
                		)
                	);
            	
            	// exec ?
            	//
            	exec($cmd, $result, $errors);
		if ($errors != 0) {
			return false;
			}
			
		// not supported ?
		//
		if (preg_match('~^'
				. preg_quote('identify: No decode delegate for this image format')
				. '~Uis', $result[0])) {
			return false;
			}
		
		// result is not what was expected
		//
		$data  = explode(':', $result[0]);
		if (count($data) < 3) {
			return false;
			}
		
		// supported ... obviously
		//
		$tmp->image_width = $data[0];
		$tmp->image_height = $data[1];

		
		// prepare target
		//
		$cmd = $this->__command(
			'convert',
                	escapeshellarg(realpath($tmp->source_filename))
                		. ' PNG:'
                		. escapeshellarg($tmp->target)
                	);

                exec($cmd, $result, $errors);
		if ($errors) {
			return false;
			}

		// prepare source
		//
		copy($tmp->target, $tmp->source);
		
		return true;
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

		// weird ... only works with absolute names
		//
		fclose(fopen($tmp->target_filename, 'w'));

		if ($tmp->save) {

			// convert and save
			//
			$cmd = $this->__command(
				'convert',
	                	escapeshellarg(realpath($tmp->target))
	                		. ' ' . $this->__mime_map[$tmp->save] . ':'
	                		. escapeshellarg($tmp->target_filename)
	                	);
			} else {

			// no "real" convert, just save
			//
			$cmd = $this->__command(
				'convert',
	                	escapeshellarg(realpath($tmp->target))
	                		. " "
	                		. escapeshellarg($tmp->target_filename)
	                	);
			}

                @exec($cmd, $result, $errors);

		// dispose
		//
		@$this->__destroy_source($tmp);
		@$this->__destroy_target($tmp);

		return ($errors == 0);
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

//--end-of-class--	
}

/////////////////////////////////////////////////////////////////////////////

?>