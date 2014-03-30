<?php

	class RTVLStreamProgrammering {
		private $dagen;
		private $uren;
		private $progProps;
		private $programmering = array();
		private $programmas = array();

		function __construct($d,$h,$pp) {
			$this->dagen = $d;
			$this->uren = $h;
			$this->progProps = $pp;
		}		

		public function initializeDB() {	
			$this->programmas = $this->getProgrammas(); 
			foreach($this->dagen as $day) {
				$dag = array();
				$query = "SELECT H,".$day." FROM ".RTVL_Programmering_Table." ORDER BY 'H' ASC";
				$results = mysql_query($query) or die (mysql_error());
				while($row = mysql_fetch_array($results)) {
					$dag[$row["H"]] = $this->programmas[$row[$day]];
				}
				$this->programmering[$day] = $dag;
			}
		}
		
		public function getProgramAt($day,$hour) {
			return $this->programmering[$day][$hour];
		}
		
		public function getDayArray() {
			return $this->dagen;
		}
		
		public function getHoursArray() {
			return $this->uren;
		}
		
		public function getDay($day) {
			return $this->programmering[$day];
		}
		
		public function getProgramById($id) {
			return $this->programmas[$id];
		}
		
		public function getProgrammas() {
			$query = "SELECT id FROM ".RTVL_Programma_Table." ORDER BY id ASC";
			$results = mysql_query($query) or die (mysql_error());
			while($row = mysql_fetch_array($results)) {
				$programmas[$row["id"]] = new RTVLProgram($row["id"],$this->progProps);
			}
			return $programmas;
		}
		
		public function getProgProps() {
			return $this->progProps;
		}
		
		public function toString($day) {
			$dayObject = $this->programmering[$day];
			$returnString = "<table id='top'><tr><td>Tijd</td><td>Programma</td></tr>";
			$beginTime = 0;
			$hasNext = false;
		
			for($i = 0; $i < count($dayObject); $i++) {
				if($dayObject[$i+1] != null) {
					$hasNext = true;
				}else {
					$hasNext = false;
				}
				if($hasNext) {
					$bool = $dayObject[$i+1]->getName() == $dayObject[$i]->getName();
					if (!$bool) {
						$endTime = $i+1;
						if(strlen($endTime) == 1) {
							$endTime = 0 . $endTime;
						}
						if(strlen($beginTime) == 1) {
							$beginTime = 0 . $beginTime;
						}
						$returnString .= "<tr><td>".$beginTime.".00-".$endTime.".00</td><td><a href='".$_SERVER["REQUEST_URI"]."#".$dayObject[$i]->getName()."'>".$dayObject[$i]->getName()."</a></td></tr>";
						$beginTime = $endTime;
					}
				}else {
					$endTime = 0;
					if(count($endTime) == 1) {
						$endTime = 0 . $endTime;
					}
					if(strlen($beginTime) == 1) {
						$beginTime = 0 . $beginTime;
					}
					$returnString .= "<tr><td>".$beginTime.".00-".$endTime.".00</td><td><a href='".$_SERVER["REQUEST_URI"]."#".$dayObject[$i]->getName()."'>".$dayObject[$i]->getName()."</a></td></tr>";
				}
			}
			$returnString .= "</table><br />";
			return $returnString;
		}
		
		public function programsToString($day) {
			$dayObject = $this->programmering[$day];
			$returnString = "";
			$programs = array();
			$medewerkers = "";
			for($i = 0; $i < count($dayObject); $i++) {
				if(!(in_array($dayObject[$i]->getName(),$programs))) {
					$medewerkers = "";
					if(!($dayObject[$i]->getPresentator() == "")) {
						$medewerkers .= "Presentator: ".$dayObject[$i]->getPresentator()."</br />";
					}
					if(!($dayObject[$i]->getTechniek() == "")) {
						$medewerkers .= "Techniek: ".$dayObject[$i]->getTechniek()."</br />";
					}
					if(!($dayObject[$i]->getRedactie() == "")) {
						$medewerkers .= "Redactie: ".$dayObject[$i]->getRedactie()."</br />";
					}
					$returnString .= "<p style='margin-top: 10px' id='".$dayObject[$i]->getName()."'><strong>".$dayObject[$i]->getName()."</strong> | <a href='".$_SERVER["REQUEST_URI"]."#top'>terug naar top van pagina</a><br />".$dayObject[$i]->getOmschrijving()."<br /><br />".$medewerkers."</p><br />";
					$programs[] = $dayObject[$i]->getName();
				}
			}
			return $returnString;
		}
	}
?>