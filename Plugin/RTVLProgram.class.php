<?php

class RTVLProgram {
	
	private $id;
	private $features;
	private $progProps;
	
	function __construct($programId,$progProps){
		global $wpdb;
		$this->id = $programId;
		$this->progProps = $progProps;
	
		$query = "SELECT * FROM ".RTVL_Programma_Table." WHERE id='".$programId."'";
		$results = $wpdb->get_results($query,"ARRAY_A");
		foreach($results as $result) {
			foreach($this->progProps as $key => $value) {
				$this->features[$value] = $result[$value];
			}
		}
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getFeature($feat) {
		return $this->features[$feat];
	}
	
	public function setNaam($newNaam) {
		$query = "UPDATE ".RTVL_Programma_Table." SET naam='".$newNaam."' WHERE id='".$this->id."'";
		mysql_query($query) or die(mysql_error());
		$this->naam = $newNaam;
	}
	
	public function setPresentatie($newPres) {
		$query = "UPDATE ".RTVL_Programma_Table." SET presentatie='".$newPres."' WHERE id='".$this->id."'";
		mysql_query($query) or die(mysql_error());
		$this->presentatie = $newPres;
	}
	
	public function setTechniek($newTech) {
		$query = "UPDATE ".RTVL_Programma_Table." SET techniek='".$newTech."' WHERE id='".$this->id."'";
		mysql_query($query) or die(mysql_error());
		$this->techniek = $newTech;
	}
	
	public function setRedactie($newRed) {
		$query = "UPDATE ".RTVL_Programma_Table." SET redactie='".$newRed."' WHERE id='".$this->id."'";
		mysql_query($query) or die(mysql_error());
		$this->redactie = $newRed;
	}
	
	public function setOmschrijving($newOm) {
		$query = "UPDATE ".RTVL_Programma_Table." SET omschrijving='".$newOm."' WHERE id='".$this->id."'";
		mysql_query($query) or die(mysql_error());
		$this->omschrijving = $newOm;
	}
	
	public function setWebcam($newCam) {
		$query = "UPDATE ".RTVL_Programma_Table." SET cam='".$newCam."' WHERE id='".$this->id."'";
		mysql_query($query) or die(mysql_error());
		$this->Cam = $newCam;
	}
}

?>