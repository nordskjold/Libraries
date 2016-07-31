<?php

	class Helper_csv {
		
		/**
		 * Parse a CSV file into an array.
		 * 
		 * @param string $path
		 * <p>Absolut path from the root.</p>
		 * 
		 * @param string $delimiter
		 * <p>The CSV delimiter that seperates values.</p>
		 * 
		 * @return array|boolean CSV data on succes, <b>FALSE</b> if not.
		 */
		public static function CSVFile2Array($path, $delimiter = ",") {
			$path = ltrim($path, '/');
			
			if(! file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.$path) || is_dir(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.$path)) {
				return false;
			}
			
			$test_split = explode(".", $path);
			
			if(strtolower(end($test_split)) !== "csv") {
				return false;
			}
			
			if(($handle = fopen($path, "r")) !== false) {
				$rows = array();
				
				while(($data = fgetcsv($handle, 1000, $delimiter)) !== false) {
					$rows[] = $data;
				}
				
				fclose($handle);
				
				return $rows;
			} else {
				return false;
			}
		}
		
		/**
		 * Parse a CSV string into an array.
		 * 
		 * @param string $csv
		 * <p>The string containing CSV data.</p>
		 * 
		 * @param string $delimiter
		 * <p>The CSV delimiter that seperates values.</p>
		 * 
		 * @return array|boolean CSV data on succes, <b>FALSE</b> if not.
		 */
		public static function CSVString2Array($csv, $delimiter = ",") {
			$rows = explode(PHP_EOL, $csv);
			
			foreach($rows as $row) {
				$row = str_getcsv($row, $delimiter);
			}
			
			return $rows;
		}
		
		/**
		 * Parse an array to a CSV file.
		 * 
		 * @param array $data
		 * <p>The array to write to CSV.</p>
		 * 
		 * @param string $file_name
		 * <p>Name of the CSV file without file extension.</p>
		 * 
		 * @param string $path
		 * <p>Absolut path from the root.</p>
		 * 
		 * @param string $delimiter
		 * <p>The CSV delimiter that seperates values.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the CSV file is created, <b>FALSE</b> if not.
		 */
		public static function array2CSV(array $data, $file_name, $path, $delimiter = ",") {
			if(! $data || empty($data) || ! $file_name || empty($file_name)) {
				return false;
			}
			
			$file_name = trim($file_name, "/");
			$path = trim($path, "/");
			
			$abs_path = dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR;
			$file = $abs_path.$file_name. '.csv';
			
			if(! is_dir($abs_path)) {
				return false;
			}
			
			if(($handle = fopen($file, "w+")) !== false) {
				foreach($data as $array) {
					if(gettype($array) == "array") {
						fputcsv($handle, $array, $delimiter);
					}
				}
				
				fclose($handle);
				
				return true;
			} else {
				return false;
			}
		}
	}

?>