<?php

	class Lib_ftp extends Lib_base {
		
		private $connection;
		private $ftp_wrapper;
		
		function __construct() {}
		
		private function __clone() {}
		
		/**
		 * Initiates the default FTP connection.
		 * 
		 * @return boolean <b>TRUE</b> on succes, <b>FALSE</b> if connection fails.
		 */
		public function initFtpConnection() {
			return $this->addFtpConnection(configuration::getFtpHost(), configuration::getFtpUsername(), configuration::getFtpPassword(), configuration::getFtpPort());
		}
		
		/**
		 * Initiates the FTP connection.
		 * This is only required if another connection than the system default is wanted.
		 * 
		 * @param string $ftp_host
		 * <p>The FTP hostname.</p>
		 * 
		 * @param string $ftp_username
		 * <p>The FTP username.</p>
		 * 
		 * @param string $ftp_password
		 * <p>The FTP password.</p>
		 * 
		 * @param int $ftp_port [optional]
		 * <p>The connection port number.</p>
		 * 
		 * @return boolean <b>TRUE</b> on succes, <b>FALSE</b> if connection fails.
		 */
		public function addFtpConnection($ftp_host, $ftp_username, $ftp_password, $ftp_port = 21) {
			if(! $this->connection = @ftp_connect($ftp_host, $ftp_port)) {
				return $this->addLibError("Kunne ikke forbinde til FTP serveren.");
			}
			
			if(! @ftp_login($this->connection, $ftp_username, $ftp_password)) {
				return $this->addLibError("Kunne ikke autentificere login informationerne.");
			}
			
			if(! @ftp_pasv($this->connection, TRUE)) {
				return $this->addLibError("Kunne ikke sætte forbindelsen til passiv.");
			}
			
			$this->ftp_wrapper = 'ftps://' .$ftp_username. ':' .$ftp_password. '@' .$ftp_host;
			
			if($this->getLibError() === false) {
				return true;
			} else {
				return false;
			}
		}
		
		/**
		 * Check if a file exists on the server.
		 * NOTE: Does not work on directories, use <i>checkIsDir</i>
		 * 
		 * @param string $file
		 * <p>Absolut path from the root to the file or directory.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the file exists, <b>FALSE</b> if it doesn't.
		 */
		public function checkFileExists($file) {
			if(empty($this->connection) && $this->initFtpConnection() === false) {
				return false;
			}
			
			$file = trim($file, '/');
			
			if(@ftp_size($this->connection, DIRECTORY_SEPARATOR.$file) === -1) {
				return false;
			} else {
				return true;
			}
		}
		
		/**
		 * Check if a file is a directory.
		 * 
		 * @param string $dir
		 * <p>Absolut path from the root to the directory.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the file is a directory, <b>FALSE</b> if it isn't.
		 */
		public function checkIsDir($dir) {
			if(empty($this->connection) && $this->initFtpConnection() === false) {
				return false;
			}
			
			$dir = trim($dir, '/');
			$current_dir = ftp_pwd($this->connection);
			
			if($current_dir !== false && @ftp_chdir($this->connection, $dir)) {
				@ftp_chdir($this->connection, $current_dir);
				return true;
			}

			return false;
		}
		
		/**
		 * List of files in a directory.
		 * 
		 * @param string $dir
		 * <p>Absolut path from the root to the directory.</p>
		 * 
		 * @param boolean $raw_list
		 * <p><b>TRUE</b> for a detailed list of file info, <b>FALSE</b> for a simple list.</p>
		 * 
		 * @return array|boolean Array list of files, <b>FALSE</b> if an error occured.
		 * 
		 * <p>Array example of simple list:</p>
		 * <p>
		 * <table>
		 * <tr valign="top"><td><b>KEY</b></td><td><b>VALUE</b></td></tr>
		 * <tr valign="top"><td>0</td><td>foler_1</td></tr>
		 * <tr valign="top"><td>1</td><td>foler_2</td></tr>
		 * <tr valign="top"><td>2</td><td>file_1</td></tr>
		 * </table>
		 * </p>
		 * 
		 * <p>Array example of detailed list:</p>
		 * <p>
		 * <table>
		 * <tr valign="top"><td><b>KEY</b></td><td><b>VALUE</b></td></tr>
		 * <tr valign="top"><td>FolderName</td><td>Array[]</td></tr>
		 * <tr valign="top"><td></td><td>time</td><td>1991</td></tr>
		 * <tr valign="top"><td></td><td>day</td><td>18</td></tr>
		 * <tr valign="top"><td></td><td>month</td><td>Mar</td></tr>
		 * <tr valign="top"><td></td><td>size</td><td>4096</td></tr>
		 * <tr valign="top"><td></td><td>group</td><td>group_name</td></tr>
		 * <tr valign="top"><td></td><td>owner</td><td>owner_name</td></tr>
		 * <tr valign="top"><td></td><td>number</td><td>3</td></tr>
		 * <tr valign="top"><td></td><td>permissions</td><td>drwxr-xr-x</td></tr>
		 * <tr valign="top"><td></td><td>type</td><td>directory</td></tr>
		 * <tr valign="top"><td></td><td>datetime</td><td>DateTime()</td></tr>
		 * </table>
		 * </p>
		 */
		public function getFilesInFolder($dir, $raw_list = false) {
			if(empty($this->connection) && $this->initFtpConnection() === false) {
				return false;
			}
			
			if($this->checkIsDir($dir) !== true) {
				return $this->addLibError("Filen er ikke en mappe.");
			}
			
			$dir = trim($dir, '/');
			
			if($raw_list) {
				$list = @ftp_rawlist($this->connection, DIRECTORY_SEPARATOR.$dir);
				
				if(is_array($list)) {
					$items = array();

					foreach($list as $child) {
						$chunks = preg_split("/\s+/", $child);
						list($item['permissions'], $item['number'], $item['owner'], $item['group'], $item['size'], $item['month'], $item['day'], $item['time']) = $chunks;
						
						$item['type'] = $chunks[0]{0} === 'd' ? 'directory' : 'file';
						
						$date = new DateTime($item['month']. ' ' .$item['day']. ' ' .$item['time']);
						$item['datetime'] = $date;
						
						array_splice($chunks, 0, 8);
						
						$items[implode(" ", $chunks)] = $item;
					}

					$list = $items;
				}
			} else {
				$list = @ftp_nlist($this->connection, DIRECTORY_SEPARATOR.$dir);
				
				if(! empty($list)) {
					foreach($list as $key => $item) {
						$list[$key] = str_replace(DIRECTORY_SEPARATOR.$dir.DIRECTORY_SEPARATOR, "", $item);
					}
				}
			}
			
			return $list;
		}
		
		/**
		 * Converts raw permission code to readable numbers.
		 * 
		 * @param string $permission
		 * <p>Raw permission string to convert.</p>
		 * 
		 * @return string The permission in readable numbers.
		 */
		public function convertPermissionsToNumbers($permission) {
			$trans = array('-' => '0', 'r' => '4', 'w' => '2', 'x' => '1');
			$permission = substr(strtr($permission, $trans), 1);
			$array = str_split($permission, 3);
			return array_sum(str_split($array[0])).array_sum(str_split($array[1])).array_sum(str_split($array[2]));
		}
		
		/**
		 * Changes the rights of a file or directory.
		 * 
		 * @param int $mode
		 * <p>The mod code.</p>
		 * 
		 * @param string $file_path
		 * <p>Absolut path from the root to the file or directory.</p>
		 * 
		 * @return boolean <b>TRUE</b> on succes, <b>FALSE</b> if change fails.
		 */
		public function changeRights($mode, $file_path) {
			if(empty($this->connection) && $this->initFtpConnection() === false) {
				return false;
			}
			
			$file_path = trim($file_path, '/');
			
			if($this->checkFileExists(DIRECTORY_SEPARATOR.$file_path) === false && $this->checkIsDir(DIRECTORY_SEPARATOR.$file_path) === false) {
				return $this->addLibError("Filen eksistere ikke.");
			}
			
			if(! @ftp_chmod($this->connection, $mode, DIRECTORY_SEPARATOR.$file_path)) {
				return $this->addLibError("Kunne ikke ændre rettighederne.");
			}
			
			return true;
		}
		
		/**
		 * Creates a directory.
		 * 
		 * @param string $dir_name
		 * <p>Name of the directory.</p>
		 * 
		 * @param string $path [optional]
		 * <p>Absolut path from the root to the directory.</p>
		 * <p>Default: Creates directory in root.</p>
		 * 
		 * @return boolean <b>TRUE</b> on succes, <b>FALSE</b> if creation fails.
		 */
		public function createDir($dir_name, $path = null) {
			if(empty($this->connection) && $this->initFtpConnection() === false) {
				return false;
			}
			
			$dir_name = trim($dir_name, '/');
			
			if($path) {
				$path = trim($path, '/');

				if($this->checkIsDir(DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR.$dir_name) === true) {
					return $this->addLibError("Filen eksistere allerede.");
				}
				
				if(! @ftp_mkdir($this->connection, DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR.$dir_name)) {
					return $this->addLibError("Kunne ikke oprette mappen.");
				}
			} else {
				if($this->checkIsDir(DIRECTORY_SEPARATOR.$dir_name) === true) {
					return $this->addLibError("Filen eksistere allerede.");
				}
				
				if(! @ftp_mkdir($this->connection, DIRECTORY_SEPARATOR.$dir_name)) {
					return $this->addLibError("Kunne ikke oprette mappen.");
				}
			}
			
			return true;
		}
		
		/**
		 * Renames a directory.
		 * 
		 * @param string $old_dir
		 * <p>Absolut path from the root to the directory.</p>
		 * 
		 * @param string $new_dir
		 * <p>Absolut path from the root to the new directory.</p>
		 * 
		 * @return boolean <b>TRUE</b> on succes, <b>FALSE</b> if rename fails.
		 */
		public function renameDir($old_dir, $new_dir) {
			return $this->renameFile($old_dir, $new_dir);
		}
		
		/**
		 * Moves a directory.
		 * 
		 * @param string $old_dir
		 * <p>Absolut path from the root to the directory.</p>
		 * 
		 * @param string $new_dir
		 * <p>Absolut path from the root to the new directory.</p>
		 * 
		 * @return boolean <b>TRUE</b> on succes, <b>FALSE</b> if rename fails.
		 */
		public function moveDir($old_dir, $new_dir) {
			return $this->renameDir($old_dir, $new_dir);
		}
		
		/**
		 * Deletes a directory.
		 * 
		 * @param string $dir_name
		 * <p>Absolut path from the root to the directory.</p>
		 * 
		 * @return boolean <b>TRUE</b> on succes, <b>FALSE</b> if deletion fails.
		 */
		public function deleteDir($dir_name) {
			if(empty($this->connection) && $this->initFtpConnection() === false) {
				return false;
			}
			
			$dir_name = trim($dir_name, '/');
			
			if($this->checkIsDir(DIRECTORY_SEPARATOR.$dir_name) === false) {
				return $this->addLibError("Mappen eksistere ikke.");
			}
			
			if(! @ftp_rmdir($this->connection, DIRECTORY_SEPARATOR.$dir_name) ) {
				$filelist = $this->getFilesInFolder(DIRECTORY_SEPARATOR.$dir_name);
				
				foreach($filelist as $file) {
					$file = DIRECTORY_SEPARATOR.$dir_name.DIRECTORY_SEPARATOR.$file;
					
					if($this->checkIsDir($file)) {
						$this->deleteDir($file);
					} else {
						$this->deleteFile($file);
					}
				}

				if(! @ftp_rmdir($this->connection, DIRECTORY_SEPARATOR.$dir_name)) {
					return $this->addLibError("Kunne ikke slette mappen.");
				}
			}
			
			return true;
		}
		
		/**
		 * Creates a file.
		 * 
		 * @param string $file_name
		 * <p>Name of the file.</p>
		 * 
		 * @param string $file_content
		 * <p>String content to parse to the file.</p>
		 * 
		 * @return boolean <b>TRUE</b> on succes, <b>FALSE</b> if creation fails.
		 */
		public function createFile($file_name, $file_content) {
			if(empty($this->connection) && $this->initFtpConnection() === false) {
				return false;
			}
			
			$file_name = trim($file_name, '/');
			
			if($this->checkFileExists(DIRECTORY_SEPARATOR.$file_name) === true) {
				return $this->addLibError("Filen eksistere allerede.");
			}
			
			$stream = fopen('php://temp', 'r+');
			fwrite($stream, $file_content);
			rewind($stream);
			
			if(! ftp_fput($this->connection, DIRECTORY_SEPARATOR.$file_name, $stream, FTP_ASCII)) {
				fclose($stream);
				return $this->addLibError("Kunne ikke oprette filen.");
			}
			
			fclose($stream);
			
			return true;
		}
		
		/**
		 * Reads a file into a variable.
		 * 
		 * @param string $file_name
		 * <p>Absolut path from the root to the file.</p>
		 * 
		 * @return string|boolean File content on succes, <b>FALSE</b> if opening the file fails.
		 */
		public function readFile($file_name) {
			if(empty($this->connection) && $this->initFtpConnection() === false) {
				return false;
			}
			
			$file_name = trim($file_name, '/');
			
			if($this->checkFileExists(DIRECTORY_SEPARATOR.$file_name) === false) {
				return $this->addLibError("Filen eksistere allerede.");
			}
			
			$content = file_get_contents($this->ftp_wrapper.DIRECTORY_SEPARATOR.$file_name);
			
			if($content === false) {
				return $this->addLibError("Kunne ikke læse filen.");
			}
			
			return $content;
		}
		
		/**
		 * Renames a file or directory.
		 * 
		 * @param string $old_file
		 * <p>Absolut path from the root to the file or directory.</p>
		 * 
		 * @param string $new_file
		 * <p>Absolut path from the root to the new file or directory.</p>
		 * 
		 * @return boolean <b>TRUE</b> on succes, <b>FALSE</b> if rename fails.
		 */
		public function renameFile($old_file, $new_file) {
			if(empty($this->connection) && $this->initFtpConnection() === false) {
				return false;
			}
			
			$old_file = trim($old_file, '/');
			$new_file = trim($new_file, '/');
			
			if($this->checkFileExists(DIRECTORY_SEPARATOR.$old_file) === false && $this->checkIsDir(DIRECTORY_SEPARATOR.$old_file) === false) {
				return $this->addLibError("Filen eksistere ikke.");
			}
			
			if($this->checkFileExists(DIRECTORY_SEPARATOR.$new_file) === true && $this->checkIsDir(DIRECTORY_SEPARATOR.$new_file) === true) {
				return $this->addLibError("Filen eksistere allerede.");
			}
			
			if(! @ftp_rename($this->connection, DIRECTORY_SEPARATOR.$old_file, DIRECTORY_SEPARATOR.$new_file)) {
				return $this->addLibError("Kunne ikke omdøbe filen.");
			}
			
			return true;
		}
		
		/**
		 * Moves a file.
		 * 
		 * @param string $old_file
		 * <p>Absolut path from the root to the file.</p>
		 * 
		 * @param string $new_file
		 * <p>Absolut path from the root to the new file.</p>
		 * 
		 * @return boolean <b>TRUE</b> on succes, <b>FALSE</b> if rename fails.
		 */
		public function moveFile($old_file, $new_file) {
			return $this->renameFile($old_file, $new_file);
		}
		
		/**
		 * Deletes a file.
		 * 
		 * @param string $file_name
		 * <p>Absolut path from the root to the file.</p>
		 * 
		 * @return boolean <b>TRUE</b> on succes, <b>FALSE</b> if deletion fails.
		 */
		public function deleteFile($file_name) {
			if(empty($this->connection) && $this->initFtpConnection() === false) {
				return false;
			}
			
			$file_name = trim($file_name, '/');
			
			if($this->checkFileExists(DIRECTORY_SEPARATOR.$file_name) === false) {
				return $this->addLibError("Filen eksistere ikke.");
			}
			
			if(! @ftp_delete($this->connection, DIRECTORY_SEPARATOR.$file_name)) {
				return $this->addLibError("Kunne ikke slette filen.");
			}
			
			return true;
		}
		
		/**
		 * Resets and closes open connections.
		 */
		function __destruct() {
			if(! empty($this->connection)) {
				if(! @ftp_close($this->connection)) {
					return $this->addLibError("Kunne ikke lukke forbindelsen.");
				}
			}
		}
	}

?>