<?php

	class Lib_message extends Lib_base {
	
		private $db;
		
		private function __clone() {}
		
		function __construct() {
			if(! $this->db)
				$this->db = $this->getDbConnection();
		}
		
		/**
		 * Adds an info message.
		 * 
		 * @param string $info [optional]
		 * <p>The info message to display.</p>
		 * 
		 * @param string $log_message [optional]
		 * <p>A message to be logged in the database but not shown.</p>
		 * 
		 * @param string $redirect [optional]
		 * <p>Redirect URL.</p>
		 */
		public function addInfo($info = null, $log_message = null, $redirect = null) {
			if($info) {
				$_SESSION['msgInfo'][] = $info;
			}
			
			$info = $info ? $info : "";
			$log_message = $log_message ? $log_message : "";
			
			$this->saveLogMessage($info, $log_message, ClientLogModel::MESSAGE_TYPE_INFO);
			
			if($redirect) {
				header('Location: ' .$redirect);
				die();
			}
		}
		
		/**
		 * Adds an error message.
		 * 
		 * @param string $info [optional]
		 * <p>The error message to display.</p>
		 * 
		 * @param string $log_message [optional]
		 * <p>A message to be logged in the database but not shown.</p>
		 * 
		 * @param string $redirect [optional]
		 * <p>Redirect URL.</p>
		 */
		public function addError($error = null, $log_message = null, $redirect = null) {
			if($error) {
				$_SESSION['msgError'][] = $error;
			}
			
			$error = $error ? $error : "";
			$log_message = $log_message ? $log_message : "";
			
			$this->saveLogMessage($error, $log_message, ClientLogModel::MESSAGE_TYPE_ERROR);
			
			if($redirect) {
				header('Location: ' .$redirect);
				die();
			}
		}
		
		/**
		 * Adds an success message.
		 * 
		 * @param string $info [optional]
		 * <p>The success message to display.</p>
		 * 
		 * @param string $log_message [optional]
		 * <p>A message to be logged in the database but not shown.</p>
		 * 
		 * @param string $redirect [optional]
		 * <p>Redirect URL.</p>
		 */
		public function addSuccess($success = null, $log_message = null, $redirect = null) {
			if($success) {
				$_SESSION['msgSuccess'][] = $success;
			}
			
			$success = $success ? $success : "";
			$log_message = $log_message ? $log_message : "";
			
			$this->saveLogMessage($success, $log_message, ClientLogModel::MESSAGE_TYPE_SUCCESS);
			
			if($redirect) {
				header('Location: ' .$redirect);
				die();
			}
		}
		
		/**
		 * Saves the client log message to the database.
		 * 
		 * @param string $message
		 * <p>Message displayed to the client.</p>
		 * 
		 * @param string $log_message
		 * <p>Message to save to the database.</p>
		 * 
		 * @param int $message_type
		 * <p>The message type.</p>
		 */
		private function saveLogMessage($message, $log_message, $message_type) {
			$client_log = new ClientLogModel();
			
			if(strpos($client_log->getCalledFrom(), "favicon.ico") === false && strpos($client_log->getCalledFrom(), "apple-touch-icon") === false) {
				$client_log->setMessageType($message_type);
				$client_log->setClientMessage($message);
				$client_log->setLogMessage(! empty($log_message) ? $log_message : "");
				$client_log->setUserId($this->getUserId() !== false ? $this->getUserId() : null);
				
				try {
					$getData = $this->db->prepare("INSERT INTO client_log VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
					$getData->bindParam(1, $client_log->getId());
					$getData->bindParam(2, $client_log->getMessageType());
					$getData->bindParam(3, $client_log->getClientMessage());
					$getData->bindParam(4, $client_log->getLogMessage());
					$getData->bindParam(5, $client_log->getCalledFrom());
					$getData->bindParam(6, $client_log->getClientIp());
					$getData->bindParam(7, $client_log->getUserId());
					$getData->bindParam(8, $client_log->getAddDatetime()->format('Y-m-d H:i:s'));
					$result = $getData->execute();
				} catch(PDOException $e) {
					die(var_dump($e->getMessage()));
				}
			}
		}
		
		/**
		 * Gets all messages formatted as HTML.
		 * 
		 * @return string|boolean Messages formatted.
		 */
		public function getMessagesFormatted() {
			$formatted = "";
			
			if(! empty($_SESSION['msgInfo'])) {
				foreach($_SESSION['msgInfo'] as $key => $text) {
					$formatted .= '<div id="message_info"><span>' .$text. '</span>';
					
					if($text !== end($_SESSION['msgInfo'])) {
						$formatted .= "<br />";
					}
					
					$formatted .= "</div>";
				}
				
				unset($_SESSION['msgInfo']);
			}
			
			if(! empty($_SESSION['msgError'])) {
				foreach($_SESSION['msgError'] as $key => $text) {
					$formatted .= '<div id="message_error"><span>' .$text. '</span>';
					
					if($text !== end($_SESSION['msgError'])) {
						$formatted .= "<br />";
					}
					
					$formatted .= "</div>";
				}
				
				unset($_SESSION['msgError']);
			}
			
			if(! empty($_SESSION['msgSuccess'])) {
				foreach($_SESSION['msgSuccess'] as $key => $text) {
					$formatted .= '<div id="message_success"><span>' .$text. '</span>';
					
					if($text !== end($_SESSION['msgSuccess'])) {
						$formatted .= "<br />";
					}
					
					$formatted .= "</div>";
				}
				
				unset($_SESSION['msgSuccess']);
			}
			
			return $formatted;
		}
		
		/**
		 * Gets the number of info messages.
		 * 
		 * @return int The number of info messages.
		 */
		public function numInfos() {
			if(isset($_SESSION['msgInfo'])) {
				return count($_SESSION['msgInfo']);
			} else {
				return 0;
			}
		}
		
		/**
		 * Gets the number of error messages.
		 * 
		 * @return int The number of error messages.
		 */
		public function numErrors() {
			if(isset($_SESSION['msgError'])) {
				return count($_SESSION['msgError']);
			} else {
				return 0;
			}
		}
		
		/**
		 * Gets the number of success messages.
		 * 
		 * @return int The number of success messages.
		 */
		public function numSuccess() {
			if(isset($_SESSION['msgSuccess'])) {
				return count($_SESSION['msgSuccess']);
			} else {
				return 0;
			}
		}
		
		/**
		 * Gets the number of messages.
		 * 
		 * @return int The number of messages.
		 */
		public function numMessages() {
			$count = 0;
			
			if(isset($_SESSION['msgError'])) {
				$count = count($_SESSION['msgError']);
			}
			
			if(isset($_SESSION['msgInfo'])) {
				$count += count($_SESSION['msgInfo']);
			}
			
			if(isset($_SESSION['msgSuccess'])) {
				$count += count($_SESSION['msgSuccess']);
			}
			
			return $count;
		}
	}

?>