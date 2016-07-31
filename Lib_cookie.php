<?php

	class Lib_cookie extends Lib_base {
	
		function __construct() {}
		
		private function __clone() {}
		
		/**
		 * Sets a cookie.
		 * 
		 * @param string $key
		 * <p>The name of the cookie.</p>
		 * 
		 * @param string $value
		 * <p>The value of the cookie.</p>
		 * 
		 * @param DateTime $expires
		 * <p>Instance of DateTime object of expiration date of the cookie.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the cookie is set, <b>FALSE</b> on trying to set session cookie.
		 */
		public function setCookie($key, $value, DateTime $expires = null) {
			if($key == configuration::getSessionName()) {
				return $this->addLibError("Login sessionen kan ikke overskrives.");
			}
			
			if($expires instanceof DateTime) {
				setcookie($key, $value, strtotime($expires->format('Y-m-d H:i:s')), "/", configuration::getSystemHostnameRaw());
			} else {
				setcookie($key, $value, 0, "/", configuration::getSystemHostnameRaw());
			}
			
			return true;
		}
		
		/**
		 * Get a cookie.
		 * 
		 * @param string $key
		 * <p>The name of the cookie.</p>
		 * 
		 * @return boolean|array The cookie, <b>FALSE</b> if it doesn't exist.
		 */
		public function getCookie($key) {
			if(! array_key_exists($key, $_COOKIE)) {
				return $this->addLibError("Cookien findes ikke.");
			}
			
			if($key == configuration::getSessionName()) {
				return $this->addLibError("Login cookien kan ikke hentes.");
			}
			
			return $_COOKIE[$key];
		}
		
		/**
		 * Checks whether a cookie exists or not.
		 * 
		 * @param string $key
		 * <p>The name of the cookie.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the cookie exists, <b>FALSE</b> if it doesn't.
		 */
		public function hasCookie($key) {
			if(! array_key_exists($key, $_COOKIE)) {
				return false;
			}
			
			if($key == configuration::getSessionName()) {
				return $this->addLibError("Login cookien kan ikke hentes.");
			}
			
			return true;
		}
		
		/**
		 * Deletes a cookie.
		 * 
		 * @param string $key
		 * <p>The name of the cookie.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the cookie is deleted, <b>FALSE</b> if it wasn't.
		 */
		public function deleteOneCookie($key) {
			if(! array_key_exists($key, $_COOKIE)) {
				return $this->addLibError("Cookien findes ikke.");
			}
			
			if($key == configuration::getSessionName()) {
				return $this->addLibError("Login cookien kan ikke hentes.");
			}
			
			setcookie($key, "", time()-3600);
			
			return true;
		}
		
		/**
		 * Deletes all cookies.
		 */
		public function deleteAllCookie() {
			foreach($_COOKIE as $key => $value) {
				if($key != configuration::getSessionName()) {
					setcookie($key, "", time()-3600);
				}
			}
		}
	}

?>