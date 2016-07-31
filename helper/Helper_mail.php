<?php

	class Helper_mail {
		
		/**
		 * @var Lib_mailer
		 */
		private static $mail_lib;
		
		/**
		 * Initialize mail template system.
		 * 
		 * @param string $sender_name
		 * <p>The mail sender name.</p>
		 * 
		 * @param string $reciever
		 * <p>The mail reciever name.</p>
		 * 
		 * @param string $subject
		 * <p>The mail subject.</p>
		 * 
		 * @param string $headline
		 * <p>The mail headline.</p>
		 * 
		 * @param string $content
		 * <p>The mail content.</p>
		 */
		private static function initMail($sender_name, $reciever, $subject, $headline, $content) {
			$content .= self::getEmailFooter();
			
			self::$mail_lib = new Lib_mailer();
			
			self::$mail_lib->addSMTPFromSupportedServers("surftown", "syste@nørdskjold.dk", "nordskjold14");
			self::$mail_lib->addSender("system@xn--nrdskjold-l8a.dk", $sender_name);
			self::$mail_lib->addRecipients($reciever);
			self::$mail_lib->addSubject($subject);
			self::$mail_lib->addMessageFromHtmlFile("template/emails/template.html", array("[HEADLINE]" => $headline, "[CONTENT]" => $content));
		}
		
		/**
		 * Fetch system info for mail templates.
		 * 
		 * @param UserModel $user
		 * <p>The recipient user model.</p>
		 * 
		 * @return array The system info.
		 */
		private static function getSystemInfo(UserModel $user) {
			$base_systems = array(
				"main" => array(
					"name" => "main",
					"display_name" => "Nørdskjold",
					"hostname" => "nørdskjold.dk"
				),
				"clash" => array(
					"name" => "clash",
					"display_name" => "Dansk Adult Clash",
					"hostname" => "dansk-clash.dk"
				),
				"royale" => array(
					"name" => "royale",
					"display_name" => "Dansk Adult Royale",
					"hostname" => "dansk-royale.dk"
				)
			);
			
			$systems = array();
			
			if(! empty(array_intersect(array("arkitekt"), $user->roles))) {
				$systems[] = $base_systems["main"];
			}
			
			if(! empty(array_intersect(array("clash medlem", "clash vejleder", "clash chef", "clash leder"), $user->roles))) {
				$systems[] = $base_systems["clash"];
			}
			
			if(! empty(array_intersect(array("royale medlem", "royale vejleder", "royale chef", "royale leder"), $user->roles))) {
				$systems[] = $base_systems["royale"];
			}
			
			if(empty($systems)) {
				$systems[] = $base_systems["main"];
			}
			
			return $systems;
		}
		
		/**
		 * Creates the e-mail template footer.
		 * 
		 * @return string The e-mail footer.
		 */
		private static function getEmailFooter() {
			$content = "<br />";
			$content .= "<h2>Forkert E-Mail?</h2>";
			$content .= "<p>Hvis du ikke kender noget til ovenstående meddelse, kan du ignorere denne henvendelse.</p>";
			$content .= "<br />";
			$content .= "<p>Denne e-mail kan ikke besvares.</p>";
			
			return $content;
		}
		
		/**
		 * Sends a confirm your e-mail message.
		 * 
		 * @param UserModel $user
		 * <p>The recipient user model.</p>
		 */
		public static function confirmEmail(UserModel $user) {
			$system = self::getSystemInfo($user);
			
			$subject = "Bekræft E-Mail";
			$headline = "E-Mail verifikation";
			
			$content = "<h2>Intro</h2>";
			$content .= '<p>Velkommen til ' .implode(" & ", array_map(function($s) { return $s["display_name"]; }, $system)). ', du er snart færdig med din tilmelding. Det er vigtigt at du bekræfter din e-mail, ellers bliver din ansøgning ikke behandlet. Efter du har verificeret at det er din e-mail adresse, skal du være opmærksom på at du ikke ville kunne logge ind lige med det samme, medlemskab skal godkendes.</p>';
			$content .= "<br />";
			$content .= "<h2>Gennemfør ansøgning</h2>";
			$content .= '<p>Tryk på <a href="http://' .$system[0]["hostname"]. '/public/login.php?action=ConfirmEmail&email=' .$user->getEmail(). '">linket her</a> for at bekræfte din e-mail.</p>';
			
			self::initMail("Nørdskjold", $user->getEmail(), $subject, $headline, $content);
			self::$mail_lib->sendMail();
		}
		
		/**
		 * Sends a re-confirm your e-mail message.
		 * 
		 * @param UserModel $user
		 * <p>The recipient user model.</p>
		 */
		public static function reConfirmEmail(UserModel $user) {
			$system = self::getSystemInfo($user);
			
			$subject = "Bekræft E-Mail ændring";
			$headline = "E-Mail verifikation";
			
			$content = "<h2>Intro</h2>";
			$content .= '<p>Ny E-Mail til ' .implode(" & ", array_map(function($s) { return $s["display_name"]; }, $system)). '. Det er vigtigt at du bekræfter din e-mail, ellers bliver dit login ikke aktiveret.</p>';
			$content .= "<br />";
			$content .= "<h2>Gennemfør aktivering</h2>";
			$content .= '<p>Tryk på <a href="http://' .$system[0]["hostname"]. '/public/login.php?action=ConfirmEmail&email=' .$user->getEmail(). '&activate=true">linket her</a> for at bekræfte din e-mail.</p>';
			
			self::initMail("Nørdskjold", $user->getEmail(), $subject, $headline, $content);
			self::$mail_lib->sendMail();
		}
		
		/**
		 * Sends a forgot password message.
		 * 
		 * @param UserModel $user
		 * <p>The recipient user model.</p>
		 * 
		 * @param string $new_password
		 * <p>The recipients new password.</p>
		 */
		public static function forgotPassword(UserModel $user, $new_password) {
			$system = self::getSystemInfo($user);
			
			$subject = "Nyt kodeord";
			$headline = "Nulstil kodeord";
			
			$content = "<h2>Surt at være glemsom</h2>";
			$content .= '<p>Dit kodeord er blevet nulstillet, dit nye kodeord er: <b>' .$new_password. '</b></p>';
			$content .= '<p>Sørg for at ændre dit kodeord med det samme, <a href="http://' .$system[0]["hostname"]. '/public/login.php">tryk her</a> for at logge ind.</p>';
			
			self::initMail("Nørdskjold", $user->getEmail(), $subject, $headline, $content);
			self::$mail_lib->sendMail();
		}
		
		/**
		 * Sends a new applicant message.
		 * 
		 * @param UserModel $user
		 * <p>The recipient user model.</p>
		 */
		public static function newApplicant(UserModel $user) {
			$system = self::getSystemInfo($user);
			
			$subject = "Ny login ansøgning";
			$headline = "Ny ansøger";
			
			$content = "<h2>Hvor?</h2>";
			$content .= '<p>Der er forespurgt efter et nyt login til ' .implode(" & ", array_map(function($s) { return $s["display_name"]; }, $system)). '.</p>';
			$content .= '<br />';
			$content .= '<h2>Hvem?</h2>';
			$content .= '<p>E-Mail adressen der er blevet brugt:<br />' .$user->getEmail(). '.</p>';
			$content .= '<p>Kort beskrivelse:<br />' .$user->information->getNote(). '.</p>';
			$content .= '<br />';
			$content .= '<p>For at behandle ansøgningen kan du <a href="http://nørdskjold.dk/public/login.php">trykke her</a>.</p>';
			
			self::initMail("Nørd Alarm", "mikkel@xn--nrdskjold-l8a.dk", $subject, $headline, $content);
			self::$mail_lib->sendMail();
		}
		
		/**
		 * Sends a welcome message.
		 * 
		 * @param UserModel $user
		 * <p>The recipient user model.</p>
		 */
		public static function confirmNewApplicant(UserModel $user) {
			$system = self::getSystemInfo($user);
			
			$subject = "Login aktiveret";
			$headline = "Ansøgning godkendt";
			
			$content = "<h2>Velkommen</h2>";
			$content .= '<p>Din ansøgning om et login til ' .implode(" & ", array_map(function($s) { return $s["display_name"]; }, $system)). ' er blevet godkendt og du kan nu logge ind og benytte dig af alle funktionaliteter som følger.</p>';
			$content .= '<p>Brug <a href="http://' .$system[0]["hostname"]. '/public/login.php">linket her</a> for at gå direkte til login siden.</p>';
			
			self::initMail("Nørdskjold", $user->getEmail(), $subject, $headline, $content);
			self::$mail_lib->sendMail();
		}
	}

?>