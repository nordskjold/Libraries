<?php

	class Lib_session extends Lib_base {
		
		private $_session, $db;
		public $maxTime;
		
		function __clone() {}
		
		function __construct() {
			$this->db = $this->getDbConnection();
			$this->maxTime['expire'] = new DateTime();
			$this->maxTime['gc'] = clone $this->maxTime['expire'];
			$this->maxTime['gc']->modify('-' .get_cfg_var('session.gc_maxlifetime'). ' seconds');
			
			session_name(configuration::getSessionName());
			
			session_set_save_handler(
					array($this, '_open'),
					array($this, '_close'),
					array($this, '_read'),
					array($this, '_write'),
					array($this, '_destroy'),
					array($this, '_gc')
			);
			
			register_shutdown_function('session_write_close');
			
			@session_start();
			
			setcookie(session_name(), session_id(), time() + get_cfg_var('session.gc_maxlifetime'), "/", configuration::getSystemHostnameRaw());
		}
		
		public function _open() {
			return true;
		}

		public function _close() {
			$this->_gc($this->maxTime['gc']);
			return true;
		}

		public function _read($id) {
			$getData = $this->db->prepare("SELECT data FROM session WHERE session.session_id = ?");
			$getData->bindParam(1, $id);
			$getData->execute();

			$allData = $getData->fetch(PDO::FETCH_ASSOC);
			$totalData = count($allData);
			$hasData = (bool)$totalData >= 1;

			return $hasData ? $allData['data'] : '';
		}

		public function _write($id, $data) {
			$user_id = null;
			
			$split = explode(";", $data);

			foreach($split as $session_entry) {
				$sec_split = explode("|", $session_entry);

				if($sec_split[0] == "logged") {
					$user_id = (int)substr($sec_split[1], 2);
					$this->checkPreviousSessionAndUpdateId($user_id);
				}
			}
			
			try {
				$getData = $this->db->prepare("REPLACE INTO session VALUES (?, ?, ?, ?, ?)");
				$getData->bindParam(1, $id);
				$getData->bindParam(2, $this->maxTime['expire']->format('Y-m-d H:i:s'));
				$getData->bindParam(3, $data);
				$getData->bindParam(4, $user_id);
				$getData->bindParam(5, $_SERVER['REMOTE_ADDR']);
				$result = $getData->execute();
			} catch(PDOException $e) {
				die($e->getMessage());
			}
			
			return $result;
		}

		public function _destroy($id) {
			$getData = $this->db->prepare("DELETE FROM session WHERE session_id = ?");
			$getData->bindParam(1, $id);
			
			return $getData->execute();
		}

		public function _gc() {
			$this->flushDuplicants();
			
			$old = $this->maxTime['gc']->format('Y-m-d H:i:s');
			
			$getData = $this->db->prepare("DELETE FROM session WHERE expire < ? AND user_id IS NULL");
			$getData->bindParam(1, $old);
			
			return $getData->execute();
		}
		
		public function getUserIdFromSessionId($id) {
			$getData = $this->db->prepare("SELECT user_id FROM session WHERE session.session_id = ?");
			$getData->bindParam(1, $id);
			$getData->execute();

			$allData = $getData->fetch(PDO::FETCH_ASSOC);
			$totalData = count($allData);
			$hasData = (bool)$totalData >= 1;

			return $hasData ? $allData['user_id'] : false;
		}
		
		public function checkPreviousSessionAndUpdateId($user_id) {
			$getData = $this->db->prepare("DELETE FROM session WHERE user_id = ?");
			$getData->bindParam(1, $user_id);
			$getData->execute();
			
			$getData = $this->db->prepare("UPDATE session SET session_id = ? WHERE user_id = ?");
			$getData->bindParam(1, session_id());
			$getData->bindParam(2, $user_id);
			$getData->execute();
		}
		
		public function flushDuplicants() {
			$getData = $this->db->prepare("SELECT session_id, expire, user_id FROM session");
			$getData->execute();
			
			$final = array();
			$deletes = array();
			
			foreach($getData->fetchAll(PDO::FETCH_ASSOC) as $entry) {
				if(! array_key_exists($entry['user_id'], $final)) {
					$final[$entry['user_id']] = array('session_id' => $entry['session_id'], 'expire' => $entry['expire']);
				} else {
					$entry_expire = new DateTime($entry['expire']);
					$final_expire = new DateTime($final[$entry['user_id']]['expire']);
					
					if($final_expire->format('Ymdhis') > $entry_expire->format('Ymdhis')) {
						$deletes[] = $entry['session_id'];
					} else {
						$deletes[] = $final[$entry['user_id']]['session_id'];
						$final[$entry['user_id']] = array('id' => $entry['session_id'], 'expire' => $entry['expire']);
					}
				}
			}
			
			if(count($deletes) != 0) {
				foreach($deletes as $id) {
					$getData = $this->db->prepare("DELETE FROM session WHERE session_id = ?");
					$getData->bindParam(1, $id);
					$getData->execute();
				}
			}
		}
		
		/**
		 * Sets a session.
		 * 
		 * @param string $key
		 * <p>Name of the session.</p>
		 * 
		 * @param string $value
		 * <p>Value of the session.</p>
		 * 
		 * @return boolean <b>TRUE</b> on succes, <b>FALSE</b> when trying to set login or message session.
		 */
		public function setSession($key, $value) {
			if($key == 'logged' || $key == 'msgInfo' || $key == 'msgError' || $key == 'msgSuccess') {
				return $this->addLibError("Kan ikke overskrive login eller meddelses sessionen.");
			}
			
			$_SESSION[$key] = $value;
			
			return true;
		}
		
		/**
		 * Gets a session from key.
		 * 
		 * @param string $key
		 * <p>Session key to search for.</p>
		 * 
		 * @return string|boolean Session value on success, <b>FALSE</b> if it doesn't exist or trying to get login and message session.
		 */
		public function getSession($key) {
			if($key == 'logged' || $key == 'msgInfo' || $key == 'msgError' || $key == 'msgSuccess') {
				return $this->addLibError("Kan ikke hente login eller meddelser via session.");
			}
			
			if(! array_key_exists($key, $_SESSION)) {
				return $this->addLibError("Sessionen kunne ikke findes.");
			}
			
			return $_SESSION[$key];
		}
		
		/**
		 * Checks whether a sessions exists.
		 * 
		 * @param string $key
		 * <p>Session key to search for.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the session exists, <b>FALSE</b> if it doesn't.
		 */
		public function hasSession($key) {
			if(! array_key_exists($key, $_SESSION)) {
				return false;
			}
			
			return true;
		}
		
		/**
		 * Sets a login session.
		 * 
		 * @param int $user_id
		 * <p>The user id which the session belongs to.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the session is set.
		 */
		public function setLoginSession($user_id) {
			$_SESSION['logged'] = $user_id;
			
			return true;
		}
		
		/**
		 * Gets the login session.
		 * 
		 * @return boolean <b>TRUE</b> if the login session is set, <b>FALSE</b> if it isn't.
		 */
		public function getLoginSession() {
			if($this->hasSession('logged')) {
				return $_SESSION['logged'];
			}
			
			return $this->addLibError("Login session kunne ikke findes.");
		}
		
		/**
		 * Terminates the login session.
		 * 
		 * @return boolean <b>TRUE</b> when the session is terminated.
		 */
		public function terminateLoginSession() {
			unset($_SESSION['logged']);
			
			return true;
		}
		
		/**
		 * Terminates one session.
		 * 
		 * @param string $key
		 * <p>Session key to search for.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the session is terminated, <b>FALSE</b> if the key doesn't exist.
		 */
		public function terminateOneSession($key) {
			if(! array_key_exists($key, $_SESSION) || $key == 'logged' || $key == 'msgInfo' || $key == 'msgError' || $key == 'msgSuccess') {
				return $this->addLibError("Sessionen kunne ikke findes.");
			}
			
			unset($_SESSION[$key]);
			
			return true;
		}
		
		/**
		 * Terminates all sessions.
		 */
		public function terminateAllSession() {
			@session_destroy();
		}
	}

?>