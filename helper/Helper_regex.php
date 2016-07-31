<?php

	class Helper_regex {
		
		/**
		 * Removes mobile emoticons from text.
		 * 
		 * @param string $text
		 * <p>The text to remove emoticons from.</p>
		 * 
		 * @return string text without emoticons.
		 */
		public static function removeEmoticons($text) {
			// Match Emoticons
			$regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
			$clean_text = preg_replace($regexEmoticons, "", $text);
			
			// Match Miscellaneous Symbols and Pictographs
			$regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
			$clean_text = preg_replace($regexSymbols, "", $clean_text);
			
			// Match Transport And Map Symbols
			$regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
			$clean_text = preg_replace($regexTransport, "", $clean_text);
			
			// Match Miscellaneous Symbols
			$regexMisc = '/[\x{2600}-\x{26FF}]/u';
			$clean_text = preg_replace($regexMisc, "", $clean_text);
			
			// Match Dingbats
			$regexDingbats = '/[\x{2700}-\x{27BF}]/u';
			$clean_text = preg_replace($regexDingbats, "", $clean_text);
			
			// Match New Emoticons
			$regexNewEmoticons = '/([0-9#][\x{20E3}])|[\x{00ae}\x{00a9}\x{203C}\x{2047}\x{2048}\x{2049}\x{3030}\x{303D}\x{2139}\x{2122}\x{3297}\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F6FF}][\x{FE00}-\x{FEFF}]?/u';
			$clean_text = preg_replace($regexNewEmoticons, "", $clean_text);
			
			return trim($clean_text);
		}
		
		/**
		 * Replaces multiple whitespaces and line breaks, with a single whitespace.
		 * 
		 * @param string $text
		 * <p>The text to replace multiple whitespaces and line breaks from.</p>
		 * 
		 * @return string The text without multiple whitespaces and line breaks.
		 */
		public static function replaceMultipleWhitespaces($text) {
			return trim(preg_replace('/\s+/', " ", $text));
		}
		
		/**
		 * Removes symbols from a text.
		 * 
		 * @param string $text
		 * <p>The text to remove symbols from.</p>
		 * 
		 * @return string The text without symbols.
		 */
		public static function removeSymbols($text) {
			return trim(preg_replace('/[^\p{L}\p{N}\s]/u', "", $text));
		}
		
		/**
		 * Modifies the URL prefix.
		 * 
		 * @param string $url
		 * <p>The URL to modify.</p>
		 * 
		 * @param string|boolean $protocol [optional]
		 * <p>Which protocol to apply, <b>FALSE</b> if none.</p>
		 * 
		 * @param boolean $www [optional]
		 * <p>Set to <b>TRUE</b> to use www prefix, <b>FALSE</b> if not.</p>
		 * 
		 * @return string The modified URL.
		 */
		public static function modifyUrlPrefix($url, $protocol = "http", $www = false) {
			$url = str_replace(array("https://", "http://", "www."), "", strtolower($url));
			
			if($www !== false) {
				$url = 'www.' .$url;
			}
			
			if($protocol !== false && ($protocol === "http" || $protocol === "https")) {
				$url = $protocol. '://' .$url;
			}
			
			return $url;
		}
	}

?>