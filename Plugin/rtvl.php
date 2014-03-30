<?php

// errorcodes:
// 0: everything oke
// 1: method missing
// 2: no program found

class json_api_rtvl_controller {

  public function program() {
	global $json_api;
	global $RTVL_Programmering;
	$method = $json_api->query->method;
	switch ($method) 
	{
		case "id":
			$id = $json_api->query->id;
			return $this->json_program($RTVL_Programmering->getProgramById($id));
		case "name":
			$name = $json_api->query->id;
			return $this->json_program($RTVL_Programmering->getProgramByName($name));
		default:
			return array(
				"errorcode" => 1,
				"message" => "No method found!"
				);
	}	
  }
  
  public function json_program($program) {
	if($program != "" && !empty($program)) {
		return array(
			"errorcode" => 0,
			"message" => "Program with id: ".$program->getId()." found.",
			"id" => $program->getId(),
			"name" => $program->getFeature("naam"),
			"description" => $program->getFeature("omschrijving"),
			"pres" => $program->getFeature("presentatie"),
			"tech" => $program->getFeature("techniek"),
			"red" => $program->getFeature("redactie"),
			"cam" => $program->getFeature("cam")			
		);
	}
	
  }
  
}

?>