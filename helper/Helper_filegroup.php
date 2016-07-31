<?php

	class Helper_filegroup {
		
		/**
		 * Gets the file extension group from an extension.
		 * 
		 * @param string $ext
		 * <p>The file extension to search for.</p>
		 * 
		 * @return string|boolean The group name if it exists, <b>FALSE</b> if the group could not be found.
		 */
		public static function getFileGroupFromExt($ext) {
			$mime_types = parse_ini_file(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR. 'library' .DIRECTORY_SEPARATOR. 'flat_files' .DIRECTORY_SEPARATOR. 'mime_types.ini', true);
			
			foreach($mime_types as $group_name => $group_extension) {
				if(array_key_exists($ext, $group_extension)) {
					return $group_name;
				}
			}
			
			return false;
		}
		
		/**
		 * Checks whether a file extension is member of a certian file type group.
		 * 
		 * @param string $ext
		 * <p>The file extension to validate.</p>
		 * 
		 * @param string $group_name
		 * <p>The name of the file type group.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the extension is member of group, else <b>FALSE</b>.
		 */
		public static function isExtMemberOfFileGroup($ext, $group_name) {
			$mime_types = parse_ini_file(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR. 'library' .DIRECTORY_SEPARATOR. 'flat_files' .DIRECTORY_SEPARATOR. 'mime_types.ini', true);
			
			if(! array_key_exists($group_name, $mime_types)) {
				return false;
			}
			
			if(! array_key_exists($ext, $mime_types[$group_name])) {
				return false;
			}
			
			return true;
		}
		
		/**
		 * Gets a files mime content type.
		 * 
		 * @param string $ext
		 * <p>The file extension.</p>
		 * 
		 * @return string The files mime content type.
		 */
		public static function getFileMimeContentType($ext) {
			$mime_types = parse_ini_file(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR. 'library' .DIRECTORY_SEPARATOR. 'flat_files' .DIRECTORY_SEPARATOR. 'mime_types.ini');

			if(array_key_exists($ext, $mime_types)) {
				return $mime_types[$ext];
			} else {
				return 'application/octet-stream';
			}
		}
	}

?>