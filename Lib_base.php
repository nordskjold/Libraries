<?php

	class Lib_base {
		
		protected $root, $lib_path;
		
		public function __construct() {
			$this->root = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR;
			$this->lib_path = dirname(__FILE__);
			
			spl_autoload_extensions('.php');
			spl_autoload_register(array($this, 'helperLoader'));
			spl_autoload_register(array($this, 'libLoader'));
			
			return new libraryFactory();
		}
		
		/**
		 * Helper autoloader.
		 */
		private function helperLoader($class) {
			$file = $this->lib_path. '/helper/' .$class. '.php';
			
			if(! file_exists($file)) {
				return false;
			}
			
			include $file;
		}
		
		/**
		 * Library autoloader.
		 */
		private function libLoader($class) {
			$file = $this->lib_path. '/' .$class. '.php';
			
			if(! file_exists($file)) {
				return false;
			}
			
			include $file;
		}
	}

?>