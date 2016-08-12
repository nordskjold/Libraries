<?php

	class Lib_mailer extends Lib_Base {
		
		private $phpmailer;
		
		private $has_smtp = false, $smtp_host, $smtp_port, $smtp_auth, $smtp_username, $smtp_password;
		
		protected $default_sender = array();
		protected $sender = array();
		
		protected $default_recipients = array();
		protected $recipients = array();
		
		protected $cc = array();
		protected $bcc = array();
		protected $reply_to = array();

		protected $subject, $message, $alt_message;
		
		function __construct() {
			require_once $this->root. 'library/PHPMailer/class.phpmailer.php';
			
			if(! $this->phpmailer) {
				$this->phpmailer = new PHPMailer();
				$this->phpmailer->IsMail();

				$this->phpmailer->CharSet = configuration::getMailCharset();

				$this->default_sender[] = array('mail' => configuration::getDefaultMailSenderAddress(), 'name' => configuration::getDefaultMailSenderName());
				$this->default_recipients[] = array('mail' => configuration::getDefaultSystemMailRecieverMail(), 'name' => configuration::getDefaultSystemMailRecieverName());
			}
		}
		
		private function __clone() {}
		
		/**
	 	 * Adds a sender address.
		 * 
		 * @param string $mail
		 * <p>The senders email address.</p>
		 * <p>( E.g. <b>'test@gmail.com'</b> )</p>
		 * 
		 * @param string $name [optional]
		 * <p>The senders name, default from 'null'.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the sender is added, <b>FALSE</b> if not.
		 */
		public function addSender($mail, $name = null) {
			if(filter_var($mail, FILTER_VALIDATE_EMAIL)) {
				$this->sender[] = array('mail' => $mail, 'name' => ($name ? mb_convert_encoding($name, configuration::getMailCharset(), "auto") : ""));
				return true;
			} else {
				return false;
			}
		}
		
		/**
	 	 * Adds a recipient address.
		 * 
		 * @param string $mail
		 * <p>The recipient email address.</p>
		 * <p>( E.g. <b>'imap.gmail.com'</b> )</p>
		 * 
		 * @param string $name [optional]
		 * <p>The recipient name, default from 'null'.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the recipient is added, <b>FALSE</b> if not.
		 */
		public function addRecipients($mail, $name = null) {
			if(filter_var($mail, FILTER_VALIDATE_EMAIL)) {
				$this->recipients[] = array('mail' => $mail, 'name' => ($name ? mb_convert_encoding($name, configuration::getMailCharset(), "auto") : ""));
				return true;
			} else {
				return false;
			}
		}
		
		/**
	 	 * Adds a Carbon Copy (CC) address.
		 * 
		 * @param string $mail
		 * <p>The recipient email address.</p>
		 * <p>( E.g. <b>'imap.gmail.com'</b> )</p>
		 * 
		 * @param string $name [optional]
		 * <p>The recipient name, default from 'null'.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the recipient is added, <b>FALSE</b> if not.
		 */
		public function addCc($mail, $name = null) {
			if(filter_var($mail, FILTER_VALIDATE_EMAIL)) {
				$this->cc[] = array('mail' => $mail, 'name' => ($name ? mb_convert_encoding($name, configuration::getMailCharset(), "auto") : ""));
				return true;
			} else {
				return false;
			}
		}
		
		/**
	 	 * Adds a Blind Carbon Copy (BCC) address.
		 * 
		 * @param string $mail
		 * <p>The recipient email address.</p>
		 * <p>( E.g. <b>'imap.gmail.com'</b> )</p>
		 * 
		 * @param string $name [optional]
		 * <p>The recipient name, default from 'null'.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the recipient is added, <b>FALSE</b> if not.
		 */
		public function addBcc($mail, $name = null) {
			if(filter_var($mail, FILTER_VALIDATE_EMAIL)) {
				$this->bcc[] = array('mail' => $mail, 'name' => ($name ? mb_convert_encoding($name, configuration::getMailCharset(), "auto") : ""));
				return true;
			} else {
				return false;
			}
		}
		
		/**
	 	 * Adds a Reply-To address.
		 * 
		 * @param string $mail
		 * <p>The recipient email address.</p>
		 * <p>( E.g. <b>'imap.gmail.com'</b> )</p>
		 * 
		 * @param string $name [optional]
		 * <p>The recipient name, default from 'null'.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the reply address is added, <b>FALSE</b> if not.
		 */
		public function addReplyTo($mail, $name = null) {
			if(filter_var($mail, FILTER_VALIDATE_EMAIL)) {
				$this->reply_to[] = array('mail' => $mail, 'name' => ($name ? mb_convert_encoding($name, configuration::getMailCharset(), "auto") : ""));
				return true;
			} else {
				return false;
			}
		}
		
		/**
	 	 * Adds a mail subject.
		 * 
		 * @param string $subject
		 * <p>The email subject.</p>
		 */
		public function addSubject($subject) {
			if($subject) {
				$this->subject = mb_convert_encoding($subject, configuration::getMailCharset(), "auto");
			}
		}
		
		/**
	 	 * Adds body content to the email.
		 * 
		 * @param string $message
		 * <p>The email body content.</p>
		 * 
		 * @param boolean $is_html
		 * <p><b>TRUE</b> as default, <b>FALSE</b> for clear-text body content.</p>
		 */
		public function addMessage($message, $is_html = true) {
			if($is_html == true) {
				$this->phpmailer->IsHTML(true);
			} else {
				$this->phpmailer->IsHTML(false);
			}
			
			if($message) {
				$this->message = mb_convert_encoding($message, configuration::getMailCharset(), "auto");
			}
		}
		
		/**
	 	 * Adds body content to the email from an HTML file.
		 * 
		 * @param string $path
		 * <p>Absolut path from the root to the file.</p>
		 * 
		 * @param array $placeholders [optional]
		 * <p>Placeholder replace array.</p>
		 * 
		 * @return boolean <b>TRUE</b> on success, <b>FALSE</b> if the file could not be retrieved.
		 */
		public function addMessageFromHtmlFile($path, $placeholders = null) {
			$path = trim($path, "/");
			
			if(! file_exists($this->root.$path) || is_dir($this->root.$path)) {
				return false;
			}
			
			$path = $this->root.$path;
			
			$html = file_get_contents($path);
			
			if($placeholders) {
				$html = strtr($html, $placeholders);
			}
			
			$this->phpmailer->IsHTML(true);
			
			if($html) {
				$this->message = mb_convert_encoding($html, configuration::getMailCharset(), "auto");
				return true;
			}
		}
		
		/**
	 	 * Adds alternate body content to the email, if HTML email cannot be viewed.
		 * 
		 * @param string $alt_message
		 * <p>The alternate mail body content.</p>
		 */
		public function addAltMessage($alt_message) {
			$this->phpmailer->IsHTML(false);
			
			if($alt_message) {
				$this->alt_message = mb_convert_encoding($alt_message, configuration::getMailCharset(), "auto");
			}
		}
		
		/**
	 	 * Adds an attachment to the email.
		 * 
		 * @param string $path
		 * <p>Absolut path from the root to the file.</p>
		 * 
		 * @param Lib_file_system $file_system
		 * <p>Instance of file system library object.</p>
		 * 
		 * @param string $new_name
		 * <p>New filename of the attachment.</p>
		 * 
		 * @return boolean <b>TRUE</b> on success, <b>FALSE</b> if the file could not be retrieved.
		 */
		public function addAttachment($path, Lib_file_system $file_system, $new_name = null) {
			$path = trim($path, '/');
			
			if(! file_exists($this->root.$path) || is_dir($this->root.$path)) {
				return false;
			}
			
			if($new_name) {
				$new_name .= '.' .$file_system->getFileExtension($path);
			} else {
				$attachment_name = $file_system->getFileNameFromFilePath($path, true);
			}
			
			$file_mime_type = $file_system->getFileMimeContentTypeFromFilePath($path);

			$this->phpmailer->AddAttachment($path, $attachment_name, "base64", $file_mime_type);
			
			return true;
		}

		/**
	 	 * Sends the email.
		 * 
		 * @return boolean <b>TRUE</b> on success, <b>FALSE</b> if the email could not be sent.
		 */
		public function sendMail() {
			if(! $this->isLibErrorsEmpty()) {
				return false;
			}
			
			if($this->has_smtp == true) {
				if(empty($this->sender) && empty($this->default_sender)) {
					return $this->addLibError("Der var ingen mail afsender.");
				}
				
				$this->phpmailer->IsSMTP();
				/**
				 * For SMTP Debug Messages
				 * [1 = messages only, 2 = errors and messages]
				 * 
				 * $this->phpmailer->SMTPDebug = 2;
				 */
				
				if($this->smtp_auth == true) {
					$this->phpmailer->SMTPAuth = true;
					$this->phpmailer->SMTPSecure = $this->smtp_auth;
				}

				$this->phpmailer->Host = $this->smtp_host;
				$this->phpmailer->Port = $this->smtp_port;
				$this->phpmailer->Username = $this->smtp_username;
				$this->phpmailer->Password = $this->smtp_password;
			}
			
			$addresses = empty($this->sender) ? $this->default_sender : $this->sender;
			
			if(empty($addresses)) {
				return $this->addLibError("Der var ingen mail afsender.");
			}
			
			foreach($addresses as $sender) {
				$this->phpmailer->SetFrom($sender['mail'], $sender['name']);
			}
			
			$addresses = empty($this->recipients) ? $this->default_recipients : $this->recipients;
			
			if(empty($addresses)) {
				return $this->addLibError("Der var ingen mail modtager.");
			}
			
			foreach($addresses as $recipients) {
				$this->phpmailer->AddAddress($recipients['mail'], $recipients['name']);
			}
			
			if(is_array($this->cc) && ! empty($this->cc)) {
				foreach($this->cc as $cc) {
					$this->phpmailer->AddCC($cc['mail'], $cc['name']);
				}
			}
			
			if(is_array($this->bcc) && ! empty($this->bcc)) {
				foreach($this->bcc as $bcc) {
					$this->phpmailer->AddBCC($bcc['mail'], $bcc['name']);
				}
			}
			
			if(is_array($this->reply_to) && ! empty($this->reply_to)) {
				foreach($this->reply_to as $reply_to) {
					$this->phpmailer->AddReplyTo($reply_to['mail'], $reply_to['name']);
				}
			}
			
			$this->phpmailer->Subject = empty($this->subject) ? configuration::getDefaultMailSubject() : $this->subject;
			
			$this->phpmailer->Body = empty($this->message) ? "" : $this->message;
			$this->phpmailer->AltBody = empty($this->alt_message) ? "" : $this->alt_message;
			
			if(! $this->phpmailer->Send()) {
				return $this->addLibError($this->phpmailer->ErrorInfo);
			} else {
				$this->clearMailer();
				return true;
			}
		}
		
		/**
	 	 * Adds a connection to an SMTP server.
		 * NOTE: When using SMTP a sender address is required.
		 * 
		 * @param string $smtp_host
		 * <p>The SMTP server hostname.</p>
		 * <p>( E.g. <b>'smtp.gmail.com'</b> )</p>
		 * 
		 * @param int $smtp_port
		 * <p>The SMTP server port number (465, 587).</p>
		 * 
		 * @param string $smtp_username
		 * <p>The SMTP server authentication username.</p>
		 * <p>Commongly an email addresse.</p>
		 * 
		 * @param string $smtp_password
		 * <p>The SMTP server authentication password.</p>
		 * 
		 * @param string $smtp_auth [optional]
		 * <p>The <i>$smtp_auth</i> changes the connection authentication (ssl, starttls).</p>
		 * 
		 * @return boolean <b>TRUE</b> if the connection is established, <b>FALSE</b> if not.
		 */
		public function addSMTP($smtp_host, $smtp_port = 25, $smtp_username, $smtp_password, $smtp_auth = "") {
			return $this->smtpConnect($smtp_host, $smtp_port, $smtp_username, $smtp_password, $smtp_auth);
		}
		
		/**
	 	 * Adds a connection to an SMTP server from a list of pre-defined servers.
		 * NOTE: When using SMTP a sender address is required.
		 * 
		 * @param string $imap_server
		 * <p>The SMTP server host prefix to search for.</p>
		 * <p>( E.g. <b>'gmail ssl'</b> )</p>
		 * 
		 * @param string $smtp_username
		 * <p>The SMTP server authentication username.</p>
		 * <p>Commongly an email addresse.</p>
		 * 
		 * @param string $smtp_password
		 * <p>The SMTP server authentication password.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the connection is established, <b>FALSE</b> if not.
		 */
		public function addSMTPFromSupportedServers($smtp_server, $smtp_username, $smtp_password) {
			$servers = parse_ini_file($this->root. 'library/flat_files/SMTP_servers.ini', true);
			
			if(array_key_exists($smtp_server, $servers)) {
				return $this->smtpConnect($servers[$smtp_server]['host'], $servers[$smtp_server]['port'], $servers[$smtp_server]['auth'], $smtp_username, $smtp_password);
			} else {
				return $this->addLibError("SMTP serveren findes ikke.");
			}
		}
		
		/**
	 	 * Adds a connection to an SMTP server.
		 * 
		 * @param string $smtp_host
		 * <p>The SMTP server hostname.</p>
		 * <p>( E.g. <b>'smtp.gmail.com'</b> )</p>
		 * 
		 * @param int $smtp_port
		 * <p>The SMTP server port number (465, 587).</p>
		 * 
		 * @param string $smtp_username
		 * <p>The SMTP server authentication username.</p>
		 * <p>Commongly an email addresse.</p>
		 * 
		 * @param string $smtp_password
		 * <p>The SMTP server authentication password.</p>
		 * 
		 * @param string $smtp_auth [optional]
		 * <p>The <i>$smtp_auth</i> changes the connection authentication (ssl, starttls).</p>
		 * 
		 * @return boolean <b>TRUE</b> if the connection is established, <b>FALSE</b> if not.
		 */
		private function smtpConnect($smtp_host, $smtp_port, $smtp_username, $smtp_password, $smtp_auth) {
			$this->has_smtp = true;
			$this->smtp_host = $smtp_host;
			$this->smtp_port = $smtp_port;
			$this->smtp_username = $smtp_username;
			$this->smtp_password = $smtp_password;
			$this->smtp_auth = $smtp_auth;
			
			return true;
		}
		
		/**
		 * Clears all Mailer properties.
		 */
		private function clearMailer() {
			$this->sender = array();
			$this->recipients = array();
			$this->cc = array();
			$this->bcc = array();
			$this->reply_to = array();
			$this->subject = null;
			$this->message = null;
			$this->alt_message = null;
			
			$this->has_smtp = false;
			$this->smtp_host = null;
			$this->smtp_port = null;
			$this->smtp_username = null;
			$this->smtp_password = null;
			
			$this->phpmailer = new PHPMailer();
			$this->phpmailer->IsMail();

			$this->phpmailer->CharSet = configuration::getMailCharset();

			$this->default_sender[] = array('mail' => configuration::getDefaultMailSenderAddress(), 'name' => configuration::getDefaultMailSenderName());
			$this->default_recipients[] = array('mail' => configuration::getDefaultSystemMailRecieverMail(), 'name' => configuration::getDefaultSystemMailRecieverName());
		}
	}

?>