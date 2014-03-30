<?php
	
	class RTVLProgrammering {
		
		private $dagen;
		private $dutchDays;
		private $uren;
		private $progProps;
		private $progFeats;
		private $programmering = array();
		private $programmas = array();
		
		function __construct($d,$h,$pp,$pf,$dd) {
			$this->dagen = $d;
			$this->uren = $h;
			$this->progProps = $pp;
			$this->progFeats = $pf;
			$this->dutchDays = $dd;
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
				$this->programmering[$day] = $dag;
			}
		}
		
		public function goodFormat() {
			if(count($this->getZeros()) == 0) 
				return true;
			else
				return false;
		}
		
		public function getZeros() {
			global $wpdb;
			$returnArray = array();
			
			foreach($this->dagen as $dag) {
				$missing = array();
				$sql = "SELECT `H` FROM ".RTVL_Programmering_Table." WHERE ".$dag."=0 ORDER BY `H` ASC";
				$results = $wpdb->get_results($sql,"ARRAY_A");
				foreach($results as $result) {
					$missing[] = $result['H'];
				}
				if(count($missing) != 0)
					$returnArray[$dag] = $missing;
			}
			return $returnArray;
		}

		public function getProgrammering() {
			return $this->programmering;
		}
		
		public function install_database() {
			global $wpdb;
			$col_dag_construct = "";
			$insert_dag_construct = "";
			foreach($this->dagen as $dag) {
				$col_dag_construct .= ",".$dag." int(11) NOT NULL";
				$insert_dag_construct .= ",".$dag;
			}
			$insert_H_construct = "";
			foreach($this->uren as $uur) {
				$insert_H_construct .= "(".$uur;
				$insert_H_dag_construct = "";
				foreach($this->dagen as $dag) {
					$insert_H_dag_construct .= ",0";
				}
				$insert_H_construct .= $insert_H_dag_construct."),";
			}
			$insert_H_construct = substr($insert_H_construct,0,-1);
			$programmaFeats = "";
			foreach($this->progProps as $key => $value) {
				$programmaFeats .= $value." ".$this->progFeats[$key].",";
			}
			$sql = "CREATE TABLE ".RTVL_Programmering_Table." (H int(2) NOT NULL".$col_dag_construct.");
				CREATE TABLE ".RTVL_Programma_Table." (
				id int(11) NOT NULL AUTO_INCREMENT, ".$programmaFeats."	UNIQUE KEY id (id)
			);
			INSERT INTO ".RTVL_Programmering_Table."	(H".$insert_dag_construct.") VALUES ".$insert_H_construct.";";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		}
		
		public function changeProgrammering($changesArray) {
			$sql = "";
			foreach($changesArray as $hour => $days) {
				$Set = "";
				$dayQuery = "UPDATE ".RTVL_Programmering_Table." SET ";
				foreach($days as $day => $programId) {
					$Set .= $day."=".$programId.",";
				}
				if($Set != "") {
					$sql .= $dayQuery.substr($Set,0,-1)." WHERE H=".$hour."; ";
				}
			}
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);	
			$this->initializeDB();
			return true;
		}
		
		public function addProgram($data) {
			// enter data in database
			global $wpdb;
			$col_Construct = "(";
			$data_Construct = "(";
			foreach($this->progProps as $key => $value) {
				$col_Construct .= $value.",";
				$data_Construct .= "'".$data[$value]."',";
			}
			$col_Construct = substr($col_Construct,0,-1).")";
			$data_Construct = substr($data_Construct,0,-1).")";
			$sql = "INSERT INTO ".RTVL_Programma_Table." ".$col_Construct." VALUES ".$data_Construct.";";
			$wpdb->query($sql);
			$this->uploadPicture(mysql_insert_id(),$data);			
			$this->initializeDB();
			return true;
		}
		
		private function uploadPicture($id,$data) {
			if($_FILES['file']['tmp_name'] != "" && !empty($_FILES['file']['tmp_name'])) { 
				// start image upload, start with checking for upload errors
				if ($_FILES["file"]["error"] > 0) {
					echo "error: ".$_FILES["file"]["error"]."<br />";
				} else {
					$imageString = $_FILES["file"]["name"];
					
					// analyze width and height make sure it is a square image.
					list($width, $height, $type, $attr) = getimagesize($_FILES["file"]["tmp_name"]);
					if($width == $height && $width == "150") {
					
						// start uploading the image to the uploads/programmas folder and name it by the program id.
						$i = strrpos($imageString,".");
						if ($i) {
							$length = strlen($imageString) - $i;
							$ext = strtolower(substr($imageString,$i+1,$length));
						}
						if($ext == "jpg" || $ext == "jpeg") {
							$uploads = wp_upload_dir();
							$newname = $uploads['path']."/programmas/".$id.".jpg";
							if(move_uploaded_file($_FILES['file']['tmp_name'], $newname)) {
								echo "upload done to: ".$newname."<br />";
							} else {
								echo "upload gone wrong to ".$newname." Error code ".$_FILES['file']['error']."<br />";
								switch ($_FILES['file']['error']) 
								 {  case 1:
										   echo 'The file is bigger than this PHP installation allows<br />';
										   break;
									case 2:
										   echo 'The file is bigger than this form allows<br />';
										   break;
									case 3:
										   echo 'Only part of the file was uploaded<br />';
										   break;
									case 4:
										   echo 'No file was uploaded<br />';
										   break;
								 }
							}
						} else {
							echo "verkeerde extensie namelijk ".$ext."<br />";
						}
					} else {
						echo "Geuploade foto is niet vierkant of niet 150x150 pixels, aub vierkante foto van 150x150 pixels aan het programma toevoegen via programma wijzigen<br />";
					}
				}
			}
		}
		
		public function deleteProgram($id) {
			global $wpdb;
			$sql = "DELETE FROM ".RTVL_Programma_Table." WHERE id='".$id."';";
			$wpdb->query($sql);
			$this->initializeDB();
			return true;
		}
		
		public function editProgram($id,$data) {
			global $wpdb;
			$updateInfo = "";
			foreach($data as $key => $value) {
				$updateInfo .= $key."='".$value."',";
			}
			$updateInfo = substr($updateInfo,0,-1);
			$sql = "UPDATE ".RTVL_Programma_Table." SET ".$updateInfo." WHERE id='".$id."';";
			$wpdb->query($sql);
			$this->uploadPicture($id,$data);
			$this->initializeDB();
			return true;
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

		public function getProgramByName($name) {
			foreach($this->programmas as $program) {
				if($program->getFeature("naam") == $name) {
					return $program;
				}
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

		public function getProgramList() {
			return $this->programmas;
		}
		
		public function getProgProps() {
			return $this->progProps;
		}
		
		public function getProgFeats() {
			return $this->progFeats;
		}
		
		public function displayDayProgrTable($day) {
			$resultString = '<table id="top" class="programTable"><tr><td class="times">Tijd:</td><td class="days">Programma</td></tr>';
			$dayProg = $this->programmering[$day];
			$startTime = "00";
			foreach($dayProg as $hour => $programma) {
				$HHs = $this->computeHH($hour);
				if($dayProg[$HHs["hNext"]]->getId() != $programma->getId()) {
					$endTime = $HHs["hhNext"];
					$resultString .= '<tr><td class="times">'.$startTime.':00-'.$endTime.':00</td><td><a href="index.php?page_id='.get_query_var('page_id').'#'.$programma->getId().'">'.$programma->getFeature("naam").'</a></td></tr>';
					$startTime = $endTime;
				}
			}
			$resultString .= '</table>';
			return $resultString;
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
		
		public function displayDayPrograms($day) {
			$resultString = "";
			$dayProg = $this->programmering[$day];
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
			return $resultString;
		}
		
		public function displayPrograms() {
			$resultString = '<p id="top"></p>';
			foreach($this->programmas as $id => $programma) {
				$resultString .= '<p style="margin-top:10px" id="'.$programma->getId().'"><strong>'.$programma->getFeature("naam").'</strong> | <a href="index.php?page_id='.get_query_var('page_id').'#top">Terug naar top van pagina</a><br />'.$programma->getFeature("omschrijving").'</p>';
				$resultString .= '<p>Presentator: '.$programma->getFeature("presentatie").'<br />';
				if($programma->getFeature("techniek") != "") { $resultString .= 'Techniek: '.$programma->getFeature("techniek").'<br />'; }
				if($programma->getFeature("redactie") != "") { $resultString .= 'Redactie: '.$programma->getFeature("redactie").'<br />'; }
				$resultString .= '</p>';
			}
			return $resultString;
		}
		
		public function displayProgrTable() {
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
					$returnString .= '<td class="programName '.$day.' hour-'.$i.'">'.$this->programmering[$day][$i]->getFeature("naam").'</td>';
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
						if($this->programmering[$day][$end]->getId() != $this->programmering[$day][$end+1]->getId()) {
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