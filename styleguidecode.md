#Making The Code Stuffs Look Pretty#
When in doubt, code for legibility and easy adoption. Capitalization and CamelCase should be used for class names, camelCase starting with lowercase for function names, and variable names in all lowercase with words separated by underscores. Indentation has been kept simple — a single hard tab for each level, with curly brackets on the same line as the control statement.

So a simplified file will look something like:

	/**
	 * Description
	 *
	 * @package seed.org.cashmusic
	 * @author CASH Music
	 * @link http://cashmusic.org/
	 *
	 * Copyright (c) 2011, CASH Music
	 * Licensed under the Affero General Public License version 3.
	 * See http://www.gnu.org/licenses/agpl-3.0.html
	 *
	 */class ClassName {
		protected $variable;
	
		/**
		 * Function Description
		 *
		 * @return value
		 */public function functionName($input_variable) {
			$variable_name = 'text';
			return $variable_name;
		}
	} // END class 

Each class should have a file of it's own.

No class is final without formatted comments.