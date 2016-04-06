<?php
namespace PayPal\Auth\Openid;
use PayPal\Common\PPModel;
/**
 * Error resource
 */
class PPOpenIdError extends PPModel {

		/**
		 * A single ASCII error code from the following enum.
		 * @param string $error
		 */
		 public function setError($error) {
		 	$this->error = $error;
		 	return $this;
		 }
		
		/**
		 * A single ASCII error code from the following enum.
		 * @return string
		 */
		 public function getError() {
		 	return $this->error;
		 }
		 
		/**
		 * A resource ID that indicates the starting resource in the returned results.
		 * @param string $error_description
		 */
		 public function setErrorDescription($error_description) {
		 	$this->error_description = $error_description;
		 	return $this;
		 }
		
		/**
		 * A resource ID that indicates the starting resource in the returned results.
		 * @return string
		 */
		 public function getErrorDescription() {
		 	return $this->error_description;
		 }
		 
		/**
		 * A URI identifying a human-readable web page with information about the error, used to provide the client developer with additional information about the error.
		 * @param string $error_uri
		 */
		 public function setErrorUri($error_uri) {
		 	$this->error_uri = $error_uri;
		 	return $this;
		 }
		
		/**
		 * A URI identifying a human-readable web page with information about the error, used to provide the client developer with additional information about the error.
		 * @return string
		 */
		 public function getErrorUri() {
		 	return $this->error_uri;
		 }
		 

}
