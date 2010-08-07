<?php
/**
* @author Kaloyan K. Tsvetkov <kaloyan@kaloyan.info>
* @license http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License Version 2.1
* @package Asido
* @subpackage Asido.Core
* @version $Id: class.image.php 7 2007-04-09 21:09:09Z mrasnika $
*/

/////////////////////////////////////////////////////////////////////////////

/**
* Asido Image
*
* @package Asido
* @subpackage Asido.Core
*/
Class Asido_Image {

	/**
	* Source Image 
	*
	* This is the image that is the source for the 
	* image operations
	*
	* @var string
	* @access protected
	*/
	var $__source = null;

	/**
	* Target Image
	*
	* This is the image that we are going to write 
	* the results from the image operations
	*
	* @var string
	* @access protected
	*/
	var $__target = null;

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
	/**
	* Operation Queue
	* @var array
	* @access protected
	*/
	var $__operations = array();
	
	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* Constructor
	*
	* @param string $source source image for the image operations
	* @param string $target target image for the image operations
	*/
	function Asido_Image($source=null, $target=null) {
		$this->source($source);
		$this->target($target);
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
	/**
	* Get\Set source image
	*
	* @param string $new_source
	* @return string
	* @access public
	*/
	function source($new_source = null) {
		
		// set new source
		//
		if (isset($new_source)) {
			
			// is it readable
			//
			if (!is_readable($new_source)) {
				trigger_error(
					sprintf(
						'Not storing source file "%s", '
							. ' because it is not '
							. ' readable',
						$new_source
						),
					E_USER_WARNING 
					);
				} else {
				$this->__source = $new_source;
				}
			}
		
		// return source
		//
		return $this->__source;
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* Get\Set target image
	*
	* @param string $new_target
	* @return string
	* @access public
	*/
	function target($new_target = null) {

		// set new target
		//
		if (isset($new_target)) {
			
			// is ti writable
			//
			if (!is_writable(dirname($new_target))) {
				trigger_error(
					sprintf(
						'Not storing target file "%s", '
							. ' because target file '
							. ' directory "%s" is '
							. ' not writable',
						$new_target,
						dirname($new_target)
						),
					E_USER_WARNING 
					);
				} else {
				$this->__target = $new_target;
				}
			}

		// return target
		//
		return $this->__target;
		}
	
	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
	/**
	* Store an operation in the image operation queue
	*
	* @param callback $callback
	* @param array $params
	*
	* @access public
	*/
	function operation($callback, $params) {
		$o = new stdClass;
		$o->callback = $callback;
		$o->params = $params;
		$this->__operations[] = $o;
		}
	
	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
	/**
	* Save the image after being processed
	*
	* @param mixed $overwrite_mode if this mode is ON 
	*		({@link ASIDO_OVERWRITE_ENABLED}), and there is already a file 
	*		with the same as the target, this method will throw a 
	*		warning stating that you are abut to overwrite an 
	*		existing file
	* @return boolean
	*
	* @access public
	*/
	function save($overwrite_mode = ASIDO_OVERWRITE_DISABLED) {

		// operations ?
		//
		if (empty($this->__operations)) {
			trigger_error(
				'Nothing to save, there are no image operations queued',
				E_USER_WARNING
				);
			return false;
			}
		
		// check files
		//
		if (!is_writable(dirname($this->__target))) {
			trigger_error(
				sprintf(
					'Target file directory "%s" is not writable',
					dirname($this->__target)
					),
				E_USER_WARNING
				);
			return false;
			}
		if (!is_readable($this->__source)) {
			trigger_error(
				sprintf(
					'Source file "%s" is not readable',
					$this->__source
					),
				E_USER_WARNING
				);
			return false;
			}
		if (ASIDO_OVERWRITE_DISABLED == $overwrite_mode) {
			if (file_exists($this->__target)) {
				trigger_error(
					sprintf(
						'Overwriting target file "%s" is not allowed',
						$this->__target
						),
					E_USER_WARNING
					);				
				return false;
				}
			}
		
		// init ?
		//
		if (!$tmp = call_user_func_array(
				array(
					&$this->__operations[0]->callback[0],
					'prepare'
					),
				array(&$this)
				)
			) {
			trigger_error(
				'Failed to initialize image operations',
				E_USER_WARNING
				);
			return false;
			}

		// do the queue
		//
		$failed = false;
		foreach ($this->__operations as $o) {
			
			// callback exists ?
			//
			if (!is_callable($o->callback)) {
				trigger_error(
					sprintf(
						'Operation %s::%s() not found',
						get_class($o->callback[0]),
						$o->callback[1]
						),
					E_USER_WARNING 
					);
				continue;
				}
			
			// do call it
			//
			$o->params['tmp'] =& $tmp;
			if (!call_user_func_array($o->callback, $o->params)) {
				trigger_error(
					sprintf(
						'Failed performing %s::%s()',
						get_class($o->callback[0]),
						$o->callback[1]
						),
					E_USER_WARNING 
					);
				$failed = true;
				}
			}
		
		// save ?
		//
		if (!call_user_func_array(
				array(
					&$this->__operations[0]->callback[0],
					'save'
					),
				array($tmp)
				)
			) {
			trigger_error(
				'Failed to save the result of the image operations',
				E_USER_WARNING
				);
			return false;
			}
		
		return $failed;
		}
	
	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
//--end-of-class--	
}

/////////////////////////////////////////////////////////////////////////////

/**
* Asido Temporary Object
*
* @package Asido
* @subpackage Asido.Core
*/
Class Asido_TMP {

	/**
	* Object for processing the source image
	* @var mixed
	* @access public
	*/
	var $source;

	/**
	* Source image filename
	* @var string
	* @access public
	*/
	var $source_filename;
	
	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
	/**
	* Object for processing the target image
	* @var mixed
	* @access public
	*/
	var $target;

	/**
	* Filename with which to save the processed file
	* @var string
	* @access public
	*/
	var $target_filename;

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* Image width
	* @var integer
	* @access public
	*/
	var $image_width;

	/**
	* Image height
	* @var integer
	* @access public
	*/
	var $image_height;

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
	/**
	* MIME-type with which to save the processed file
	* @var string
	* @access public
	*/
	var $save;

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
//--end-of-class--	
}

/////////////////////////////////////////////////////////////////////////////

?>