<?php

	class Helper_date {
		
		/**
		 * Fetch danish month name.
		 * 
		 * @param int $key
		 * <p>The month number.</p>
		 *
		 * @return string|boolean The month name if it exists, <b>FALSE</b> if it doesn't.
		 */
		public static function getDanishMonth($key) {
			$months = array(
				1 => "januar",
				2 => "februar",
				3 => "marts",
				4 => "april",
				5 => "maj",
				6 => "juni",
				7 => "juli",
				8 => "august",
				9 => "september",
				10 => "oktober",
				11 => "november",
				12 => "december"
			);
			
			return array_key_exists((int)$key, $months) ? $months[(int)$key] : false;
		}
		
		/**
		 * Fetch danish weekday name.
		 * 
		 * @param int $key
		 * <p>The weekday number.</p>
		 *
		 * @return string|boolean The weekday name if it exists, <b>FALSE</b> if it doesn't.
		 */
		public static function getDanishDay($key) {
			$days = array(
				1 => "mandag",
				2 => "tirsdag",
				3 => "onsdag",
				4 => "torsdag",
				5 => "fredag",
				6 => "lørdag",
				7 => "søndag"
			);
			
			return array_key_exists((int)$key, $days) ? $days[(int)$key] : false;
		}
	}

?>