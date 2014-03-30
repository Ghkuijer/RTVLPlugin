<?php
// define option names
define(PREFIX,"wp3_");
define(RTVL_Plugin_DB,"RTVL_Plugin_DB");
define(RTVL_Plugin_SC,"RTVL_Plugin_SC");
define(RTVL_Programma_Table,PREFIX . "rtv_programmas");
define(RTVL_Programmering_Table,PREFIX . "rtv_programmering");
define(RTVL_Menu_Table,PREFIX . "rtv_menus");

// define MYSQL connect doc
include('RTVLStreamConnect.php');
include('RTVLStreamProgram.class.php');
include('RTVLStreamProgrammering.class.php');

class RTVLStreamController {
	private $dagen = array("Mon","Tue","Wed","Thu","Fri","Sat","Sun");
	private $uren = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23);
	private $progProps = array("naam","omschrijving","presentatie","techniek","redactie","cam");
	private $programmering;

	function __construct() {
		$this->programmering = new RTVLStreamProgrammering($this->dagen,$this->uren,$this->progProps);
		$this->programmering->initializeDB();
		date_default_timezone_set("Europe/Amsterdam");
	}

	public function getProgramPlaying() {
		return $this->programmering->getProgramAt(date('D'),date('G'));
	}

	public function getNextProgram() {
		return $this->programmering->getProgramAt(date('D',strtotime('+1 hour')),date('G',strtotime('+1 hour')));
	}
}
?>