<?php

	class Lib_uploader extends Lib_base {
		
		private $file, $file_name, $file_extension, $file_path;
		
		function __construct() {}
		
		private function __clone() {}
		
		/**
		 * Initiates the file upload handler.
		 * 
		 * @param array $file
		 * <p>The file to parse.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the upload is initiated, <b>FALSE</b> if not.
		 */
		public function init($file) {
			$this->file = $file;
			
			if(empty($this->file['name'])) {
				return $this->addLibError("Der blev ikke fundet nogen fil.");
			}
			
			$file_ext = explode('.', $this->file['name']);
			$this->file_extension = strtolower(end($file_ext));
			
			array_pop($file_ext);
			
			$this->file_name = implode('.', $file_ext);
			
			if(Helper_filegroup::getFileGroupFromExt($this->file_extension) == false) {
				return $this->addLibError("Fil typen er ikke understøttet.");
			}
			
			$post_max_size = configuration::getPostMaxSize();
			$max_file_size = substr($post_max_size, 0, (strlen($post_max_size) - 1));
			$max_file_size .= ' ' .strtolower(substr($post_max_size, -1)). 'b';
			
			$tmp_size = explode(' ', $max_file_size);
			
			$size_in_bytes = Helper_converter::convertByteFromTo($tmp_size[0], $tmp_size[1], "b");
			
			if($this->file["size"] > $size_in_bytes) {
				return $this->addLibError("Fil størrelsen er for stor.");
			}
			
			if($this->file["error"] > 0) {
				return $this->addLibError($this->file["error"]);
			}
			
			return true;
		}
		
		/**
		 * Sets the file upload restrictions.
		 * 
		 * @param string $max_file_size [optional]
		 * <p>The max file size of the upload.</p>
		 * <p>( E.g. <b>'10 kb'</b> ) Size and format, seperated by space.</p>
		 * 
		 * @param array $accepted_file_type_group [optional]
		 * <p>The group name of the file type to accept.</p>
		 * 
		 * @param array $accepted_file_type [optional]
		 * <p>The file extensions to accept in the upload.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the restrictions is set, <b>FALSE</b> if the restrictions could not be set.
		 */
		public function setRestrictions($max_file_size = null, array $accepted_file_type_group = null, array $accepted_file_type = null) {
			if(! $this->isLibErrorsEmpty()) {
				return false;
			}
			
			if($max_file_size == null) {
				$post_max_size = configuration::getPostMaxSize();
				$max_file_size = substr($post_max_size, 0, (strlen($post_max_size) - 1));
				$max_file_size .= ' ' .strtolower(substr($post_max_size, -1)). 'b';
			}
			
			$tmp_size = explode(' ', $max_file_size);
			
			$size_in_bytes = Helper_converter::convertByteFromTo($tmp_size[0], $tmp_size[1], "b");
			
			if($this->file["size"] > $size_in_bytes) {
				return $this->addLibError("Fil størrelsen er for stor.");
			}
			
			if($accepted_file_type_group != null || $accepted_file_type != null) {
				if($accepted_file_type_group != null) {
					$isMember = false;
					
					foreach($accepted_file_type_group as $group) {
						return $this->file_extension;
						
						if(Helper_filegroup::isExtMemberOfFileGroup($this->file_extension, $group)) {
							$isMember = true;
						}
					}
					
					if($isMember == false) {
						return $this->addLibError("Fil typen er ikke understøttet.");
					}
				}
				
				if($accepted_file_type != null) {
					$isSupported = false;
					
					foreach($accepted_file_type as $type) {
						if($this->file_extension == $type) {
							$isSupported = true;
						}
					}
					
					if($isSupported == false) {
						return $this->addLibError("Fil typen er ikke understøttet.");
					}
				}
			}
			
			return true;
		}
		
		/**
		 * Sets the file properties.
		 * 
		 * @param string $path
		 * <p>Absolut path from the root to the upload folder.</p>
		 * 
		 * @param string $new_name [optional]
		 * <p>New name for the file.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the properties is set, <b>FALSE</b> if the properties could not be set.
		 */
		public function setProperties($path, $new_name = null) {
			if(! $this->isLibErrorsEmpty()) {
				return false;
			}
			
			$path = trim($path, '/');
			
			if(! file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$path) || ! is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$path)) {
				return $this->addLibError("Ugyldig sti.");
			}
			
			$this->file_path = $path;
			
			if($new_name) {
				$this->file_name = $new_name;
			}
			
			if(file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$this->file_path.DIRECTORY_SEPARATOR.$this->file_name. '.' .$this->file_extension)) {
				return $this->addLibError("Filen eksistere allerede.");
			}
			
			return true;
		}
		
		/**
		 * Upload the file.
		 * 
		 * @return string|boolean Filename on upload success, <b>FALSE</b> if there's an upload error.
		 */
		public function upload() {
			if(! $this->isLibErrorsEmpty()) {
				return false;
			}
			
			move_uploaded_file($this->file["tmp_name"], dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$this->file_path.DIRECTORY_SEPARATOR.$this->file_name. '.' .$this->file_extension);
			
			$return = $this->file_name. '.' .$this->file_extension;
			
			$this->clearObj();
			
			return $return;
		}
		
		/**
		 * Clears the object for re-use.
		 */
		private function clearObj() {
			$this->file = null;
			$this->file_name = null;
			$this->file_extension = null;
			$this->file_path = null;
		}
	}

?>