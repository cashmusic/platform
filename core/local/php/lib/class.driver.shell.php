<?php
/**
* @author Kaloyan K. Tsvetkov <kaloyan@kaloyan.info>
* @license http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License Version 2.1
* @package Asido
* @subpackage Asido.Driver
* @version $Id: class.driver.shell.php 7 2007-04-09 21:09:09Z mrasnika $
*/

/////////////////////////////////////////////////////////////////////////////

/**
* Common file for all "shell" based solutions 
*
* @package Asido
* @subpackage Asido.Driver
*
* @abstract
*/
Class Asido_Driver_Shell Extends Asido_Driver {
	
	/**
	* Path to the executables
	* @var string
	* @access private
	*/
	var $__exec = '';

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
	/**
	* Try to locate the program
	* @param string $program
	* @return string
	*
	* @access protected
	*/
	function __exec($program) {

    		// safe mode ?
    		//
		if (!ini_get('safe_mode') || !$path = ini_get('safe_mode_exec_dir')) {
        		($path = getenv('PATH')) || ($path = getenv('Path'));
    			}

		$executable = false;
		$p = explode(PATH_SEPARATOR, $path);
		$p[] = getcwd();

		$ext = array();		
		if (OS_WINDOWS) {
			$ext = getenv('PATHEXT')
					? explode(PATH_SEPARATOR, getenv('PATHEXT'))
					: array('.exe','.bat','.cmd','.com');
		
			// extension ?
			//
			array_unshift($ext, '');
			}

		// walk the variants
		//
		foreach ($ext as $e) {
			foreach ($p as $dir) {
				$exe = $dir . DIRECTORY_SEPARATOR . $program . $e;

				// *nix only implementation
				//
				if (OS_WINDOWS ? is_file($exe) : is_executable($exe)) {
					$executable = $exe;
					break;
					}
				}
			}

		return $executable;
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* Run a command
	* @param string $program
	* @param string $args
	* @return string
	* @access protected
	*/
	function __command($program, $args = '') {
		return $this->__exec . $program . ' ' . $args;
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
		return unlink($tmp->source);
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
		return unlink($tmp->target);
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
//--end-of-class--	
}

/////////////////////////////////////////////////////////////////////////////

?>