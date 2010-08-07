<?php
/**
* Asido Imate Resizing Solution
*
* Asido is a PHP (PHP4/PHP5) image processing solution
*
* @author Kaloyan K. Tsvetkov <kaloyan@kaloyan.info>
* @license http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License Version 2.1
*
* @package Asido
* @subpackage Asido.Core
* @version $Id: class.asido.php 7 2007-04-09 21:09:09Z mrasnika $
*/

/////////////////////////////////////////////////////////////////////////////

/**
* backward compatibility: the DIR_SEP constant isn't used anymore
*/
if(!defined('DIR_SEP')) {
	define('DIR_SEP', DIRECTORY_SEPARATOR);
	}
/**
* backward compatibility: the PATH_SEPARATOR constant is availble since 4.3.0RC2
*/
if (!defined('PATH_SEPARATOR')) {
	define('PATH_SEPARATOR', OS_WINDOWS ? ';' : ':');
        }

// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

/**
* Set the ASIDO_DIR constant up with the absolute path to Asido files. If it is 
* not defined, include_path will be used. Set ASIDO_DIR only if any other module 
* or application has not already set it up.
*/
if (!defined('ASIDO_DIR')) {
	define('ASIDO_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);
	}

// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

/**
* Constant for declaring a proportional resize
* @see Asido::Resize()
*/
define('ASIDO_RESIZE_PROPORTIONAL', 1001);

/**
* Constant for declaring a strech resize
* @see Asido::Resize()
*/
define('ASIDO_RESIZE_STRETCH', 1002);

/**
* Constant for declaring a fitting resize
* @see Asido::Resize()
*/
define('ASIDO_RESIZE_FIT', 1003);

// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

/**
* Constant for declaring overwriting the target file if it exists
* @see Asido_Image::Save()
*/
define('ASIDO_OVERWRITE_ENABLED', 2001);

/**
* Constant for declaring NOT overwriting the target file if it exists
* @see Asido_Image::Save()
*/
define('ASIDO_OVERWRITE_DISABLED', 2002);

// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

/**
* Constant for declaring watermark position
* @see Asido::Watermark()
*/
define('ASIDO_WATERMARK_TOP_LEFT', 3001);

/**
* Constant for declaring watermark position
* @see Asido::Watermark()
*/
define('ASIDO_WATERMARK_TOP_CENTER', 3002);

/**
* Constant for declaring watermark position
* @see Asido::Watermark()
*/
define('ASIDO_WATERMARK_TOP_RIGHT', 3003);

/**
* Constant for declaring watermark position
* @see Asido::Watermark()
*/
define('ASIDO_WATERMARK_MIDDLE_LEFT', 3004);

/**
* Constant for declaring watermark position
* @see Asido::Watermark()
*/
define('ASIDO_WATERMARK_MIDDLE_CENTER', 3005);

/**
* Constant for declaring watermark position
* @see Asido::Watermark()
*/
define('ASIDO_WATERMARK_MIDDLE_RIGHT', 3006);

/**
* Constant for declaring watermark position
* @see Asido::Watermark()
*/
define('ASIDO_WATERMARK_BOTTOM_LEFT', 3007);

/**
* Constant for declaring watermark position
* @see Asido::Watermark()
*/
define('ASIDO_WATERMARK_BOTTOM_CENTER', 3008);

/**
* Constant for declaring watermark position
* @see Asido::Watermark()
*/
define('ASIDO_WATERMARK_BOTTOM_RIGHT', 3009);

/**
* Constant for declaring watermark position
* @see Asido::Watermark()
*/
define('ASIDO_WATERMARK_TILE', 3010);

// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

/**
* Constant for declaring watermark position
* @see Asido::Watermark()
*/
define('ASIDO_WATERMARK_NORTH_WEST', ASIDO_WATERMARK_TOP_LEFT);

/**
* Constant for declaring watermark position
* @see Asido::Watermark()
*/
define('ASIDO_WATERMARK_NORTH', ASIDO_WATERMARK_TOP_CENTER);

/**
* Constant for declaring watermark position
* @see Asido::Watermark()
*/
define('ASIDO_WATERMARK_NORTH_EAST', ASIDO_WATERMARK_TOP_RIGHT);

/**
* Constant for declaring watermark position
* @see Asido::Watermark()
*/
define('ASIDO_WATERMARK_WEST', ASIDO_WATERMARK_MIDDLE_LEFT);

/**
* Constant for declaring watermark position
* @see Asido::Watermark()
*/
define('ASIDO_WATERMARK_CENTER', ASIDO_WATERMARK_MIDDLE_CENTER);

/**
* Constant for declaring watermark position
* @see Asido::Watermark()
*/
define('ASIDO_WATERMARK_MIDDLE', ASIDO_WATERMARK_MIDDLE_CENTER);

/**
* Constant for declaring watermark position
* @see Asido::Watermark()
*/
define('ASIDO_WATERMARK_EAST', ASIDO_WATERMARK_MIDDLE_RIGHT);

/**
* Constant for declaring watermark position
* @see Asido::Watermark()
*/
define('ASIDO_WATERMARK_SOUTH_WEST', ASIDO_WATERMARK_BOTTOM_LEFT);

/**
* Constant for declaring watermark position
* @see Asido::Watermark()
*/
define('ASIDO_WATERMARK_SOUTH', ASIDO_WATERMARK_BOTTOM_CENTER);

/**
* Constant for declaring watermark position
* @see Asido::Watermark()
*/
define('ASIDO_WATERMARK_SOUTH_EAST', ASIDO_WATERMARK_BOTTOM_RIGHT);

// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

/**
* Constant for declaring watermark scalable
* @see Asido::Watermark()
*/
define('ASIDO_WATERMARK_SCALABLE_ENABLED', 4001);

/**
* Constant for declaring watermark scalable factor
* @see Asido::Watermark()
*/
define('ASIDO_WATERMARK_SCALABLE_FACTOR', 0.25);

/**
* Constant for declaring watermark not scalable
* @see Asido::Watermark()
*/
define('ASIDO_WATERMARK_SCALABLE_DISABLED', 4002);

// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

/**
* Alias for JPEG mime-type
* @see Asido::Convert()
*/
define('ASIDO_MIME_JPEG', 'image/jpeg');

/**
* Alias for GIF mime-type
* @see Asido::Convert()
*/
define('ASIDO_MIME_GIF', 'image/gif');

/**
* Alias for PNG mime-type
* @see Asido::Convert()
*/
define('ASIDO_MIME_PNG', 'image/png');

// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

/**
* Constant for declaring what type of support the current driver offers
* @see Asido::is_format_supported()
*/
define('ASIDO_SUPPORT_READ', 5001);

/**
* Constant for declaring what type of support the current driver offers
* @see Asido::is_format_supported()
*/
define('ASIDO_SUPPORT_WRITE', 5002);

/**
* Constant for declaring what type of support the current driver offers
* @see Asido::is_format_supported()
*/
define('ASIDO_SUPPORT_READ_WRITE', 5003);

/////////////////////////////////////////////////////////////////////////////

/**
* @see Asido_Driver}
*/
include_once ASIDO_DIR . "class.driver.php";

/**
* @see Asido_Image}
*/
include_once ASIDO_DIR . "class.image.php";

/////////////////////////////////////////////////////////////////////////////

/**
* Asido API
*
* This class stores the Asido API for some basic image-processing
* operations like resizing, watermarking and converting.
*
* @package Asido
* @subpackage Asido.Core
*/
Class Asido {

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* Get version of Asido release
	*
	* @return string
	* @access public
	* @static
	*/
	function version() {
		return '0.0.0.1';
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
	/**
	* Set a driver
	*
	* Set a driver as active by providing its name as argument to this static method
	*
	* @param string $driver_name
	* @return boolean
	*
	* @access public
	* @static
	*/
	function driver($driver_name) {
		
		// class exists ?
		//
		if (class_exists($c = asido::__driver_classname($driver_name))) {
			asido::_driver(new $c);
			return true;
			}
		
		// file exists ?
		//
		if (!$fp = @fopen(
				$f = asido::__driver_filename($driver_name), 'r', 1)
			) {
			trigger_error(
				sprintf(
					'Asido driver file "%s" (for driver "%s") '
						. ' not found for including',
					$f,
					$driver_name
					),
				E_USER_ERROR
				);
			return false;
			}
		fclose($fp);

		// include it
		//
		require_once($f);
		
		// file loaded, check again ...
		//
		if (class_exists($c)) {
			asido::_driver(new $c);
			return true;
			}
		
		trigger_error(
			sprintf(
				'Asido driver class "%s" (for driver "%s") not found',
				$c,
				$driver_name
				),
			E_USER_ERROR
			);
		return false;
		}

	/**
	* Compose the filename for a driver
	*
	* If you want to use a different mechanism for composing driver's 
	* filename, then override this method in a subclass of {@link Asido}
	*
	* @param string $driver_name
	* @return string
	*
	* @access protected
	* @static
	*/
	function __driver_filename($driver_name) {
		return ASIDO_DIR . sprintf('class.driver.%s.php', strtolower($driver_name));
		}

	/**
	* Compose the classname for a driver
	*
	* If you want to use a different mechanism for composing driver's 
	* classname, then override this method in a subclass of {@link Asido}
	*
	* @param string $driver_name
	* @return string
	*
	* @access protected
	* @static
	*/
	function __driver_classname($driver_name) {
		return 'Asido_driver_' . $driver_name;
		}
	
	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
	/**
	* Get the supported mime-types by the loaded driver
	*
	* @param mixed $mode
	* @return array
	* @access public
	* @static
	*/
	function get_supported_types($mode=ASIDO_SUPPORT_READ_WRITE) {
		
		$d =& asido::_driver();

		// no driver ?
		//
		if (!is_a($d, 'asido_driver')) {
			trigger_error('No Asido driver loaded',
				E_USER_WARNING
				);
			return false;
			}
		
		return $d->get_supported_types($mode);
		}

	/**
	* Checks whether a mime-type is supported
	*
	* @param string $mime_type
	* @param mixed $mode
	* @return array
	* @access public
	* @static
	*/
	function is_format_supported($mime_type, $mode=ASIDO_SUPPORT_READ_WRITE) {
		
		$d =& asido::_driver();

		// no driver ?
		//
		if (!is_a($d, 'asido_driver')) {
			trigger_error('No Asido driver loaded',
				E_USER_WARNING
				);
			return false;
			}
		
		return $d->supported(strToLower($mime_type), $mode);
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
	/**
	* Get\Set the instance of Asido driver
	*
	* @param Asido_Driver $driver 
	* @return Asido_Driver
	*
	* @internal using static array in order to store a reference in a static variable
	*
	* @access private
	* @static
	*/
	function &_driver($driver=null) {
		
		static $_d = array();
		
		if (isset($driver)) {
			
			// is it a driver ?
			//
			if (!is_a($driver, 'Asido_Driver')) {
				trigger_error(
					sprintf(
						'The class you are attempting to '
							. ' load "%s" is not an '
							. ' Asido driver',
						get_class($driver)
						),
					E_USER_ERROR
					);
				return false;
				}
			
			// is it compatible ?
			//
			if (!$driver->is_compatible()) {
				trigger_error(
					sprintf(
						'The class you are attempting to load '
							. ' "%s" as Asido driver is '
							. ' not compatible',
						get_class($driver)
						),
					E_USER_ERROR
					);
				return false;
				}
			
			$_d[0] =& $driver;
			}

		return $_d[0];		
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
	/**
	* Get a new image object
	*
	* @param string $source source image for the image operations
	* @param string $target target image for the image operations
	* @return Asido_Image
	*
	* @access public
	* @static
	*/
	function image($source=null, $target=null) {
		return new Asido_Image($source, $target);
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
	/**
	* Resize an image
	*
	* Use this method to resize a previously created {@link Asido_Image} 
	* object. The resize operation can be performed in three modes. The 
	* proportional mode set by ASIDO_RESIZE_PROPORTIONAL will attempt to fit 
	* the image inside the "frame" create by the $width and $height arguments, 
	* while the stretch mode set by ASIDO_RESIZE_STRETCH will stretch the 
	* image if necessary to fit into that "frame". The "fitting" mode set by 
	* ASIDO_RESIZE_FIT will attempt to resize the image proportionally only if 
	* it does not fit inside the "frame" set by the provided width and height: 
	* if it does fit, the image will not be resized at all.
	*
	* @param Asido_Image &$image
	* @param integer $width
	* @param integer $height
	* @param mixed $mode mode for resizing the image:
	*	either ASIDO_RESIZE_STRETCH or ASIDO_RESIZE_PROPORTIONAL or ASIDO_RESIZE_FIT 
	* @return boolean
	*
	* @access public
	* @static
	*/
	function resize(&$image, $width, $height, $mode=ASIDO_RESIZE_PROPORTIONAL) {
		return asido::_operation(
				$image, __FUNCTION__,
				array(
					'width' => $width,
					'height' => $height,
					'mode' => $mode,
				)
			);
		}

	/**
	* Resize an image by making it fit a particular width
	*
	* Use this method to resize a previously created {@link Asido_Image} 
	* object by making it fit a particular width while keeping the
	* proportions ratio.
	*
	* @param Asido_Image &$image
	* @param integer $width
	* @return boolean
	*
	* @access public
	* @static
	*/
	function width(&$image, $width) {
		return asido::_operation(
				$image, 'resize',
				array(
					'width' => $width,
					'height' => 0,
					'mode' => ASIDO_RESIZE_PROPORTIONAL,
				)
			);
		}

	/**
	* Resize an image by making it fit a particular height
	*
	* Use this method to resize a previously created {@link Asido_Image} 
	* object by making it fit a particular height while keeping the
	* proportions ratio.
	*
	* @param Asido_Image &$image
	* @param integer $height
	* @return boolean
	*
	* @access public
	* @static
	*/
	function height(&$image, $height) {
		return asido::_operation(
				$image, 'resize',
				array(
					'width' => 0,
					'height' => $height,
					'mode' => ASIDO_RESIZE_PROPORTIONAL,
				)
			);
		}

	/**
	* Resize an image by stretching it by the provided width and height
	*
	* Use this method to resize a previously created {@link Asido_Image} 
	* object by stretching it to fit a particular height without keeping
	* the proportions ratio.
	*
	* @param Asido_Image &$image
	* @param integer $width
	* @param integer $height
	* @return boolean
	*
	* @access public
	* @static
	*/
	function stretch(&$image, $width, $height) {
		return asido::_operation(
				$image, 'resize',
				array(
					'width' => $width,
					'height' => $height,
					'mode' => ASIDO_RESIZE_STRETCH,
				)
			);
		}

	/**
	* Resize an image by "fitting" in the provided width and height
	*
	* Use this method to resize a previously created {@link Asido_Image} 
	* object if it is bigger then the "frame" set by the provided width and 
	* height: if it is smaller it will not be resized
	*
	* @param Asido_Image &$image
	* @param integer $width
	* @param integer $height
	* @return boolean
	*
	* @access public
	* @static
	*/
	function fit(&$image, $width, $height) {
		return asido::_operation(
				$image, 'resize',
				array(
					'width' => $width,
					'height' => $height,
					'mode' => ASIDO_RESIZE_FIT,
				)
			);
		}


	/**
	* Resize an image by "framing" it with the provided width and height
	*
	* Use this method to resize a previously created {@link Asido_Image} 
	* object by placing it inside the "frame" set by the provided width and 
	* height. First the image will be resized in the same manner as {@link 
	* Asido::fit()} does, and then it will be placed in the center of a canvas 
	* with the proportions of the provided width and height (achieving a 
	* "Passepartout" framing effect). The background of the "passepartout" 
	* is set by the $color argument
	*
	* @param Asido_Image &$image
	* @param integer $width
	* @param integer $height
	* @param Asido_Color $color	passepartout background
	* @return boolean
	*
	* @access public
	* @static
	*/
	function frame(&$image, $width, $height, $color=null) {
		return asido::_operation(
				$image, __FUNCTION__,
				array(
					'width' => $width,
					'height' => $height,
					'color' => $color,
				)
			);
		}

	/**
	* Convert an image from one file-type to another
	*
	* Use this method to convert a previously created {@link Asido_Image} 
	* object from its original file-type to another.
	*
	* @param Asido_Image &$image
	* @param string $mime_type MIME type of the file-type to which this image should be converted to
	* @return boolean
	*
	* @access public
	* @static
	*/
	function convert(&$image, $mime_type) {
		return asido::_operation(
				$image, __FUNCTION__,
				array(
					'mime' => $mime_type
				)
			);
		}
	
	/**
	* Watermark an image
	*
	* Use this method to watermark a previously create {@link Asido_Image} 
	* object. You can set the position of the watermark (the gravity) by using 
	* each of the nine available "single" positions (single means the 
	* watermark will appear only once), or the "tile" position, which applied 
	* the watermark all over the image like a tiled wallpaper. If the 
	* watermark image is larger than the image that is supposed to be 
	* watermarked you can shrink the watermark image: the scale of its 
	* shrinking is determined by the $scalable_factor argument.
	* 
	* @param Asido_Image &$image
	* @param string $watermark_image path to the file which is going to be use as watermark
	* @param mixed $position position(gravity) of the watermark: the 
	*	available values are ASIDO_WATERMARK_TOP_LEFT, 
	*	ASIDO_WATERMARK_TOP_CENTER, ASIDO_WATERMARK_TOP_RIGHT, 
	*	ASIDO_WATERMARK_MIDDLE_LEFT, ASIDO_WATERMARK_MIDDLE_CENTER, 
	*	ASIDO_WATERMARK_MIDDLE_RIGHT, ASIDO_WATERMARK_BOTTOM_LEFT, 
	*	ASIDO_WATERMARK_BOTTOM_CENTER, ASIDO_WATERMARK_BOTTOM_RIGHT and 
	*	ASIDO_WATERMARK_TILE
	* @param mixed $scalable whether to shrink the watermark or not if the 
	*	watermark image is bigger than the image that is supposed to be 
	*	watermarked. 
	* @param float $scalable_factor watermark scaling factor
	* @return boolean
	*
	* @access public
	* @static
	*/
	function watermark(&$image, $watermark_image,
			$position = ASIDO_WATERMARK_BOTTOM_RIGHT,
			$scalable = ASIDO_WATERMARK_SCALABLE_ENABLED,
			$scalable_factor = ASIDO_WATERMARK_SCALABLE_FACTOR
			) {
		return asido::_operation(
				$image, __FUNCTION__,
				array(
					'watermark_image' => $watermark_image,
					'position' => $position,
					'scalable' => $scalable,
					'scalable_factor' => $scalable_factor
				)
			);
		}

	/**
	* Grayscale the provided image
	* 
	* @param Asido_Image &$image
	* @return boolean
	*
	* @access public
	* @static
	*/
	function grayscale(&$image) {
		return asido::_operation(
				$image, __FUNCTION__,
				array()
			);
		}

	/**
	* Grayscale the provided image
	* 
	* @param Asido_Image &$image
	* @return boolean
	*
	* @access public
	* @static
	*/	
	function greyscale(&$image) {
		return Asido::grayscale($image);
		}

	/**
	* Rotate the provided image (clockwise)
	* 
	* @param Asido_Image &$image
	* @param float $angle 
	* @param Asido_Color $color	background color for when non-rectangular angles are used
	* @return boolean
	*
	* @access public
	* @static
	*/
	function rotate(&$image, $angle, $color=null) {
		return asido::_operation(
				$image, __FUNCTION__,
				array(
					'angle' => $angle,
					'color' => $color,
					)
			);
		}

	/**
	* Return an color object ({@link Asido_Color}) with the provided RGB channels
	*
	* @param integer $red	the value has to be from 0 to 255
	* @param integer $green	the value has to be from 0 to 255
	* @param integer $blue	the value has to be from 0 to 255
	* @return Asido_Color
	* @access public
	* @static
	*/
	function color($red, $green, $blue) {
		$color = new Asido_Color;
		$color->set($red, $green, $blue);
		return $color;
		}

	/**
	* Copy an image onto an already created {@link Acudo_Image} object
	*
	* @param Asido_Image &$image
	* @param string $applied_image	filepath to the image that is going to be copied
	* @param integer $x
	* @param integer $y
	* @return boolean
	* @access public
	* @static
	*/
	function copy(&$image, $applied_image, $x, $y) {
		return asido::_operation(
				$image, __FUNCTION__,
				array(
					'image' => $applied_image,
					'x' => $x,
					'y' => $y
					)
			);
		}

	/**
	* Crop an already created {@link Acudo_Image} object
	*
	* @param Asido_Image &$image
	* @param integer $x
	* @param integer $y
	* @param integer $width
	* @param integer $height
	* @return boolean
	* @access public
	* @static
	*/
	function crop(&$image, $x, $y, $width, $height) {
		return asido::_operation(
				$image, __FUNCTION__,
				array(
					'x' => $x,
					'y' => $y,
					'width' => $width,
					'height' => $height
					)
			);
		}

	/**
	* Creates a vertical mirror (flip) by reflecting the pixels around the central X-axis
	* 
	* @param Asido_Image &$image
	* @return boolean
	*
	* @access public
	* @static
	*/
	function flip(&$image) {
		return asido::_operation(
				$image, __FUNCTION__,
				array()
			);
		}

	/**
	* Creates a horizontal mirror (flop) by reflecting the pixels around the central Y-axis
	* 
	* @param Asido_Image &$image
	* @return boolean
	*
	* @access public
	* @static
	*/
	function flop(&$image) {
		return asido::_operation(
				$image, __FUNCTION__,
				array()
			);
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* Store an operation to image's queue of operations
	*
	* @param Asido_Image &$image
	* @param string $operation
	* @param array $params
	* @return boolean
	*
	* @access private
	* @static
	*/
	function _operation(&$image, $operation, $params) {

		$d =& asido::_driver();

		// no driver ?
		//
		if (!is_a($d, 'asido_driver')) {
			trigger_error('No Asido driver loaded',
				E_USER_WARNING
				);
			return false;
			}

		
		// not an image ?
		//
		if (!is_a($image, 'Asido_Image')) {
			trigger_error(
				sprintf(
					'The image you are attempting to '
						. ' \'%s\' ("%s") is not a '
						. ' valid Asido image object',
					$operation,
					get_class($image)
					),
				E_USER_ERROR
				);
			return false;
			}

		// queue operation
		//
		$p = array_merge(
			array(
				'tmp' => null
				),
			$params
			);
		
		$image->operation(
			array(&$d, $operation),
			$p
			);
		return true;
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* Output an error when trying to access an abstract method
	*
	* @param string $class
	* @param string $function
	*
	* @internal this is a small hack for not being able to use PHP5's OOP 
	*	    apparatus when designing abstract classes and methods
	*
	* @access public
	* @static
	*/
	function trigger_abstract_error($class, $function) {
		trigger_error(
			sprintf(
				'Cannot run the abstract method %s::%s()',
				$class,
				$function
				),
			E_USER_ERROR
			);
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

//--end-of-class--	
}

/////////////////////////////////////////////////////////////////////////////

/**
* Asido Color
*
* This class stores common color-related routines
*
* @package Asido
* @subpackage Asido.Core
*/
Class Asido_Color {

	/**
	* Red Channel
	* @var integer
	* @access private
	*/
	var $_red = 0;
	
	/**
	* Green Channel
	* @var integer
	* @access private
	*/
	var $_green = 0;
	
	/**
	* Blue Channel
	* @var integer
	* @access private
	*/
	var $_blue = 0;

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* Set a new color
	*
	* @param integer $red	the value has to be from 0 to 255
	* @param integer $green	the value has to be from 0 to 255
	* @param integer $blue	the value has to be from 0 to 255
	* @access public
	*/
	function set($red, $green, $blue) {
		$this->_red = $red % 256;
		$this->_green = $green % 256;
		$this->_blue = $blue % 256;
		}

	/**
	* Get the stored color
	*
	* @return array	indexed array with three elements: one for each channel following the RGB order
	* @access public
	*/
	function get() {
		return array(
			$this->_red % 256,
			$this->_green % 256,
			$this->_blue % 256
			);
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

//--end-of-class--	
}

/////////////////////////////////////////////////////////////////////////////

?>