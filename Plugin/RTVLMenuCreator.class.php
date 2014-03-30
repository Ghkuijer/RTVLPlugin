<?php

class RTVLMenuCreator {

	private $programmering;
	private $programmas;
	
	function __construct($progr) {
		$this->programmering = $progr;
		$this->programmas = $this->programmering->getProgrammas();
	}
	
	public function createTop() {
		if (!current_user_can('rtv_manage_programs'))
		{
		  wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		
		echo '<div class="wrap">';
		
		// get settings
		$dbInstalled = get_option(RTVL_Plugin_DB);
		$scheduleMade = get_option(RTVL_Plugin_SC);
		$badFormatDays = $this->programmering->getZeros();
		$nrDays = count($badFormatDays);
		$nrRowsMissing = 0;
		foreach($badFormatDays as $dag) {
			$nrRowsMissing += count($dag);
		}
		
		?>
		
		
			<h2>RTVL Plugin Status</h2>
			<div style="border: 1px solid;">
				<table>
					<tr>	<td>Database status: 					</td><td><?php echo $dbInstalled; ?></td>	</tr>
					<tr>	<td>Programmering ingevoerd: 			</td><td><?php echo $scheduleMade; ?></td>	</tr>
					<tr>	<td>Aantal dagen waarop invoer mist:	</td><td><?php echo $nrDays; ?></td>		</tr>
					<tr>	<td>Aantal missende rijen:				</td><td><?php echo $nrRowsMissing; ?></td>	</tr>
				</table>
			</div>
		</div>
		
		<?php
				
		
	}
	
	public function createProgramManager() {
		?>
		<div class="wrap"><h2>Beheer programma's</h2>
		<?php
		if (!current_user_can('rtv_manage_programs'))
		{
		  wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		
		$message = "";
		$editTable = "";
		if($_GET['programAdded'] == 'true' && !empty($_POST)) {
			foreach($_POST as $key => $value) {
				$data[$key] = $value;
			}
			if($this->programmering->addProgram($data)) {
				$message .= "Programma succesvol toegevoegd.<br />";
				$this->programmas = $this->programmering->getProgrammas();
			} else {
				$message .= "Er is een probleem opgetreden tijdens het invoeren van het programma.<br />";
			}
		}
		
		if($_GET['programEdit'] == 'true' && !empty($_POST)) {
			foreach($_POST as $key => $value) {
				$data[$key] = $value;
			}
			if($this->programmering->editProgram($_GET['editId'],$data)) {
				$message .= "Programma succesvol bewerkt<br />";
				$this->programmas = $this->programmering->getProgrammas();
			} else {
				$message .= "Er is een fout opgetreden tijdens het bewerken van het programma.<br />";
			}
		}
		
		if(!empty($_GET['delete'])) {
			if($this->programmering->deleteProgram($_GET['delete'])) {
				$message .= "Programma succesvol verwijderd.<br />";
				$this->programmas = $this->programmering->getProgrammas();
			} else {
				$message .= "Er is een probleem opgetreden tijdens het verwijderen van het programma.<br />";
			}
		}
		
		if(!empty($_GET['edit'])) {
			$editTable = $this->getEditTable($_GET['edit']);			
		}
		
		$programAmount = count($this->programmas);
		$progTable = "";
		if($programAmount > 0) {
			$message .= $programAmount." programma's ingevoerd.<br />";
			$progTable .= "<table border='1px solid'><tr><td border='1px solid'><strong>ID:</strong></td>";
			foreach($this->programmering->getProgProps() as $key => $value) {
				$progTable .= "<td border='1px solid'><strong>".$value.":</strong></td>";
			}
			$progTable .= "</tr>";
			foreach($this->programmas as $id => $program) {
				$progTable .= "<tr border='1px solid'><td border='1px solid'>".$id."</td>";
				foreach($this->programmering->getProgProps() as $key => $value) {
					$progTable .= "<td id='".$id."' class='".$value."' border='1px solid'>".$program->getFeature($value)."</td>";
				}
				$progTable .= "<td border='1px solid'><button type='button' onclick='editRTVLProg(".$id.")'>edit</button></td><td border='1px solid'><button type='button' onclick='deleteRTVLProg(".$id.")'>delete</button></td></tr>";
			}
			$progTable .= "</table>";
		} else {
			$message .= "Nog geen programma's ingevoerd, voer eerst programma's in.<br />";
		}
		
		?>
		<hr />
		<?php 
			if(!empty($message)) 
				echo $message."<hr />"; 
			if(!empty($editTable))
				echo $editTable;
			if(!empty($progTable))		
				echo $progTable; 
		?>
			<hr />
			<form enctype="multipart/form-data" action="<?php echo menu_page_url( "RTVL_Manage_Programs", false ); ?>&programAdded=true" method="POST">
			<table>
				<tr><td><h3>Nieuw programma invoeren:</h3></td></tr>
				<?php
					foreach($this->programmering->getProgProps() as $key => $value) {
						$programmaFeats = $this->programmering->getProgFeats();
						$progFeat = explode(" ",$programmaFeats[$key]);
						$propType = "";
						switch($progFeat[0]) {
							case "varchar(225)":
								$propType = '<input type="text" name="'.$value.'" />';
								break;
							case "text":
								$propType = '<textarea name="'.$value.'"></textarea>';
								break;
							case "tinyint(1)":
								$propType = 'On:<input type="radio" name="'.$value.'" value="1" />Off:<input type="radio" name="'.$value.'" value="0" checked />';
								break;
						}
						echo "<tr><td>".$value.":</td><td>".$propType."</td></tr>"; 
					}
					echo '<tr><td>image:</td><td><input name="file" id="file" type="file" /></td></tr>';
				?>
				<tr><td></td><td><input type="submit" value="Programma Toevoegen" /></td></tr>
			</table>
			</form>
			<hr />
		</div>
		<?php
	}
	
	public function createProgrammeringManager() {
		?>
		<div class="wrap"><h2>Beheer programmering</h2>
		<?php
		if (!current_user_can('rtv_manage_programs'))
		{
		  wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		if($_GET['programmeringChange'] == 'true') {
			$changesArray = array();
			foreach($this->programmering->getHoursArray() as $keyHour => $hour) {
				$changesArray[$hour] = array();
				foreach($this->programmering->getDayArray() as $keyDay => $day) {
					$programAt = $this->programmering->getProgramAt($day,$hour);
					if($programAt == "undefined" || $programAt == "" || empty($programAt)) {
						$id = 0;
					} else {
						$id = $programAt->getId();
					}
					if($id != $_POST[$day."-".$hour]) {
						$changesArray[$hour][$day] = $_POST[$day."-".$hour];
					}
				}
			}
			if($this->programmering->changeProgrammering($changesArray)) {
				echo "Programmering succesvol gewijzigd.";
			}
		}
		
		
		echo '<form action="'.menu_page_url( "RTVL_Manage_Programmering", false ).'&programmeringChange=true" method="post"><table>';
		echo '<tr><td>Uren\dagen</td>';
		foreach($this->programmering->getDayArray() as $key => $day) {
			echo '<td><strong>'.$day.'</strong></td>';
		}
		echo '</tr>';
		
		$programSelectString = '<option value="0">empty</option>';
		if(!empty($this->programmas)) {
			foreach($this->programmas as $id => $program) {
				$programSelectString .= '<option value="'.$id.'">'.$program->getFeature('naam').'</option>';
			}
		}
			
		foreach($this->programmering->getHoursArray() as $keyHour => $hour) {
			echo '<tr><td><strong>'.$hour.'</strong></td>';
			foreach($this->programmering->getDayArray() as $keyDay => $day) {
				$program = $this->programmering->getProgramAt($day,$hour);
				if($program == "undefined" || empty($program)) {
					$id = "0";
					$programName = "empty";
				} else {
					$id = $program->getId();
					$programName = $program->getFeature("naam");
				}
				$programOptionsArray = explode('<option value="'.$id.'">'.$programName.'</option>',$programSelectString);
				$programOptionsString = $programOptionsArray[0].'<option value="'.$id.'" SELECTED>'.$programName.'</option>'.$programOptionsArray[1];
				echo '<td><select name="'.$day.'-'.$hour.'">'.$programOptionsString.'</select></td>';
			}
			echo '</tr>';
		}
		echo '<tr><td><input type="submit" value="Opslaan" /></td></tr></table></form>';
		
		echo "</div>";
	}
	
	private function getEditTable($id) {
		$editProgram = $this->programmering->getProgramById($id);
		$editTable .= '<form enctype="multipart/form-data" action="'.menu_page_url( "RTVL_Manage_Programs", false ).'&programEdit=true&editId='.$id.'" method="post"><table>
							<tr><td><h3>Programma bewerken:</h3></td></tr>';
				$programmaFeats = $this->programmering->getProgFeats();
				foreach($this->programmering->getProgProps() as $key => $value) {
					$progFeat = explode(" ",$programmaFeats[$key]);
					$propType = "";
					switch($progFeat[0]) {
						case "varchar(225)":
							$propType = '<input type="text" name="'.$value.'" value="'.$editProgram->getFeature($value).'" />';
							break;
						case "text":
							$propType = '<textarea name="'.$value.'">'.$editProgram->getFeature($value).'</textarea>';
							break;
						case "tinyint(1)":
							$on = "";
							$off = "";
							if($editProgram->getFeature($value) == '1')
								$on = "checked";
							else 
								$off = "checked";
							$propType = 'On:<input type="radio" name="'.$value.'" value="1" '.$on.' />Off:<input type="radio" name="'.$value.'" value="0" '.$off.' />';
							break;
					}
					$editTable .= "<tr><td>".$value.":</td><td>".$propType."</td></tr>"; 
				}
		$editTable .= '<tr><td>image:</td><td><input name="file" type="file" /></td></tr>';
		$editTable .= '<tr><td></td><td><input type="submit" value="Programma Bewerken" /></td></tr>
						</table></form>';
			
		$editTable .= "<hr />";
		return $editTable;
	}

	public function createMenuManager(){
		?>
		<div class="wrap"><h2>Beheer Menu's</h2>
		<?php
		if (!current_user_can('rtv_manage_menu'))
		{
		  wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		$message = "";
		if($_GET['action'] == 'menuAdded' && !empty($_POST)) {
			foreach($_POST as $key => $value) {
				$data[$key] = $value;
			}
			if($this->addMenu($data)) {
				$message .= "Menu succesvol toegevoegd.<br />";
			} else {
				$message .= "Er is een probleem opgetreden tijdens het invoeren van het programma.<br />";
			}
		} else if($_GET['action'] == 'del' && !empty($_GET['id'])) {
			if($this->deleteMenu($_GET['id'])) {
				$message .= "Menu succesvol verwijderd.<br />";
			} else {
				$message .= "Er is een probleem opgetreden tijdens het verwijderen van het menu.<br />";
			}
		}

		echo $message;

		?>
		<form enctype="multipart/form-data" action="<?php echo menu_page_url( "RTVL_Manage_Menus", false ); ?>&action=menuAdded" method="POST">
			<table>
				<tr><td><h3>Nieuw programma invoeren:</h3></td></tr>
				<tr><td>Naam:</td><td><input type="text" name="name" /></td><td>Relevante naam gebruiken s.v.p.!</td></tr>
				<tr><td>Slug:</td><td><input type="text" name="slug" /></td><td>Alleen letters gebruiken! Ook geen spaties!</td></tr>
				<tr><td></td><td><input type="submit" value="Programma Toevoegen" /></td></tr>
			</table>
		</form>
		<hr />
		<h3>Ingevoerde Menu's</h3>
		<table border="1px solid">
			<tr><td border="1px solid">Naam:</td><td border="1px solid">Slug:</td><td></td></tr>
		<?php 
			$menus = $this->getMenus();
			foreach($menus as $id => $menu) {
				echo '<tr><td border="1px solid">'.$menu['name'].'</td><td border="1px solid">'.$menu['slug'].'</td><td border="1px solid"><a href="'.menu_page_url( "RTVL_Manage_Menus", false ).'&action=del&id='.$id.'"><button>delete</button></a></td></tr>';
			
			}		
		?>
		</table>
		<hr />
		<?php
	}

	public function addMenu($data) {
		// enter data in database
		global $wpdb;
		$sql = "INSERT INTO ".RTVL_Menu_Table." (name,slug) VALUES ('".$data['name']."','".$data['slug']."');";
		$wpdb->query($sql);		
		return true;
	}

	public function deleteMenu($id) {
		// delete menu from database
		global $wpdb;
		$sql = "DELETE FROM ".RTVL_Menu_Table." WHERE id='".$id."';";
		$wpdb->query($sql);		
		return true;
	}

	public function getMenus() {
		global $wpdb;
		$menus = array();
		$query = "SELECT id,name,slug FROM ".RTVL_Menu_Table." ORDER BY id ASC";
		$results = $wpdb->get_results($query,"ARRAY_A");
		foreach($results as $result) {
			$menus[$result["id"]] = array( 'name' => $result["name"], 'slug' => $result["slug"] );
		}
		return $menus;
	}

	public function createWiki() {
		?>
		<div class="wrap"><h2>RTV Lansingerland Wiki</h2>
		<iframe src="http://www.rtvlansingerland.nl/RTVLWiki/" width="100%" height="1000px"></iframe>
		</div>
		<?php
	}
	
}

?>