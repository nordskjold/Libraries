<?php

	class Lib_validator extends Lib_base {
		
		function __construct() {}
		
		private function __clone() {}
		
		/**
		 * Checks whether a value is empty.
		 * 
		 * @param mixed $value
		 * <p>The value to check.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the value is empty, <b>FALSE</b> if it isn't.
		 */
		public function isEmpty($value) {
			if($value == null) {
				if(gettype($value) == "boolean") {
					return false;
				}
			} else {
				$value = trim($value);
			}
			
			if(! $value || empty($value)) {
				return true;
			} else {
				return false;
			}
		}
		
		/**
		 * Validates the value to be a string.
		 * 
		 * @param mixed $value
		 * <p>The value to check.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the value is a string, <b>FALSE</b> if it isn't.
		 */
		public function isString($value) {
			if(gettype($value) !== "string") {
				return false;
			} else {
				return true;
			}
		}
		
		/**
		 * Validates the value to only be a string.
		 * 
		 * @param mixed $value
		 * <p>The value to check.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the value is a string, <b>FALSE</b> if it isn't.
		 */
		public function isOnlyString($value) {
			if((bool)preg_match('~[0-9]~', (string)$value) === true) {
				return false;
			} else {
				return true;
			}
		}
		
		/**
		 * Validates if the value has symbols.
		 * 
		 * @param mixed $value
		 * <p>The value to check.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the value has symbols, <b>FALSE</b> if it hasn't.
		 */
		public function hasSymbols($value) {
			return (bool)preg_match('/[^\p{L}\p{N}\s]/u', (string)$value);
		}
		
		/**
		 * Validates the value to be an integer.
		 * 
		 * @param mixed $value
		 * <p>The value to check.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the value is a number, <b>FALSE</b> if it isn't.
		 */
		public function isInt($value) {
			if(! is_numeric($value)) {
				return false;
			} else {
				return true;
			}
		}
		
		/**
		 * Validates the value to only be an integer.
		 * 
		 * @param mixed $value
		 * <p>The value to check.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the value is a number, <b>FALSE</b> if it isn't.
		 */
		public function isOnlyInt($value) {
			if(! ctype_digit((string)$value)) {
				return false;
			} else {
				return true;
			}
		}
		
		/**
		 * Validates the value to be boolean.
		 * 
		 * @param mixed $value
		 * <p>The value to check.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the value is a boolean, <b>FALSE</b> if it isn't.
		 */
		public function isBool($value) {
			if(gettype($value) != "boolean") {
				return false;
			} else {
				return true;
			}
		}
		
		/**
		 * Validates the value to be an array.
		 * 
		 * @param mixed $value
		 * <p>The value to check.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the value is a boolean, <b>FALSE</b> if it isn't.
		 */
		public function isArray($value) {
			if(gettype($value) != "array") {
				return false;
			} else {
				return true;
			}
		}
		
		/**
		 * Validates the value to be an instance of <i>DateTime</i>.
		 * 
		 * @param mixed $value
		 * <p>The value to check.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the value is a date, <b>FALSE</b> if it isn't.
		 */
		public function isDate($value) {
			if(gettype($value) != "object") {
				$value = date_create($value);
			}
			
			if(! $value instanceof DateTime) {
				return false;
			} else {
				return true;
			}
		}
		
		/**
		 * Validates the value to have a minimum string length.
		 * 
		 * @param mixed $value
		 * <p>The value to check.</p>
		 * 
		 * @param int $len
		 * <p>The minimum string length.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the value is longer than minimum, <b>FALSE</b> if it isn't.
		 */
		public function minStrLen($value, $len) {
			if(strlen($value) < $len) {
				return false;
			} else {
				return true;
			}
		}
		
		/**
		 * Validates the value to have a maximum string length.
		 * 
		 * @param mixed $value
		 * <p>The value to check.</p>
		 * 
		 * @param int $len
		 * <p>The maximum string length.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the value is shorter than maximum, <b>FALSE</b> if it isn't.
		 */
		public function maxStrLen($value, $len) {
			if(strlen($value) > $len) {
				return false;
			} else {
				return true;
			}
		}
		
		/**
		 * Validates the value to match system password criterias.
		 * 
		 * @param mixed $value
		 * <p>The value to check.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the value meets system password requirements, <b>FALSE</b> if it doesn't.
		 */
		public function isPassword($value) {
			if(configuration::getPasswordReqNum()) {
				if(preg_match('#\d#', $value) == 0) {
					return false;
				}
			}
			
			if(strlen($value) < configuration::getPasswordMinLength()) {
				return false;
			} else {
				return true;
			}
		}
		
		/**
		 * Validates the value to be an email address.
		 * 
		 * @param mixed $value
		 * <p>The value to check.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the value is an email, <b>FALSE</b> if it isn't.
		 */
		public function isEmail($value) {
			if(! filter_var($value, FILTER_VALIDATE_EMAIL)) {
				return false;
			} else {
				return true;
			}
		}
		
		/**
		 * Validates the value to be an URL address.
		 * 
		 * @param mixed $value
		 * <p>The value to check.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the value is an url, <b>FALSE</b> if it isn't.
		 */
		public function isUrl($value) {
			$pattern = "_^(?:(?:https?|s?ftps?)://)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:/[^\s]*)?\$_iuS";
			
			return (filter_var($value, FILTER_VALIDATE_URL) !== false && preg_match($pattern, $value, $matches) !== false ? true : false);
		}
	}

?>