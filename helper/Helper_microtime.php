<?php

	class Helper_microtime {
		
		private static $microtimeStart = null, $microtimeEnd = null, $microtimeLog = array();
		
		/**
		 * Starts or continues a microtime log.
		 * 
		 * @param string $caption
		 * <p>A short description for the log entry.</p>
		 */
		public static function log($caption = null) {
			$backtrace = debug_backtrace();
			$backtrace = array_shift($backtrace);
			
			list($usec, $sec) = explode(" ", microtime());
			$time = number_format((float)$usec + (float)$sec, 25, ".", "");
			
			$log = new stdClass();
			$log->line = $backtrace["line"];
			$log->caption = $caption;
			$log->time = $time;
			
			self::$microtimeLog[] = $log;
		}
		
		/**
		 * Sets a simple microtime start point.
		 */
		public static function start() {
			list($usec, $sec) = explode(" ", microtime());
			self::$microtimeStart = number_format((float)$usec + (float)$sec, 25, ".", "");
		}
		
		/**
		 * Sets a simple microtime end point.
		 */
		public static function end() {
			list($usec, $sec) = explode(" ", microtime());
			self::$microtimeEnd = number_format((float)$usec + (float)$sec, 25, ".", "");
		}
		
		/**
		 * Converts microtime into seconds.
		 * 
		 * @param float $start
		 * <p>Starting point in milliseconds.</p>
		 * 
		 * @param float $end
		 * <p>Ending point in milliseconds.</p>
		 * 
		 * @return float The time between the start and end point.
		 */
		private static function resultCalc($start, $end) {
			$result_raw = number_format($end - $start, 4, ".", "");
			
			$result = array("ms" => ceil($result_raw * 1000), "s" => 0);
			
			$clear_int = (float)floor($result_raw);
			$result_raw = (float)$result_raw - $clear_int;
			
			if($result_raw == 0) {
				$result_raw = 0.0001;
			}
			
			$result["s"] = $clear_int + ceil($result_raw * pow(10, 2)) / pow(10, 2);
			
			return $result;
		}
		
		/**
		 * Calculates result shert from microtime log or simple start and end point.
		 * 
		 * @param function $f [optional]
		 * <p>A function to microtime test.</p>
		 * 
		 * @return array|boolean An array containing the microtime data, <b>FALSE</b> if data is missing.
		 */
		public static function result($f = null) {
			if(! $f && empty(self::$microtimeLog)) {
				if(self::$microtimeStart && self::$microtimeEnd) {
					return self::$resultCalc(self::$microtimeStart, self::$microtimeEnd);
				}
				
				return false;
			} elseif(! $f && ! empty(self::$microtimeLog)) {
				$backtrace = debug_backtrace();
				$backtrace = array_shift($backtrace);

				list($usec, $sec) = explode(" ", microtime());
				$time = number_format((float)$usec + (float)$sec, 25, ".", "");

				$log = new stdClass();
				$log->line = $backtrace["line"];
				$log->caption = null;
				$log->time = $time;

				self::$microtimeLog[] = $log;

				$logs = array();
				$total = array("ms" => (float)0, "s" => (float)0);

				foreach(self::$microtimeLog as $k => $log) {
					if(array_key_exists(($k + 1), self::$microtimeLog)) {
						$start = $log;
						$end = self::$microtimeLog[($k + 1)];

						$result = self::$resultCalc($start->time, $end->time);
						$total["ms"] += $result["ms"];
						$total["s"] += $result["s"];

						$logs[] = 'Line ' .$start->line. '-' .$end->line. ';' .($start->caption !== null ? ' ' .$start->caption. ':' : ""). ' ' .$result["ms"]. ' ms., ' .$result["s"]. ' s.';
					}
				}

				$logs[] = 'Line ' .self::$microtimeLog[0]->line. '-' .self::$microtimeLog[count(self::$microtimeLog) - 1]->line. '; ' .$total["ms"]. ' ms., ' .$total["s"]. ' s.';

				return $logs;
			} else {
				self::$start();
				$f();
				self::$end();

				return self::$result();
			}
		}
		
	}

?>