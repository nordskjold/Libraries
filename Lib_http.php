<?php

	class Lib_http extends Lib_base {
	
		private $post, $get, $file;
	
		private function __clone() {}
		
		function __construct() {
			$this->post = $_POST;
			$this->get = $_GET;
			$this->file = $_FILES;
		}
		
		/**
		 * Checks if $_POST key exists.
		 * 
		 * @param string $post_key
		 * <p>Post key to search for.</p>
		 * 
		 * @return boolean <b>TRUE</b> if it exists, <b>FALSE</b> if it doesn't.
		 */
		public function hasPost($post_key) {
			if(array_key_exists($post_key, $this->post)) {
				return true;
			} else {
				return false;
			}
		}
		
		/**
		 * Checks if $_GET key exists.
		 * 
		 * @param string $post_key
		 * <p>Get key to search for.</p>
		 * 
		 * @return boolean <b>TRUE</b> if it exists, <b>FALSE</b> if it doesn't.
		 */
		public function hasGet($get_key) {
			if(array_key_exists($get_key, $this->get)) {
				return true;
			} else {
				return false;
			}
		}
		
		/**
		 * Checks if $_FILES key exists.
		 * 
		 * @param string $file_key
		 * <p>File key to search for.</p>
		 * 
		 * @return boolean <b>TRUE</b> if it exists, <b>FALSE</b> if it doesn't.
		 */
		public function hasFile($file_key) {
			if(array_key_exists($file_key, $this->file) && ! empty($this->file[$file_key]['name'])) {
				return true;
			} else {
				return false;
			}
		}
		
		/**
		 * Get $_POST value.
		 * 
		 * @param string $post_key [optional]
		 * <p>$_POST key to search for.</p>
		 * 
		 * @return string|array|boolean $_POST value if <i>$post_key</i> is set, else if <i>$post_key</i> isn't set, the entire $_POST array is returned, <b>FALSE</b> if there's no content to return.
		 */
		public function getPost($post_key = null) {
			if($post_key) {
				if(array_key_exists($post_key, $this->post)) {
					return $this->post[$post_key];
				} else {
					return $this->addLibError("Post Http enheden findes ikke.");
				}
			} else {
				return $this->post;
			}
		}
		
		/**
		 * Get $_GET value.
		 * 
		 * @param string $get_key [optional]
		 * <p>$_GET key to search for.</p>
		 * 
		 * @return string|array|boolean $_GET value if <i>$get_key</i> is set, else if <i>$get_key</i> isn't set, the entire $_GET array is returned, <b>FALSE</b> if there's no content to return.
		 */
		public function getGet($get_key = null) {
			if($get_key) {
				if(array_key_exists($get_key, $this->get)) {
					return $this->get[$get_key];
				} else {
					return $this->addLibError("Get Http enheden findes ikke.");
				}
			} else {
				return $this->get;
			}
		}
		
		/**
		 * Get $_FILES value.
		 * 
		 * @param string $file_key [optional]
		 * <p>$_FILES key to search for.</p>
		 * 
		 * @return string|array|boolean $_FILES value if <i>$file_key</i> is set, else if <i>$file_key</i> isn't set, the entire $_FILES array is returned, <b>FALSE</b> if there's no content to return.
		 */
		public function getFile($file_key = null) {
			if($file_key) {
				if(array_key_exists($file_key, $this->file)) {
					return $this->file[$file_key];
				} else {
					return $this->addLibError("Fil Http enheden findes ikke.");
				}
			} else {
				return $this->file;
			}
		}
		
		/**
		 * Gets the http referer value.
		 * 
		 * @return string|boolean The HTTP_REFERER value if it exists, <b>FALSE</b> if it doesn't.
		 */
		public function getHttpReferer() {
			if(array_key_exists('HTTP_REFERER', $_SERVER)) {
				return $_SERVER['HTTP_REFERER'];
			} else {
				return false;
			}
		}
		
		/**
		 * Gets the client IP.
		 * 
		 * @return string The REMOTE_ADDR value if it exists, <b>FALSE</b> if it doesn't.
		 */
		public function getClientIp() {
			if(array_key_exists('REMOTE_ADDR', $_SERVER)) {
				return $_SERVER['REMOTE_ADDR'];
			} else {
				return false;
			}
		}
		
		/**
		 * Gets the URI Query String.
		 * 
		 * @return string The REQUEST_URI value if it exists, <b>FALSE</b> if it doesn't.
		 */
		public function getQueryString() {
			if(array_key_exists('REQUEST_URI', $_SERVER)) {
				return $_SERVER['REQUEST_URI'];
			} else {
				return false;
			}
		}
		
		/**
		 * Gets the server host.
		 * 
		 * @return string The SERVER_NAME value if it exists, <b>FALSE</b> if it doesn't.
		 */
		public function getServerName() {
			if(array_key_exists('SERVER_NAME', $_SERVER)) {
				return $_SERVER['SERVER_NAME'];
			} else {
				return false;
			}
		}
		
		/**
		 * Clears the $_GET scope.
		 * 
		 * @param string $get_key [optional]
		 * <p>$_GET key to search for.</p>
		 * 
		 * @return boolean <b>TRUE</b> on succes, <b>FALSE</b> if key doesn't exist
		 */
		public function clearGetHttp($get_key = null) {
			if(! $get_key) {
				unset($this->get);
			} else {
				if(array_key_exists($get_key, $this->get)) {
					unset($this->get[$get_key]);
				} else {
					return $this->addLibError("Get Http enheden findes ikke.");
				}
			}
			
			return true;
		}
		
		/**
		 * Clears the $_POST scope.
		 * 
		 * @param string $post_key [optional]
		 * <p>$_POST key to search for.</p>
		 * 
		 * @return boolean <b>TRUE</b> on succes, <b>FALSE</b> if key doesn't exist
		 */
		public function clearPostHttp($post_key = null) {
			if(! $post_key) {
				unset($this->post);
			} else {
				if(array_key_exists($post_key, $this->post)) {
					unset($this->post[$post_key]);
				} else {
					return $this->addLibError("Post Http enheden findes ikke.");
				}
			}
			
			return true;
		}
		
		/**
		 * Clears the $_FILES scope.
		 * 
		 * @param string $file_key [optional]
		 * <p>$_FILES key to search for.</p>
		 * 
		 * @return boolean <b>TRUE</b> on succes, <b>FALSE</b> if key doesn't exist
		 */
		public function clearFileHttp($file_key = null) {
			if(! $file_key) {
				unset($this->file);
			} else {
				if(array_key_exists($file_key, $this->file)) {
					unset($this->file[$file_key]);
				} else {
					return $this->addLibError("Fil Http enheden findes ikke.");
				}
			}
			
			return true;
		}
		
		/**
		 * Redirects to another page.
		 * 
		 * @param string $path
		 * <p>Path to the page.</p>
		 */
		public function redirect($path) {
			if(! empty($path)) {
				header('Location: ' .$path);
				die();
			}
		}
	}

?>