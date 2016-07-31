<?php

	class Lib_IMAP extends Lib_base {
		
		private $connection, $connection_root_host, $connection_host, $connection_port, $connection_username, $connection_password;
		
		function __construct() {}
		
		private function __clone() {}
		
		/**
	 	 * Adds a connection to an IMAP server.
		 * 
		 * @param string $imap_host
		 * <p>The IMAP server hostname.</p>
		 * <p>( E.g. <b>'imap.gmail.com'</b> )</p>
		 * 
		 * @param int $imap_port
		 * <p>The IMAP server port number.</p>
		 * 
		 * @param string $imap_username
		 * <p>The IMAP server authentication username.</p>
		 * <p>Commongly an email addresse.</p>
		 * 
		 * @param string $imap_password
		 * <p>The IMAP server authentication password.</p>
		 * 
		 * @param string $imap_auth [optional]
		 * <p>The <i>$imap_auth</i> changes the default connection authentication from <b>'notls'</b>.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the connection is established, <b>FALSE</b> if not.
		 */
		public function addIMAP($imap_host, $imap_port, $imap_username, $imap_password, $imap_auth = "notls") {
			return $this->imapConnect($imap_host, $imap_port, $imap_username, $imap_password, $imap_auth);
		}
		
		/**
	 	 * Adds a connection to an IMAP server from a list of pre-defined servers.
		 * 
		 * @param string $imap_server
		 * <p>The IMAP server host prefix to search for.</p>
		 * <p>( E.g. <b>'gmail'</b> )</p>
		 * 
		 * @param string $imap_username
		 * <p>The IMAP server authentication username.</p>
		 * <p>Commongly an email addresse.</p>
		 * 
		 * @param string $imap_password
		 * <p>The IMAP server authentication password.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the connection is established, <b>FALSE</b> if not.
		 */
		public function addIMAPFromSupportedServers($imap_server, $imap_username, $imap_password) {
			$servers = parse_ini_file(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR. 'library' .DIRECTORY_SEPARATOR. 'flat_files' .DIRECTORY_SEPARATOR. 'IMAP_servers.ini', true);
			
			if(array_key_exists($imap_server, $servers)) {
				return $this->imapConnect($servers[$imap_server]['host'], $servers[$imap_server]['port'], $imap_username, $imap_password, $servers[$imap_server]['auth']);
			} else {
				return false;
			}
		}
		
		/**
	 	 * Adds a connection to an IMAP server.
		 * 
		 * @param string $imap_host
		 * <p>The IMAP server hostname.</p>
		 * <p>( E.g. <b>'imap.gmail.com'</b> )</p>
		 * 
		 * @param int $imap_port
		 * <p>The IMAP server port number.</p>
		 * 
		 * @param string $imap_username
		 * <p>The IMAP server authentication username.</p>
		 * <p>Commongly an email addresse.</p>
		 * 
		 * @param string $imap_password
		 * <p>The IMAP server authentication password.</p>
		 * 
		 * @param string $imap_auth [optional]
		 * <p>The <i>$imap_auth</i> changes the default connection authentication from <b>'notls'</b>.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the connection is established, <b>FALSE</b> if not.
		 */
		private function imapConnect($imap_host, $imap_port, $imap_username, $imap_password, $imap_auth) {
			$this->connection_root_host = '{' .$imap_host. ':' .$imap_port. '/' .$imap_auth. '}';
			$this->connection_host = '{' .$imap_host. ':' .$imap_port. '/' .$imap_auth. '}';
			$this->connection_port = $imap_port;
			$this->connection_username = $imap_username;
			$this->connection_password = $imap_password;
			$this->connection = imap_open($this->connection_host, $this->connection_username, $this->connection_password);
			
			if($this->connection === false) {
				return $this->addLibError("Kunne ikke forbinde til IMAP serveren.");
			}
			
			return true;
		}
		
		/**
	 	 * Gets a list of folders from an IMAP server.
		 * 
		 * @param string $pattern [optional]
		 * <p>The folder search pattern.</p>
		 * 
		 * @return array|boolean The folder list array on success, <b>FALSE</b> if folders weren't retrieved.
		 */
		public function getFolderList($pattern = "*") {
			if(! $this->connection) {
				return $this->addLibError("Var ikke forbundet til IMAP serveren.");
			}
			
			$folders = imap_list($this->connection, $this->connection_root_host, $pattern);
			
			if(is_array($folders)) {
				foreach($folders as $folder) {
					$folder_clean = str_replace($this->connection_root_host, "", $folder);
					$exploded = explode("/", $folder_clean);
					$mailbox = end($exploded);
					$mailbox = utf8_encode(imap_utf7_decode($mailbox));
					$stripped[$folder] = $mailbox;
				}
				
				return $stripped;
			} else {
				return $this->addLibError("Kunne ikke hente mappelisten.");
			}
		}
		
		/**
	 	 * Gets a folder status from an IMAP server.
		 * 
		 * @param string $folder_name
		 * <p>The folder name search pattern.</p>
		 * 
		 * @return array|boolean The folder status array on success, <b>FALSE</b> if status weren't retrieved.
		 */
		public function getFolderStatus($folder_name) {
			if(! $this->connection_host) {
				return $this->addLibError("Var ikke forbundet til IMAP serveren.");
			}
			
			$folders = $this->getFolderList();
			
			$exists = false;
			$BIN_folder_name = bin2hex($folder_name);
			
			foreach($folders as $folder_host => $folder) {
				$BIN_folder = str_replace("00", "", bin2hex($folder));
				
				if($BIN_folder_name == $BIN_folder) {
					$exists = true;
				}
			}
			
			if($exists === true) {
				$host = $this->connection_root_host.imap_utf7_encode($folder_name);
				
				if(! imap_status($this->connection, $host, SA_ALL)) {
					return $this->addLibError("Kunne ikke hente mappe status.");
				} else {
					$status = imap_status($this->connection, $host, SA_ALL);
					
					$status_rebuild = array(
						"messages" => $status->messages,
						"recent" => $status->recent,
						"unseen" => $status->unseen,
						"uidnext" => $status->uidnext,
						"uidvalidity" => $status->uidvalidity
					);
					
					return $status_rebuild;
				}
			} else {
				return $this->addLibError("Kunne ikke hente mappe status.");
			}
		}
		
		/**
	 	 * Changes the internal connection folder pointer.
		 * 
		 * @param string $folder
		 * <p>The folder name to change pointer to.</p>
		 * 
		 * @return boolean <b>TRUE</b> on success, <b>FALSE</b> if folder pointer wasn't changed.
		 */
		private function changeFolder($folder) {
			if(! $this->connection_host) {
				return $this->addLibError("Var ikke forbundet til IMAP serveren.");
			}
			
			$folders = $this->getFolderList();
			
			$host = array_search($folder, $folders);
			
			if($host) {
				$this->connection_host = $host;
				$this->connection = imap_open($this->connection_host, $this->connection_username, $this->connection_password);

				if($this->connection === false) {
					return $this->addLibError("Kunne ikke skifte mappe.");
				}
				
				return true;
			} else {
				return $this->addLibError("Mappen kunne ikke findes.");
			}
		}
		
		/**
		 * Retrieve mails from a folder.
		 * 
		 * @param boolean $headers_only [optional]
		 * <p>If <i>$headers_only</i> is set to <b>TRUE</b>, the email content will not be fetched (Saves on ressources for listing of mails).</p>
		 * 
		 * @param string $folder [optional]
		 * <p>The folder which to fetch mails from.</p>
		 * 
		 * @param int $limit [optional]
		 * <p>The amount of mails to fetch.</p>
		 * 
		 * @return array|boolean Fetched emails in an array, <b>FALSE</b> if mails couldn't be fetched.
		 * 
		 * <p>Email return array example:</p>
		 * <p>
		 * <table>
		 * <tr valign="top"><td><b>KEY</b></td><td><b>VALUE</b></td></tr>
		 * <tr valign="top"><td>uid</td><td>1</td></tr>
		 * <tr valign="top"><td>from_email</td><td>from@mail.com</td></tr>
		 * <tr valign="top"><td>from_name</td><td>John Doe</td></tr>
		 * <tr valign="top"><td>reply_email</td><td>reply@mail.com</td></tr>
		 * <tr valign="top"><td>reply_name</td><td>Jane Doe</td></tr>
		 * <tr valign="top"><td>subject</td><td>Email Subject</td></tr>
		 * <tr valign="top"><td>unseen</td><td><b>TRUE</b></td></tr>
		 * <tr valign="top"><td>flagged</td><td><b>TRUE</b></td></tr>
		 * <tr valign="top"><td>answered</td><td><b>TRUE</b></td></tr>
		 * <tr valign="top"><td>deleted</td><td><b>TRUE</b></td></tr>
		 * <tr valign="top"><td>draft</td><td><b>TRUE</b></td></tr>
		 * <tr valign="top"><td>size</td><td>128</td></tr>
		 * <tr valign="top"><td>date</td><td><b>DateTime Object</b></td></tr>
		 * </table>
		 * </p>
		 */
		public function getMailsFromFolder($headers_only = false, $folder = "INBOX", $limit = false) {
			if(! $this->connection) {
				return $this->addLibError("Var ikke forbundet til IMAP serveren.");
			}
			
			$this->changeFolder($folder);
			
			$emails = false;
			
			if(imap_num_msg($this->connection) > 0) {
				if($limit === false) {
					$num_msgs = imap_num_msg($this->connection);
				} else {
					$num_msgs = (int)$limit;
				}
				
				for($i = 1; $i <= $num_msgs; $i++) {
					$header = imap_header($this->connection, $i);
					
					$from = $header->from[0];
					$reply_to = @$header->reply_to[0];
					$uid = imap_uid($this->connection, $i);
					
					$unseen = trim($header->Unseen);
					$flagged = trim($header->Flagged);
					$answered = trim($header->Answered);
					$deleted = trim($header->Deleted);
					$draft = trim($header->Draft);
					$size = trim($header->Size);
					
					$from_name = isset($from->personal) ? utf8_encode(current(imap_mime_header_decode($from->personal))->text) : "";
					$reply_name = isset($reply_to->personal) ? utf8_encode(current(imap_mime_header_decode($reply_to->personal))->text) : "";
					$subject = isset($header->subject) ? utf8_encode(current(imap_mime_header_decode($header->subject))->text) : "";
					
					$details = array(
						"uid" => $uid,
						"from_email" => (isset($from->mailbox) && isset($from->host)) ? $from->mailbox. "@" .$from->host : "",
						"from_name" => $from_name,
						"reply_email" => (isset($reply_to->mailbox) && isset($reply_to->host)) ? $reply_to->mailbox. "@" .$reply_to->host : "",
						"reply_name" => $reply_name,
						"subject" => $subject,
						"unseen" => empty($unseen) ? false : true,
						"flagged" => empty($flagged) ? false : true,
						"answered" => empty($answered) ? false : true,
						"deleted" => empty($deleted) ? false : true,
						"draft" => empty($draft) ? false : true,
						"size" => empty($size) ? 0 : $size,
						"date" => (isset($header->date)) ? date_create($header->date) : ""
					);
					
					if($headers_only !== true) {
						$details['content'] = $this->getSingleMailContent($uid);
					}
					
					$emails[$i] = $details;
				}
				
				return $emails;
			} else {
				return $this->addLibError("Der var ingen mails på IMAP serveren.");
			}
		}
		
		/**
	 	 * Retrieve a single mails content.
		 * 
		 * @param int $uid
		 * <p>The mail unique identification number.</p>
		 * 
		 * @return string|boolean Fetched mail content as string, <b>FALSE</b> if mail content couldn't be fetched.
		 */
		public function getSingleMailContent($uid) {
			if(! $this->connection_host) {
				return $this->addLibError("Var ikke forbundet til IMAP serveren.");
			}
			
			$body = $this->getSingleMailContentPart($uid, "TEXT/HTML");
			
			if(empty($body)) {
				$body = $this->getSingleMailContentPart($uid, "TEXT/PLAIN");
			}
			
			if(! empty($body)) {
				return utf8_encode($body);
			} else {
				return $this->addLibError("Indholdet i mailen var tomt.");
			}
		}

		/**
	 	 * Retrieve a single mails content part and modify it for reading.
		 * 
		 * @param int $uid
		 * <p>The mail unique identification number.</p>
		 * 
		 * @param int $mimetype
		 * <p>The mail content mime-type.</p>
		 * 
		 * @param int $structure [optional]
		 * <p>The mail message structure.</p>
		 * 
		 * @param int $partNumber [optional]
		 * <p>The mail structure part number.</p>
		 * 
		 * @return string|boolean Fetched content as string, <b>FALSE</b> if mail content couldn't be fetched.
		 */
		private function getSingleMailContentPart($uid, $mimetype, $structure = false, $partNumber = false) {
			if(! $structure) {
				$structure = imap_fetchstructure($this->connection, $uid, FT_UID);
			}
			
			if($structure) {
				if($mimetype == $this->getSingleMailContentMimeType($structure)) {
					if(! $partNumber) {
						$partNumber = 1;
					}
					
					$text = imap_fetchbody($this->connection, $uid, $partNumber, FT_UID);

					switch($structure->encoding) {
//						case 0:
//							return imap_7bit($text);
						case 1:
							return imap_8bit($text);
						case 2:
							return imap_binary($text);
						case 3:
							return imap_base64($text);
						case 4:
							return imap_qprint($text);
						case 5:
							return ;
						default:
							return $text;
					}
				}

				if($structure->type == 1) {
					foreach($structure->parts as $index => $subStruct) {
						$prefix = "";

						if($partNumber) {
							$prefix = $partNumber. ".";
						}
						
						$data = $this->getSingleMailContentPart($uid, $mimetype, $subStruct, $prefix. ($index + 1));

						if($data) {
							return $data;
						}
					}
				}
			}

			return $this->addLibError("Kunne ikke hente indholds-part.");
		}

		/**
	 	 * Determine a single mails content mime-type from mail structure.
		 * 
		 * @param StdClass $structure
		 * <p>The mail structure from <b>imap_fetchstructure()</b>.</p>
		 * 
		 * @return string The mail content mime-type.
		 */
		private function getSingleMailContentMimeType($structure) {
			$primaryMimetype = array("TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER");

			if($structure->subtype) {
				return $primaryMimetype[(int)$structure->type]. "/" .$structure->subtype;
			}
			
			return "TEXT/PLAIN";
		}
		
		/**
	 	 * Checks for attachment on an email.
		 * 
		 * @param int $uid
		 * <p>The mail unique identification number.</p>
		 * 
		 * @param boolean $return_part [optional]
		 * <p>If <i>$return_part</i> is set to <b>TRUE</b>, it will return the part of the mail structure that contains the attachment.</p>
		 * 
		 * @return bool|array <b>TRUE</b> if the mail has an attachment, <b>FALSE</b> if it doesn't.
		 * <p>Array of attachment parts if <i>$return_part</i> is set to <b>TRUE</b>.</p>
		 * 
		 * <p>Attachment return array example:</p>
		 * <p>
		 * <table>
		 * <tr valign="top"><td><b>KEY</b></td><td><b>VALUE</b></td></tr>
		 * <tr valign="top"><td>part_num</td><td>1</td></tr>
		 * <tr valign="top"><td>file_name</td><td>file.txt</td></tr>
		 * <tr valign="top"><td>file_type</td><td>TEXT</td></tr>
		 * <tr valign="top"><td>file_size</td><td>128</td></tr>
		 * <tr valign="top"><td>encoding</td><td>3</td></tr>
		 * <tr valign="top"><td>params</td><td>array()</td></tr>
		 * </table>
		 * </p>
		 */
		public function hasAttachments($uid, $return_part = false) {
			if(! $this->connection_host) {
				return $this->addLibError("Var ikke forbundet til IMAP serveren.");
			}
			
			$mailStruct = imap_fetchstructure($this->connection, $uid, FT_UID);
			$collected_parts = array();
			
			if(isset($mailStruct->parts)) {
				foreach($mailStruct->parts as $part_num => $parts) {
					if(isset($parts->disposition) && strtolower($parts->disposition) == "attachment") {
						$collected_parts[$part_num] = $parts;
					} else {
						continue;
					}
				}
				
				if(empty($collected_parts)) {
					return $this->addLibError("Der var ingen vedhæftede filer.");
				}
			} else {
				if(! isset($mailStruct->disposition) || strtolower($mailStruct->disposition) != "attachment") {
					return $this->addLibError("Der var ingen vedhæftede filer.");
				}
				
				$collected_parts[1] = $mailStruct;
			}
			
			if($return_part !== false) {
				$parts = array();
				
				foreach($collected_parts as $part_num => $struct) {
					$parts[] = array(
						"part_num" => $part_num,
						"file_name" => $struct->dparameters[0]->value,
						"file_type" => $struct->subtype,
						"file_size" => $struct->bytes,
						"encoding" => $struct->encoding,
						"params" => $struct->dparameters,
					);
				}
				
				return $parts;
			} else {
				return true;
			}
		}
		
		/**
	 	 * Saves all email attachments on the server.
		 * 
		 * @param int $uid
		 * <p>The mail unique identification number.</p>
		 * 
		 * @param string $to_folder
		 * <p>Absolut path from the root to the folder.</p>
		 * 
		 * @param boolean $return_saved_files [optional]
		 * <p>If <i>$return_saved_files</i> is set to <b>TRUE</b>, it will return an array containing the attachments data.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the mail attachment was saved, <b>FALSE</b> if it wasn't.
		 * <p>Array of attachment data if <i>$return_saved_files</i> is set to <b>TRUE</b>.</p>
		 * 
		 * <p>Attachment return array example:</p>
		 * <p>
		 * <table>
		 * <tr valign="top"><td><b>KEY</b></td><td><b>VALUE</b></td></tr>
		 * <tr valign="top"><td>file_name</td><td>test.txt</td></tr>
		 * <tr valign="top"><td>file_type</td><td>PLAIN</td></tr>
		 * <tr valign="top"><td>file_size</td><td>128</td></tr>
		 * </table>
		 * </p>
		 */
		public function saveAttachments($uid, $to_folder, $return_saved_files = false) {
			if(! $this->connection_host) {
				return $this->addLibError("Var ikke forbundet til IMAP serveren.");
			}
			
			$to_folder = trim($to_folder, "/");
			
			if(! file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$to_folder) || ! is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$to_folder)) {
				return $this->addLibError("Mappen kunne ikke findes.");
			}
			
			$to_folder = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$to_folder;
			
			$parts = $this->hasAttachments($uid, true);
			
			if($parts !== false) {
				foreach($parts as $part) {
					$msg = imap_fetchbody($this->connection, $uid, (int)$part['part_num'] + 1, FT_UID);
					
					switch($part['encoding']) {
						case 0:
						case 1:
							$data = imap_8bit($msg);
						break;
						case 2:
							$data = imap_binary($msg);
						break;

						case 3:
						case 5:
							$data = imap_base64($msg);
						break;
						case 4:
							$data = imap_qprint($msg);
						break;
					}
					
					file_put_contents($to_folder.DIRECTORY_SEPARATOR.$part['file_name'], $data);
					
					if($return_saved_files !== false) {
						unset($part['part_num']);
						unset($part['encoding']);
						unset($part['params']);
						$saved_files[] = $part;
					}
				}
				
				if($return_saved_files !== false && isset($saved_files) && ! empty($saved_files)) {
					return $saved_files;
				} else {
					return true;
				}
			} else {
				return $this->addLibError("Kunne ikke hente indholds-part.");
			}
		}
		
		/**
	 	 * Saves a copy of the email on the server.
		 * 
		 * @param int $uid
		 * <p>The mail unique identification number.</p>
		 * 
		 * @param string $to_folder
		 * <p>Absolut path from the root to the folder.</p>
		 * 
		 * @param string $filename
		 * <p>Name of the copy file on the server.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the mail was saved, <b>FALSE</b> if it wasn't.
		 */
		public function saveMail($uid, $to_folder, $filename) {
			if(! $this->connection_host) {
				return $this->addLibError("Var ikke forbundet til IMAP serveren.");
			}
			
			$to_folder = trim($to_folder, "/");
			$filename = trim($filename);
			
			if(! file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$to_folder) || ! is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$to_folder)) {
				return $this->addLibError("Mappen kunne ikke findes.");
			}
			
			if(empty($filename)) {
				return $this->addLibError("Filen kunne ikke findes.");
			}
			
			$to_folder = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$to_folder;
			
			$headers = imap_fetchheader($this->connection, $uid, FT_UID);
			$body = imap_body($this->connection, $uid, FT_UID);
			
			$save = file_put_contents($to_folder.DIRECTORY_SEPARATOR.$filename.'.eml', $headers . "\n" . $body);
			
			if($save !== false) {
				return true;
			} else {
				return $this->addLibError("Kunne ikke gemme den vedhæftede fil.");
			}
		}
		
		/**
	 	 * Sets a flag on an email.
		 * 
		 * @param int $uid
		 * <p>The mail unique identification number.</p>
		 * 
		 * @param string $flag
		 * <p>Type of flag for the email. Types of flags are:</p>
		 * <br />
		 * <p><b>read</b></p>
		 * <p><b>flagged</b></p>
		 * <p><b>answered</b></p>
		 * <p><b>deleted</b></p>
		 * <p><b>draft</b></p>
		 * 
		 * @return boolean <b>TRUE</b> if the flag was applied to the mail, <b>FALSE</b> if it wasn't.
		 */
		public function setMailFlag($uid, $flag) {
			if(! $this->connection_host) {
				return $this->addLibError("Var ikke forbundet til IMAP serveren.");
			}
			
			$flag = strtolower(trim($flag));
			
			switch($flag) {
				case "read":
					return imap_setflag_full($this->connection, $uid, "\\Seen", ST_UID);
					break;
				case "flagged":
					return imap_setflag_full($this->connection, $uid, "\\Flagged", ST_UID);
					break;
				case "answered":
					return imap_setflag_full($this->connection, $uid, "\\Answered", ST_UID);
					break;
				case "deleted":
					return imap_setflag_full($this->connection, $uid, "\\Deleted", ST_UID);
					break;
				case "draft":
					return imap_setflag_full($this->connection, $uid, "\\Draft", ST_UID);
					break;
				default:
					return $this->addLibError("Ugyldigt mail flag.");
					break;
			}
		}
		
		/**
	 	 * Unsets a flag on an email.
		 * 
		 * @param int $uid
		 * <p>The mail unique identification number.</p>
		 * 
		 * @param string $flag
		 * <p>Type of flag for the email. Types of flags are:</p>
		 * <br />
		 * <p><b>read</b></p>
		 * <p><b>flagged</b></p>
		 * <p><b>answered</b></p>
		 * <p><b>deleted</b></p>
		 * <p><b>draft</b></p>
		 * 
		 * @return boolean <b>TRUE</b> if the flag was removed from the mail, <b>FALSE</b> if it wasn't.
		 */
		public function removeMailFlag($uid, $flag) {
			if(! $this->connection_host) {
				return $this->addLibError("Var ikke forbundet til IMAP serveren.");
			}
			
			$flag = strtolower(trim($flag));
			
			switch($flag) {
				case "read":
					return imap_clearflag_full($this->connection, $uid, "\\Seen", ST_UID);
					break;
				case "flagged":
					return imap_clearflag_full($this->connection, $uid, "\\Flagged", ST_UID);
					break;
				case "answered":
					return imap_clearflag_full($this->connection, $uid, "\\Answered", ST_UID);
					break;
				case "deleted":
					return imap_clearflag_full($this->connection, $uid, "\\Deleted", ST_UID);
					break;
				case "draft":
					return imap_clearflag_full($this->connection, $uid, "\\Draft", ST_UID);
					break;
				default:
					return $this->addLibError("Ugyldigt mail flag.");
					break;
			}
		}
		
		/**
	 	 * Moves an email to a different mail folder.
		 * 
		 * @param int $uid
		 * <p>The mail unique identification number.</p>
		 * 
		 * @param string $to_folder
		 * <p>Name of the folder which the mail is to be moved to.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the mail was moved, <b>FALSE</b> if it wasn't.
		 */
		public function moveMail($uid, $to_folder) {
			if(! $this->connection_host) {
				return $this->addLibError("Var ikke forbundet til IMAP serveren.");
			}
			
			$folders = $this->getFolderList();
			
			$host = false;
			$BIN_to_folder = bin2hex($to_folder);
			
			foreach($folders as $folder_host => $folder) {
				$BIN_folder = str_replace("00", "", bin2hex($folder));
				
				if($BIN_to_folder == $BIN_folder) {
					$host = $folder_host;
				}
			}
			
			if($host !== false) {
				$host = str_replace($this->connection_root_host, "", $host);
				
				return imap_mail_move($this->connection, $uid, $host, CP_UID);
			} else {
				return $this->addLibError("Mappen kunne ikke findes.");
			}
		}
		
		/**
	 	 * Copies an email to a different mail folder.
		 * 
		 * @param int $uid
		 * <p>The mail unique identification number.</p>
		 * 
		 * @param string $to_folder
		 * <p>Name of the folder which the mail is to be copied to.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the mail was copied, <b>FALSE</b> if it wasn't.
		 */
		public function copyMail($uid, $to_folder) {
			if(! $this->connection_host) {
				return $this->addLibError("Var ikke forbundet til IMAP serveren.");
			}
			
			$folders = $this->getFolderList();
			
			$host = false;
			$BIN_to_folder = bin2hex($to_folder);
			
			foreach($folders as $folder_host => $folder) {
				$BIN_folder = str_replace("00", "", bin2hex($folder));
				
				if($BIN_to_folder == $BIN_folder) {
					$host = $folder_host;
				}
			}
			
			if($host !== false) {
				$host = str_replace($this->connection_root_host, "", $host);
				
				return imap_mail_copy($this->connection, $uid, $host, CP_UID);
			} else {
				return $this->addLibError("Mappen kunne ikke findes.");
			}
		}
		
		/**
	 	 * Creates a mail folder.
		 * 
		 * @param string $folder_name
		 * <p>Name of the new folder.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the folder was created, <b>FALSE</b> if it wasn't.
		 */
		public function createMailFolder($folder_name) {
			if(! $this->connection_host) {
				return $this->addLibError("Var ikke forbundet til IMAP serveren.");
			}
			
			$folders = $this->getFolderList();
			
			$exists = false;
			$BIN_folder_name = bin2hex($folder_name);
			
			foreach($folders as $folder_host => $folder) {
				$BIN_folder = str_replace("00", "", bin2hex($folder));
				
				if($BIN_folder_name == $BIN_folder) {
					$exists = true;
				}
			}
			
			if($exists === false) {
				$folder_name = imap_utf7_encode($folder_name);
				$host = $this->connection_root_host.$folder_name;
				
				if(! imap_createmailbox($this->connection, $host)) {
					return $this->addLibError("Kunne ikke oprette mappen.");
				} else {
					$this->connection = imap_open($this->connection_host, $this->connection_username, $this->connection_password);

					if($this->connection === false) {
						return $this->addLibError("Mappen kunne ikke findes.");
					}
					
					return true;
				}
			} else {
				return $this->addLibError("Mappen kunne ikke findes.");
			}
		}
		
		/**
	 	 * Renames a mail folder.
		 * 
		 * @param string $old_folder_name
		 * <p>Old folder name.</p>
		 * 
		 * @param string $new_folder_name
		 * <p>New folder name.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the folder was renamed, <b>FALSE</b> if it wasn't.
		 */
		public function renameMailFolder($old_folder_name, $new_folder_name) {
			if(! $this->connection_host) {
				return $this->addLibError("Var ikke forbundet til IMAP serveren.");
			}
			
			$folders = $this->getFolderList();
			
			$exists = false;
			$BIN_old_folder_name = bin2hex($old_folder_name);
			
			foreach($folders as $folder_host => $folder) {
				$BIN_folder = str_replace("00", "", bin2hex($folder));
				
				if($BIN_old_folder_name == $BIN_folder) {
					$exists = true;
				}
			}
			
			if($exists === true) {
				$old_host = $this->connection_root_host.imap_utf7_encode($old_folder_name);
				$new_host = $this->connection_root_host.imap_utf7_encode($new_folder_name);
				
				if(! imap_renamemailbox($this->connection, $old_host, $new_host)) {
					return $this->addLibError("Kunne ikke omdøbe mappen.");
				} else {
					$this->connection = imap_open($this->connection_host, $this->connection_username, $this->connection_password);

					if($this->connection === false) {
						return $this->addLibError("Mappen kunne ikke findes.");
					}
					
					return true;
				}
			} else {
				return $this->addLibError("Mappen kunne ikke findes.");
			}
		}
		
		/**
	 	 * Deletes a mail folder.
		 * 
		 * @param string $folder_name
		 * <p>Name of the folder to delete.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the folder was deleted, <b>FALSE</b> if it wasn't.
		 */
		public function deleteMailFolder($folder_name) {
			if(! $this->connection_host) {
				return $this->addLibError("Var ikke forbundet til IMAP serveren.");
			}
			
			$folders = $this->getFolderList();
			
			$exists = false;
			$BIN_folder_name = bin2hex($folder_name);
			
			foreach($folders as $folder_host => $folder) {
				$BIN_folder = str_replace("00", "", bin2hex($folder));
				
				if($BIN_folder_name == $BIN_folder) {
					$exists = true;
				}
			}
			
			if($exists === true) {
				$folder_name = imap_utf7_encode($folder_name);
				$host = $this->connection_root_host.$folder_name;
				
				if(! imap_deletemailbox($this->connection, $host)) {
					return $this->addLibError("Kunne ikke slette mappen.");
				} else {
					$this->connection = imap_open($this->connection_host, $this->connection_username, $this->connection_password);

					if($this->connection === false) {
						return $this->addLibError("Mappen kunne ikke findes.");
					}
					
					return true;
				}
			} else {
				return $this->addLibError("Mappen kunne ikke findes.");
			}
		}
		
		/**
	 	 * Gets the IMAP error messages.
		 * 
		 * @return string|boolean The error messages if any exists, <b>FALSE</b> if none exists.
		 */
		public function getImapServerError() {
			$errors = imap_errors();

			if(empty($errors)) {
				$errors = imap_last_error();
				
				if(empty($errors)) {
					return false;
				}
			}
			
			$final = "";
			
			foreach($errors as $error) {
				$final .= $error;
				
				if($error !== end($errors)) {
					$final .= "<br />";
				}
			}
			
			return $final;
		}
		
		function __destruct() {
			if(gettype($this->connection) == "ressource") {
				$this->imap_host = null;
				$this->imap_port = null;
				$this->imap_username = null;
				$this->imap_password = null;
				$this->connection = null;

				imap_errors();
				imap_close($this->connection, CL_EXPUNGE);
			}
		}
	}

?>