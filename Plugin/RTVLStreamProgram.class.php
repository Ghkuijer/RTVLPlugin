<?php

class RTVLStreamProgram {
	
	private $id;
	private $features;
	private $progProps;
	
	function __construct($programId,$pp){
		$this->id = $programId;
		$this->progProps = $pp;
	
		$query = "SELECT * FROM ".RTVL_Programma_Table." WHERE id='".$programId."'";
		$results = mysql_query($query) or die (mysql_error());
		while($row = mysql_fetch_array($results)) {
			foreach($this->progProps as $prop) {
				$this->features[$prop] = $row[$prop];
			}
		}
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getFeature($feat) {
		return $this->features[$feat];
	}
}

?>