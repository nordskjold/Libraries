<?php

	class Lib_file_system extends Lib_base {
		
		private $file_system_error;
		
		function __construct() {}
		
		private function __clone() {}
		
		/**
		 * Checks if a file or directory exists.
		 * 
		 * @param string $filepath
		 * <p>Absolut path from the root to the file or directory.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the file or directory exists, <b>FALSE</b> if not.
		 */
		public function fileExists($filepath) {
			return file_exists($this->root.trim($filepath, "/")) ? true : false;
		}
		
		/**
		 * Checks if a file is a directory.
		 * 
		 * @param string $filepath
		 * <p>Absolut path from the root to the file.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the file is a directory, <b>FALSE</b> if not.
		 */
		public function isDir($filepath) {
			return ($this->fileExists($filepath) && is_dir($this->root.trim($filepath, "/"))) ? true : false;
		}
		
		/**
		 * Checks if a file is not a directory.
		 * 
		 * @param string $filepath
		 * <p>Absolut path from the root to the file.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the file is not a directory, <b>FALSE</b> if it is.
		 */
		public function isFile($filepath) {
			return ($this->fileExists($filepath) && ! $this->isDir($filepath)) ? true : false;
		}
		
		/**
		 * Creates a directory.
		 * 
		 * @param string $path
		 * <p>Absolut path from the root.</p>
		 * 
		 * @param string $name
		 * <p>Name of the directory.</p>
		 * 
		 * @return boolean <b>TRUE</b> on success, <b>FALSE</b> if path is wrong or directory already exists.
		 */
		public function createDir($path, $name) {
			$path = trim($path, '/');
			$name = trim($name, '/');
			
			if(! $this->isDir($path.DIRECTORY_SEPARATOR.$name)) {
				return $this->addLibError("Mappen kunne ikke findes.");
			} elseif($this->isDir($path.DIRECTORY_SEPARATOR.$name)) {
				return $this->addLibError("Mappen findes allerede.");
			}
			
			mkdir($this->root.$path.DIRECTORY_SEPARATOR.$name);
			
			clearstatcache();
			
			return true;
		}
		
		/**
		 * Creates a file.
		 * 
		 * @param string $path
		 * <p>Absolut path from the root to where the file is stored.</p>
		 * 
		 * @param string $name
		 * <p>Name of the file, with file extension.</p>
		 * 
		 * @param string $content
		 * <p>Content of the file.</p>
		 * 
		 * @return boolean <b>TRUE</b> on success, <b>FALSE</b> if the file already exists.
		 */
		public function createFile($path, $name, $content = "") {
			$path = trim($path, '/');
			$name = trim($name, '/');
			
			if($this->isFile($path.DIRECTORY_SEPARATOR.$name)) {
				return $this->addLibError("Filen findes allerede.");
			}
			
			file_put_contents($this->root.$path.DIRECTORY_SEPARATOR.$name, $content);
			
			clearstatcache();
			
			return true;
		}
		
		/**
		 * Deletes a directory and all of it's contents.
		 * 
		 * @param string $dir
		 * <p>Absolut path from the root to and name of the directory to delete.</p>
		 * 
		 * @return boolean <b>TRUE</b> on success, <b>FALSE</b> if the directory doesn't exist.
		 */
		public function deleteDir($dir) {
			$dir = trim($dir, '/');
			
			if(! file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$dir) || ! is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$dir)) {
				return $this->addLibError("Mappen kunne ikke findes.");
			}
			
			$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::CHILD_FIRST);
			
			foreach($files as $fileinfo) {
				$remove_method = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
				$remove_method($fileinfo->getRealPath());
			}
			
			rmdir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$dir);
			
			clearstatcache();
			
			return true;
		}
		
		/**
		 * Deletes a file.
		 * 
		 * @param string $file
		 * <p>Absolut path from the root to and file name to delete.</p>
		 * 
		 * @return boolean <b>TRUE</b> on success, <b>FALSE</b> if the file doesn't exist.
		 */
		public function deleteFile($file) {
			$file = trim($file, '/');
			
			if(! file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$file) || is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$file)) {
				return $this->addLibError("Filen kunne ikke findes.");
			}
			
			unlink(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$file);
			
			clearstatcache();
			
			return true;
		}
		
		/**
		 * Renames a directory.
		 * 
		 * @param string $path
		 * <p>Absolut path from the root to the directory.</p>
		 * 
		 * @param string $dir
		 * <p>Name of the directory to rename.</p>
		 * 
		 * @param string $new_name
		 * <p>New name of the directory.</p>
		 * 
		 * @return boolean <b>TRUE</b> on success, <b>FALSE</b> if the directory doesn't exist.
		 */
		public function renameDir($path, $dir, $new_name) {
			$path = trim($path, '/');
			$dir = trim($dir, '/');
			$new_name = trim($new_name, '/');
			
			if(! file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR.$dir) || ! is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR.$dir)) {
				return $this->addLibError("Mappen kunne ikke findes.");
			}
			
			rename(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR.$dir, dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR.$new_name);
			
			clearstatcache();
			
			return true;
		}
		
		/**
		 * Renames a file.
		 * 
		 * @param string $path
		 * <p>Absolut path from the root to the file.</p>
		 * 
		 * @param string $dir
		 * <p>Name of the file to rename.</p>
		 * 
		 * @param string $new_name
		 * <p>New name of the file.</p>
		 * 
		 * @return boolean <b>TRUE</b> on success, <b>FALSE</b> if the file doesn't exist.
		 */
		public function renameFile($path, $file, $new_name) {
			$path = trim($path, '/');
			$file = trim($file, '/');
			$new_name = trim($new_name, '/');
			
			if(! file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR.$file) || is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR.$file)) {
				return $this->addLibError("Filen kunne ikke findes.");
			}
			
			rename(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR.$file, dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR.$new_name);
			
			clearstatcache();
			
			return true;
		}
		
		/**
		 * Copies a directory and all of it's contents.
		 * 
		 * @param string $dir
		 * <p>Absolut path from the root and name of the directory to copy.</p>
		 * 
		 * @param string $new_dir_dst
		 * <p>Absolut path from the root to the new destination.</p>
		 * 
		 * @return boolean <b>TRUE</b> on success, <b>FALSE</b> if the directory doesn't exist or can't be read.
		 */
		public function copyDir($dir, $new_dir_dst) {
			$dir = trim($dir, '/');
			$new_dir_dst = trim($new_dir_dst, '/');
			
			if(! file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$dir) || ! is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$dir)) {
				return $this->addLibError("Mappen kunne ikke findes.");
			}
			
			$dir_stream = opendir($dir);
			@mkdir($new_dir_dst);
			
			while(false !== ($file = readdir($dir_stream))) {
				if(($file != '.') && ($file != '..')) {
					if(is_dir($dir.DIRECTORY_SEPARATOR.$file)) {
						recurse_copy($dir.DIRECTORY_SEPARATOR.$file,$new_dir_dst.DIRECTORY_SEPARATOR.$file);
					} else {
						copy($dir.DIRECTORY_SEPARATOR.$file,$new_dir_dst.DIRECTORY_SEPARATOR.$file);
					}
				}
			}
			
			closedir($dir_stream);
			
			clearstatcache();
			
			return true;
		}
		
		/**
		 * Copies a file.
		 * 
		 * @param string $file
		 * <p>Absolut path from the root to the file.</p>
		 * 
		 * @param string $new_file_dst
		 * <p>Absolut path from the root to the new destination.</p>
		 * 
		 * @return boolean <b>TRUE</b> on success, <b>FALSE</b> if the file doesn't exist.
		 */
		public function copyFile($file, $new_file_dst) {
			$file = trim($file, '/');
			$new_file_dst = trim($new_file_dst, '/');
			
			if(! file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$file) || is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$file)) {
				return $this->addLibError("Filen kunne ikke findes.");
			}
			
			copy(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$file, dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$new_file_dst);
			
			clearstatcache();
			
			return true;
		}
		
		/**
		 * Moves a directory and all of it's contents.
		 * 
		 * @param string $dir
		 * <p>Absolut path from the root to the directory.</p>
		 * 
		 * @param string $new_dir_dst
		 * <p>Absolut path from the root to the new destination.</p>
		 * 
		 * @return boolean <b>TRUE</b> on success, <b>FALSE</b> if the directory doesn't exist.
		 */
		public function moveDir($dir, $new_dir_dst) {
			$dir = trim($dir, '/');
			
			if(! file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$dir) || ! is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$dir)) {
				return $this->addLibError("Mappen kunne ikke findes.");
			}
			
			$this->copyDir($dir, $new_dir_dst);
			$this->deleteDir($dir);
			
			clearstatcache();
			
			return true;
		}
		
		/**
		 * Moves a file.
		 * 
		 * @param string $file
		 * <p>Absolut path from the root to the file.</p>
		 * 
		 * @param string $new_file_dst
		 * <p>Absolut path from the root to the new destination.</p>
		 * 
		 * @return boolean <b>TRUE</b> on success, <b>FALSE</b> if the file doesn't exist.
		 */
		public function moveFile($file, $new_file_dst) {
			$file = trim($file, '/');
			
			if(! file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$file) || is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$file)) {
				return $this->addLibError("Filen kunne ikke findes.");
			}
			
			$this->copyFile($file, $new_file_dst);
			$this->deleteFile($file);
			
			clearstatcache();
			
			return true;
		}
		
		/**
		 * Reads a files content.
		 * 
		 * @param string $file
		 * <p>Absolut path from the root to the file.</p>
		 * 
		 * @return string|boolean File content on success, <b>FALSE</b> if the file can't be read.
		 */
		public function getFileContent($file) {
			$file = trim($file, '/');
			
			if(! file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$file) || is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$file)) {
				return $this->addLibError("Filen kunne ikke findes.");
			}
			
			$content = file_get_contents(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$file);
			
			return $content;
		}
		
		/**
		 * Gets all files in a directory.
		 * 
		 * @param string $dir
		 * <p>Absolut path from the root to the directory.</p>
		 * 
		 * @return array|boolean Array of files on success, <b>FALSE</b> if the directory doesn't exist.
		 */
		public function getFilesInFolder($dir) {
			$dir = trim($dir, '/');
			
			if(! file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$dir) || ! is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$dir)) {
				return $this->addLibError("Mappen kunne ikke findes.");
			}
			
			$files = new RecursiveDirectoryIterator($dir);
			$final = array();
			
			foreach($files as $fileinfo) {
				if($fileinfo->isDir()) {
					$final['dir'][] = $fileinfo->getFilename();
				} else {
					$final['file'][] = $fileinfo->getFilename();
				}
			}
			
			clearstatcache();
			
			return $final;
		}
		
		/**
		 * Gets a file size formatted.
		 * 
		 * @param string $file
		 * <p>Absolut path from the root to the file or directory.</p>
		 * 
		 * @param string $format
		 * <p>Which format to return, default is bytes.</p>
		 * <p>( E.g. <b>'kb', 'mb', 'gb'</b> )</p>
		 * 
		 * @return float|boolean Size on success, <b>FALSE</b> if the file or directory doesn't exist.
		 */
		public function getFileSize($file, $format = null) {
			$file = trim($file, '/');
			
			if(! file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$file)) {
				return $this->addLibError("Filen kunne ikke findes.");
			}
			
			if(is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$file)) {
				return $this->addLibError("Filen er en mappe.");
			}
			
			$size = (float)filesize(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$file);
			
			if($format) {
				$size = Helper_converter::convertByteFromTo($size, "b", $format);
			}
			
			clearstatcache();
			
			return $size;
		}
		
		/**
		 * Gets file extension from file name.
		 * 
		 * @param string $path
		 * <p>Absolut path from the root to where the file is stored.</p>
		 * 
		 * @return string|boolean The file extension on success, <b>FALSE</b> if the file doesn't exist.
		 */
		public function getFileExtension($path) {
			$path = trim($path, '/');
			
			if(! file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$path) || is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$path)) {
				return false;
			}
			
			$splitted = explode('.', $path);
			
			clearstatcache();
			
			return end($splitted);
		}
		
		/**
		 * Gets file name from file path.
		 * 
		 * @param string $path
		 * <p>Absolut path from the root to where the file is stored.</p>
		 * 
		 * @param boolean $with_ext [optional]
		 * <p><b>TRUE</b> for file extension, <b>FALSE</b> for file name trimmed.</p>
		 * <p>Default: <b>FALSE</b></p>
		 * 
		 * @return string The file name.
		 */
		public function getFileNameFromFilePath($path, $with_ext = null) {
			$path = trim($path, '/');
			
			if(! file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$path) || is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$path)) {
				return $this->addLibError("Filen kunne ikke findes.");
			}
			
			$splittet = explode('/', $path);
			$file_expl = explode('.', end($splittet));
			
			if(! $with_ext) {
				array_pop($file_expl);
			}
			
			$file_name = implode('.', $file_expl);
			
			return $file_name;
		}
		
		/**
		 * Forces a file download to the client.
		 * 
		 * @param string $path
		 * <p>Absolut path from the root to the file with file extension.</p>
		 * 
		 * @param string $file_name
		 * <p>New file name without file extension.</p>
		 */
		public function forceDownload($path, $file_name) {
			$path = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.trim($path, '/');

			$path_splittet = explode('.', $path);
			$file_ext = '.' .end($path_splittet);

			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename=' .$file_name.$file_ext);
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' .filesize($path));
			ob_clean();
            flush();
			readfile($path);
			exit;
		}
	}

?>