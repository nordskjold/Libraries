<?php

	class Lib_calendar extends Lib_base {
		
		private $start_date, $end_date, $all_day_event;
		
		private $summary, $description, $url, $location;
		
		private $event_repeat;
		
		private $alarm_times = array();
		
		function __construct() {}
		
		private function __clone() {}
		
		/**
		 * Sets calendar start and end date.
		 * 
		 * @param DateTime $start_date
		 * <p>The calendar event start date.</p>
		 * 
		 * @param DateTime $end_date
		 * <p>The calendar event end date.</p>
		 * 
		 * @param boolean $all_day_event [optional]
		 * <p>Set to <b>TRUE</b> for an all day event.</p>
		 * 
		 * @return boolean <b>TRUE</b> on success, <b>FALSE</b> if the dates are incorrect.
		 */
		public function setCalendarTime(DateTime $start_date, DateTime $end_date, $all_day_event = false) {
			if($start_date > $end_date) {
				return $this->addLibError("Kalenderens start tidspunkt kan ikke forekomme efter slut tidspunktet.");
			}
			
			$this->start_date = $start_date;
			$this->end_date = $end_date;
			$this->all_day_event = $all_day_event;
			
			return true;
		}
		
		/**
		 * Sets calendar properties.
		 * 
		 * @param string $summery [optional]
		 * <p>The calendar event summery text.</p>
		 * 
		 * @param string $description [optional]
		 * <p>The calendar event description text.</p>
		 * 
		 * @param string $url [optional]
		 * <p>The calendar event url.</p>
		 * 
		 * @param string $location [optional]
		 * <p>The calendar event location.</p>
		 * 
		 * @return boolean <b>TRUE</b> on success.
		 */
		public function setCalendarProperties($summary, $description, $url, $location) {
			$this->summary = preg_replace('/([\,;])/','\\\$1', $summary);
			$this->description = preg_replace('/([\,;])/','\\\$1', $description);
			$this->url = preg_replace('/([\,;])/','\\\$1', $url);
			$this->location = preg_replace('/([\,;])/','\\\$1', $location);
			
			return true;
		}
		
		/**
		 * Sets calendar repeat frequency.
		 * 
		 * @param string $repeat
		 * <p>The calendar repeat frequency.</p>
		 * <p>( E.g. <b>'DAILY', 'WEEKLY', 'MONTHLY', 'YEARLY'</b> )</p>
		 * 
		 * @return boolean <b>TRUE</b> on success, <b>FALSE</b> if the frequency doesn't exist.
		 */
		public function setCalendarRepeat($repeat) {
			$repeat = strtoupper($repeat);
			
			if($repeat != "DAILY" && $repeat != "WEEKLY" && $repeat != "MONTHLY" && $repeat != "YEARLY") {
				return $this->addLibError("Kalenderens gentagelse er ikke korrekt.");
			}
			
			$this->event_repeat = $repeat;
			
			return true;
		}
		
		/**
		 * Sets a calendar alarm.
		 * 
		 * @param DateTime $alarm_time
		 * <p>The alarm trigger date.</p>
		 * 
		 * @param string $uid [optional]
		 * <p>The alarm trigger UID.</p>
		 * 
		 * @return boolean <b>TRUE</b> on success.
		 */
		public function setCalendarAlarm(DateTime $alarm_time, $uid = null) {
			$minutes = ($this->start_date->getTimestamp() - $alarm_time->getTimestamp()) / 60;
			
			if(! $uid) {
				$uid = uniqid();
			}
			
			$alarm = array("uid" => $uid, "trigger" => $minutes);
			
			$this->alarm_times[] = $alarm;
			
			return true;
		}
		
		/**
		 * Creates a calendar file.
		 * 
		 * @param string $path
		 * <p>Absolut path from the root.</p>
		 * 
		 * @param string $calendar_name [optional]
		 * <p>The calendar name.</p>
		 * 
		 * @param string $uid [optional]
		 * <p>The calendar UID.</p>
		 * 
		 * @param integer $sequence [optional]
		 * <p>The calendar sequence number for updates.</p>
		 * 
		 * @return string|boolean Calendar UID on success, <b>FALSE</b> if creating the calendar file fails.
		 */
		public function createCalendarFile($path, $calendar_name = null, $uid = null, $sequence = null) {
			if(! $this->start_date instanceof DateTime || ! $this->end_date instanceof DateTime) {
				return $this->addLibError("Kalender skal bruge et start og slut tidspunkt.");
			}
			
			if(! $uid) {
				$uid = uniqid();
			}
			
			$now = new DateTime();
			
			$file_content = 'BEGIN:VCALENDAR'."\n";
				$file_content .= 'CALSCALE:GREGORIAN'."\n";
				$file_content .= 'VERSION:2.0'."\n";
				$file_content .= 'METHOD:PUBLISH'."\n";
				$file_content .= 'STATUS:CONFIRMED'."\n";
				$file_content .= 'PRODID:-//NoEm//NES//DA'."\n";
				
				if($calendar_name != "") {
					$file_content .= 'X-WR-CALNAME;VALUE=TEXT:' .$calendar_name. "\n";
				}
				
				$file_content .= 'BEGIN:VEVENT'."\n";
					$file_content .= 'CREATED:' .$now->format("Ymd\THis\Z"). "\n";
					$file_content .= 'UID:' .$uid. "\n";
					
					if($this->url != "") {
						$file_content .= 'URL;VALUE=URI:' .$this->url. "\n";
					}
					
					if($this->all_day_event == false) {
						$file_content .= 'DTEND;TZID=Europe/Copenhagen:' .$this->end_date->format("Ymd\THis"). "\n";
					} else {
						$file_content .= 'DTEND;TZID=Europe/Copenhagen:' .$this->end_date->format("Ymd"). "\n";
					}
					
					$file_content .= 'TRANSP:OPAQUE'."\n";
					
					if($this->summary != "") {
						$file_content .= 'SUMMARY:' .$this->summary. "\n";
					}
					
					if($this->all_day_event == false) {
						$file_content .= 'DTSTART;TZID=Europe/Copenhagen:' .$this->start_date->format("Ymd\THis"). "\n";
					} else {
						$file_content .= 'DTSTART;TZID=Europe/Copenhagen:' .$this->start_date->format("Ymd"). "\n";
					}
					
					$file_content .= 'DTSTAMP:' .$now->format("Ymd\THis\Z"). "\n";
					
					if($this->location != "") {
						$file_content .= 'LOCATION:' .$this->location. "\n";
					}
					
					if($sequence) {
						$file_content .= 'SEQUENCE:' .$sequence. "\n";
					}
					
					if($this->event_repeat != "") {
						$file_content .= 'RRULE:FREQ=' .$this->event_repeat. "\n";
					}
					
					if($this->description != "") {
						$file_content .= 'DESCRIPTION:' .$this->description. "\n";
					}
					
					if(! empty($this->alarm_times)) {
						foreach($this->alarm_times as $alarm_time) {
							$file_content .= 'BEGIN:VALARM'."\n";
								$file_content .= 'X-WR-ALARMUID:' .$alarm_time["uid"]. "\n";
								$file_content .= 'UID:' .$alarm_time["uid"]. "\n";
								$file_content .= 'TRIGGER:-PT' .$alarm_time["trigger"]. 'M'."\n";
								$file_content .= 'ACTION:NONE'."\n";
							$file_content .= 'END:VALARM'."\n";
						}
					}
					
				$file_content .= 'END:VEVENT'."\n";
			$file_content .= 'END:VCALENDAR'."\n";
			
			file_put_contents($this->root.trim($path, "/").DIRECTORY_SEPARATOR.$uid. '.ics', $file_content);
			
			$this->clearObj();
			
			return $uid;
		}
		
		/**
		 * Clears the object for re-use.
		 */
		private function clearObj() {
			$this->start_date = null;
			$this->end_date = null;
			$this->summary = null;
			$this->description = null;
			$this->url = null;
			$this->location = null;
			$this->event_repeat = null;
			$this->alarm_times = null;
			
			clearstatcache();
		}
	}

?>