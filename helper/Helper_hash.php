<?php

	class Helper_hash {
		
		/**
		 * Generates a completely random sequence.
		 * 
		 * @param int $strlen
		 * <p>Length of the generated code.</p>
		 * 
		 * @return string The final code sequence.
		 */
		public static function genCode($strlen = 8) {
			$code = strtr(hash("md5", (string)time()), array("i" => "", "1" => "", "o" => "", "0" => ""));
			$code = substr($code, rand(1, (strlen($code) - (int)$strlen)), $strlen);
			
			return $code;
		}
		
		/**
		 * Appends a salt value to a password and encryption.
		 * 
		 * @param string $password
		 * <p>The password to salt.</p>
		 * 
		 * @return string|boolean The salted password on success, <b>FALSE</b> if not.
		 */
		public static function doPasswordEncryption($password = null) {
			if($password === null || $password === "") {
				return false;
			}
			
			$password = hash("md5", $password.configuration::getPasswordSalt());
			
			return $password;
		}
	}

?>