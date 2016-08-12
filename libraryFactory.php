<?php

	class libraryFactory {
		
		private $cookie_lib;
		private $file_system_lib;
		private $ftp_lib;
		private $http_lib;
		private $IMAP_lib;
		private $mailer_lib;
		private $message_lib;
		private $session_lib;
		private $uploader_lib;
		private $validator_lib;
		private $calendar_lib;
		private $cURL_lib;
		private $image_lib;
		
		function __construct() {}
		private function __clone() {}
		
		/**
		 * Fetch the cookie library.
		 * 
		 * @return Lib_cookie Cookie library instance.
		 */
		public function getCookieLib() {
			if(! $this->cookie_lib) {
				$this->cookie_lib = new Lib_cookie();
			}
			
			return $this->cookie_lib;
		}
		
		/**
		 * Fetch the filesystem library.
		 * 
		 * @return Lib_file_system File system library instance.
		 */
		public function getFileSystemLib() {
			if(! $this->file_system_lib) {
				$this->file_system_lib = new Lib_file_system();
			}
			
			return $this->file_system_lib;
		}
		
		/**
		 * Fetch the FTP library.
		 * 
		 * @return Lib_ftp FTP library instance.
		 */
		public function getFtpLib() {
			if(! $this->ftp_lib) {
				$this->ftp_lib = new Lib_ftp();
			}
			
			return $this->ftp_lib;
		}
		
		/**
		 * Fetch the HTTP library.
		 * 
		 * @return Lib_http HTTP library instance.
		 */
		public function getHttpLib() {
			if(! $this->http_lib) {
				$this->http_lib = new Lib_http();
			}
			
			return $this->http_lib;
		}
		
		/**
		 * Fetch the IMAP library.
		 * 
		 * @return Lib_IMAP IMAP library instance.
		 */
		public function getIMAPLib() {
			if(! $this->IMAP_lib) {
				$this->IMAP_lib = new Lib_IMAP();
			}
			
			return $this->IMAP_lib;
		}
		
		/**
		 * Fetch the mailer library.
		 * 
		 * @return Lib_mailer Mailer library instance.
		 */
		public function getMailerLib() {
			if(! $this->mailer_lib) {
				$this->mailer_lib = new Lib_mailer();
			}
			
			return $this->mailer_lib;
		}
		
		/**
		 * Fetch the message library.
		 * 
		 * @return Lib_message Message library instance.
		 */
		public function getMessageLib() {
			if(! $this->message_lib) {
				$this->message_lib = new Lib_message();
			}
			
			return $this->message_lib;
		}
		
		/**
		 * Fetch the session library.
		 * 
		 * @return Lib_session Session library instance.
		 */
		public function getSessionLib() {
			if(! $this->session_lib) {
				$this->session_lib = new Lib_session();
			}
			
			return $this->session_lib;
		}
		
		/**
		 * Fetch the uploader library.
		 * 
		 * @return Lib_uploader Upload handler library instance.
		 */
		public function getUploaderLib() {
			if(! $this->uploader_lib) {
				$this->uploader_lib = new Lib_uploader();
			}
			
			return $this->uploader_lib;
		}
		
		/**
		 * Fetch the validator library.
		 * 
		 * @return Lib_validator Validator library instance.
		 */
		public function getValidatorLib() {
			if(! $this->validator_lib) {
				$this->validator_lib = new Lib_validator();
			}
			
			return $this->validator_lib;
		}
		
		/**
		 * Fetch the calendar library.
		 * 
		 * @return Lib_calendar Calendar library instance.
		 */
		public function getCalendarLib() {
			if(! $this->calendar_lib) {
				$this->calendar_lib = new Lib_calendar();
			}
			
			return $this->calendar_lib;
		}
		
		/**
		 * Fetch the cURL library.
		 * 
		 * @return Lib_curl cURL library instance.
		 */
		public function getCURLLib() {
			if(! $this->cURL_lib) {
				$this->cURL_lib = new Lib_cURL();
			}
			
			return $this->cURL_lib;
		}
		
		/**
		 * Fetch the image library.
		 * 
		 * @return Lib_image Image library instance.
		 */
		public function getImageLib() {
			if(! $this->image_lib) {
				$this->image_lib = new Lib_image();
			}
			
			return $this->image_lib;
		}
	}

?>