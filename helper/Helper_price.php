<?php

	class Helper_price {
		
		/**
		 * Converts a price from display to computed.
		 * 
		 * @param string $price
		 * <p>The price in display format.</p>
		 * 
		 * @return float The price converted to computed.
		 */
		public static function display2Computed($price) {
			if(is_string($price)) {
				if(strpos($price, ',') && strpos($price, '.')) {
					if(strpos($price, ',') < strpos($price, '.')) {
						$price = (float)str_replace(',', '', $price);
					} else {
						$price = str_replace('.', '', $price);
						$price = (float)str_replace(',', '.', $price);
					}
				} elseif(strpos($price, ',')) {
					$price = (float)str_replace(',', '.', $price);
				} else {
					$price = (float)$price;
				}
			}
			
			return (float)$price;
		}
		
		/**
		 * Converts a price from minor to computed.
		 * 
		 * @param int $price
		 * <p>The price in minor format.</p>
		 * 
		 * @return float The price converted to computed.
		 */
		public static function minor2Computed($price) {
			return (float)((int)$price / 100);
		}
		
		/**
		 * Converts a price from display or computed to minor.
		 * 
		 * @param string|int|float $price
		 * <p>The price in display or computed format.</p>
		 * 
		 * @return int The price converted to minor.
		 */
		public static function displayAndComputed2Minor($price) {
			if(is_string($price)) {
				$price = self::display2Computed($price);
			}
			
			$price = (float)number_format($price, 2, '.', '');
			$price = (int)($price * 100);
			
			return $price;
		}
		
		/**
		 * Rounds a price to nearest minor.
		 * 
		 * @param string|int|float $price
		 * <p>The price in display or computed format.</p>
		 * 
		 * @param string $type
		 * <p>The round type.</p>
		 * <p>( E.g. <b>'round', 'up', 'down'</b> )</p>
		 * 
		 * @param int $nearest
		 * <p>The nearest minor to round to.</p>
		 * 
		 * @return float The price rounded to nearest minor.
		 */
		public static function round2Nearest($price, $type = "round", $nearest = 25) {
			if(is_string($price)) {
				$price = self::display2Computed($price);
			}
			
			$converting_num = 100 / (int)$nearest;
			
			switch($type) {
				case "up":
					$price = ceil($price * $converting_num);
					break;
				case "down":
					$price = floor($price * $converting_num);
					break;
				default:
					$price = round($price * $converting_num);
					break;
			}
			
			$price = $price / $converting_num;
			
			return (float)$price;
		}
	}

?>