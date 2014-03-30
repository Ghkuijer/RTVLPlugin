<?php

/**
 * Adds RTVL_Pages functionality.
 */

global $wpdb;
define(RTVL_Programma_Table,$wpdb->prefix . "rtv_programmas");
define(RTVL_Programmering_Table,$wpdb->prefix . "rtv_programmering");
 
class RTVLPages {
	
	private $programmering;
	private $programmas;
	private $dagen = array("Mon","Tue","Wed","Thu","Fri","Sat","Sun");
	private $dutchDays = array("Maandag","Dinsdag","Woensdag","Donderdag","Vrijdag","Zaterdag","Zondag");
	private $uren = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23);
	private $progProps = array("naam","omschrijving","presentatie","techniek","redactie","cam");
	
	/**
	 * Construct RTVLPages to supply function for program pages.
	 */
	public function __construct() {
		$this->initializeDB();
	}
	
	public function initializeDB() {	
		global $wpdb;
		
		$this->programmas = $this->getProgrammas(); 
			
		foreach($this->dagen as $day) {
			$dag = array();
			$query = "SELECT H,".$day." FROM ".RTVL_Programmering_Table." ORDER BY 'H' ASC";
			$results = $wpdb->get_results($query,"ARRAY_A");
			foreach($results as $result){
				$dag[$result["H"]] = $this->programmas[$result[$day]];
			}
			$this->programmeringTable[$day] = $dag;
		}
	}
	
	public function getProgrammas() {
		global $wpdb;
		$query = "SELECT id FROM ".RTVL_Programma_Table." ORDER BY id ASC";
		$results = $wpdb->get_results($query,"ARRAY_A");
		foreach($results as $result) {
			$programmas[$result["id"]] = new RTVLProgram($result["id"],$this->progProps);
		}
		return $programmas;
	}

	public function displayDayProgrTable($day) {
		$resultString = '<table id="top"><tr><td>Tijd:</td><td>Programma</td></tr>';
		$dayProg = $this->programmeringTable[$day];
		$startTime = "00";
		foreach($dayProg as $hour => $programma) {
			$HHs = $this->computeHH($hour);
			if($dayProg[$HHs["hNext"]]->getId() != $programma->getId()) {
				$endTime = $HHs["hhNext"];
				$resultString .= '<tr><td>'.$startTime.':00-'.$endTime.':00</td><td><a href="index.php?page_id='.get_query_var('page_id').'#'.$programma->getId().'">'.$programma->getFeature("naam").'</a></td></tr>';
				$startTime = $endTime;
			}
		}
		$resultString .= '</table>';
		echo $resultString;
	}
	
	public function displayDayPrograms($day) {
		$resultString = "";
		$dayProg = $this->programmeringTable[$day];
		foreach($dayProg as $hour => $programma) {
			$HHs = $this->computeHH($hour);
			if($dayProg[$HHs["hNext"]]->getId() != $programma->getId()) {
				$resultString .= '<p style="margin-top:10px" id="'.$programma->getId().'"><strong>'.$programma->getFeature("naam").'</strong> | <a href="index.php?page_id='.get_query_var('page_id').'#top">Terug naar top van pagina</a><br />'.$programma->getFeature("omschrijving").'</p>';
				$resultString .= '<p>Presentator: '.$programma->getFeature("presentatie").'<br />';
				if($programma->getFeature("techniek") != "") { $resultString .= 'Techniek: '.$programma->getFeature("techniek").'<br />'; }
				if($programma->getFeature("redactie") != "") { $resultString .= 'Redactie: '.$programma->getFeature("redactie").'<br />'; }
				$resultString .= '</p>';
			}
		}
		echo $resultString;
	}
	
	public function displayProgramList() {
		$resultString = '<p id="top"></p>';
		foreach($this->programmas as $id => $programma) {
			$resultString .= '<p style="margin-top:10px" id="'.$programma->getId().'"><strong>'.$programma->getFeature("naam").'</strong> | <a href="index.php?page_id='.get_query_var('page_id').'#top">Terug naar top van pagina</a><br />'.$programma->getFeature("omschrijving").'</p>';
			$resultString .= '<p>Presentator: '.$programma->getFeature("presentatie").'<br />';
			if($programma->getFeature("techniek") != "") { $resultString .= 'Techniek: '.$programma->getFeature("techniek").'<br />'; }
			if($programma->getFeature("redactie") != "") { $resultString .= 'Redactie: '.$programma->getFeature("redactie").'<br />'; }
			$resultString .= '</p>';
		}
		echo $resultString;
	}
	
	private function computeHH($hour) {
		if(strlen($hour) != 2) {
			$hh = "0".$hour;
		} else { $hh = $hour; }
		if($hour == 23) { $hourNext = 0; }
		else { $hourNext = $hour+1; }
		if(strlen($hourNext) != 2) {
			$hhNext = "0".$hourNext;
		} else { $hhNext = $hourNext; }
		return array("hh" => $hh, "hhNext" => $hhNext, "h" => $hour, "hNext" => $hourNext);
	}

	public function toStringAll() {
		$returnString = '<table class="programTable" id="top"><tr><td class="days"></td>';
		
		foreach($this->dutchDays as $day) {
			$returnString .= '<td class="days">'.$day.'</td>';
		}
		$returnString .= '</tr>';
	
		for($i = 0; $i <= end($this->uren); $i++) {
			$end = $this->findEnd($i);
			if(strlen($i) == 1) {
				$beginTime = "0".$i;
			} else {
				$beginTime = $i;
			}
			if(strlen($end+1) == 1) {
				$endTime = "0".($end+1);
			} else {
				$endTime = $end+1;
			}
			$returnString .= '<tr><td class="times">'.$beginTime.'.00 - '.$endTime.'.00</td>';
			foreach($this->dagen as $day) {
				$returnString .= '<td class="programName '.$day.' hour-'.$i.'">'.$this->programmeringTable[$day][$i]->getFeature("naam").'</td>';
			}
			$returnString .= '</tr>';
			$i = $end;
		}
		$returnString .= '</table>';
	
		return $returnString;
	}

	public function findEnd($h) {
		$end = $h;
		if($h != end($this->uren)) {
			$endNow = false;
			while(!$endNow && $end != end($this->uren)) {
				foreach($this->dagen as $day) {
					if($this->programmeringTable[$day][$end]->getId() != $this->programmeringTable[$day][$end+1]->getId()) {
						$endNow = true;
					}
				}
				if(!$endNow) {
					$end++;
				}
			}
		}
		return $end;
	}

}
?>