<?php

	class Helper_converter {
		
		/**
		 * Converts bytes into a readable format.
		 * 
		 * @param string|int|float $bytes
		 * <p>Bytes to format.</p>
		 * 
		 * @param string $from_format
		 * <p>Format which bytes should be converted from.</p>
		 * <p>( E.g. <b>'kb', 'mb', 'gb'</b> )</p>
		 * 
		 * @param string $to_format
		 * <p>Format which bytes should be converted to.</p>
		 * <p>( E.g. <b>'kb', 'mb', 'gb'</b> )</p>
		 * 
		 * @return float The byte converted.
		 */
		public static function convertByteFromTo($bytes, $from_format, $to_format) {
			$from_format = in_array(strtolower($from_format), array("b", "kb", "mb", "gb", "tb")) ? strtolower($from_format) : "b";
			$to_format = in_array(strtolower($to_format), array("b", "kb", "mb", "gb", "tb")) ? strtolower($to_format) : "b";
			$byte_types = array("b" => 1, "kb" => 1024, "mb" => 1048576, "gb" => 1073741824, "tb" => 1099511627776);

			$bytes = ((float)$bytes * $byte_types[$from_format]) / $byte_types[$to_format];
			$bytes = (float)(round($bytes, 2) === 0.00 ? 0 : round($bytes, 2));
			
			return $bytes;
		}
		/**
		 * Converts bytes into the simplest readable format.
		 * 
		 * @param string|int|float $bytes
		 * <p>Bytes to format.</p>
		 * 
		 * @return string The converted byte as a string with extension.
		 */
		public static function convertByteToReadable($bytes) {
			$symbol = array("b", "kb", "mb", "gb", "tb");
			$exp = floor(log((float)$bytes) / log(1024));
			return sprintf('%.2f ' .$symbol[$exp], ((float)$bytes / pow(1024, floor($exp))));
		}
	}

?>