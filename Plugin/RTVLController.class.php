<?php

// include all files needed
include("RTVLMenuCreator.class.php");
include("RTVLProgrammering.class.php");
include("RTVLProgram.class.php");
include("RTVLWidget.class.php");

global $wpdb;

// define option names
define(RTVL_Plugin_DB,"RTVL_Plugin_DB");
define(RTVL_Plugin_SC,"RTVL_Plugin_SC");
define(RTVL_Programma_Table,$wpdb->prefix . "rtv_programmas");
define(RTVL_Programmering_Table,$wpdb->prefix . "rtv_programmering");
define(RTVL_Menu_Table,$wpdb->prefix . "rtv_menus");

class RTVLController {

	private $programmering;
	private $widget;
	private $dagen = array("Mon","Tue","Wed","Thu","Fri","Sat","Sun");
	private $dutchDays = array("Maandag","Dinsdag","Woensdag","Donderdag","Vrijdag","Zaterdag","Zondag");
	private $uren = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23);
	private $progProps = array("naam","omschrijving","presentatie","techniek","redactie","cam");
	private $progFeats = array("varchar(225) NOT NULL","text NOT NULL","varchar(225) NOT NULL","varchar(225)","varchar(225)","tinyint(1) NOT NULL");

	function __construct() {
		wp_register_script('RTVL_Script',plugins_url() . '/RTVLPlugin/RTVLJS.js',array(),'1.0');
		wp_enqueue_script('RTVL_Script');
		
		// make new objects that can be used
		$this->programmering = new RTVLProgrammering($this->dagen,$this->uren,$this->progProps,$this->progFeats, $this->dutchDays);
		$this->menuCreator = new RTVLMenuCreator($this->programmering);
		$this->widget = new RTVLWidget();

		global $RTVL_Programmering;
		$RTVL_Programmering = $this->programmering;
		
		// register RTVLWidget widget
		add_action( 'widgets_init', create_function( '', 'register_widget( "RTVLWidget" );' ) );
		
		//update settings
		$this->updateSCSetting();
		
		// initialize everything so all data is placed in objects
		$this->programmering->initializeDB();
		
		// add shortcode to display programmering info
		add_shortcode( 'rtv_programmering' , array(&$this, 'programmeringToString') );
		add_shortcode( 'rtv_programs' , array(&$this, 'programsToString') );

		// initialize admin top-menu
		add_action( 'admin_menu', array(  &$this, 'createRTVLAdminMenu' ) );
	}
	
	public function activatePlugin() {
		// installing database if not yet installed
		$databaseMade = get_option(RTVL_Plugin_DB);
		if($databaseMade != "set") {
			$this->programmering->install_database();
			$this->install_database();
			add_option(RTVL_Plugin_DB,"set");
			add_option(RTVL_Plugin_SC,"undone");
		} else {
			if($this->programmering->goodFormat()) {
				add_option(RTVL_Plugin_SC,"done");
			} else {
				add_option(RTVL_Plugin_SC,"undone");
			}
		}
	}

	public function install_database() {
		global $wpdb;
		$sql = "CREATE TABLE ".RTVL_Programma_Table." (id int(11) NOT NULL AUTO_INCREMENT, name	varchar(225), slug varchar(225) UNIQUE KEY id (id));";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	
	public function updateSCSetting() {
		if(get_option(RTVL_Plugin_SC) == "undone" && $this->programmering->goodFormat()) {
			update_option(RTVL_Plugin_SC,"done");
		} else if (get_option(RTVL_Plugin_SC) == "done" && !($this->programmering->goodFormat())) {
			update_option(RTVL_Plugin_SC,"undone");
		}
	}

	public function add_rtvl_controller($controllers) {
  		$controllers[] = 'rtvl';
 		return $controllers;
	}

	public function set_rtvl_controller_path() {
  		return plugins_url() . '/RTVLPlugin/rtvl.php';
	}
	
	public function createMenu($which) {
		switch ($which) {
			case "PluginTop":
				$this->menuCreator->createTop();
				break;
			case "Programmering":
				break;
			case "TechLogs":
				break;
		}
	}
	
	public function createRTVLAdminMenu() {
		add_menu_page("RTVL Plugin", "RTVL Plugin", "rtv_manage_programs", "RTVL_Plugin_Status", array(  &$this->menuCreator, 'createTop' ));
		add_submenu_page("RTVL_Plugin_Status","Programma's beheren","Programma's beheren","rtv_manage_programs","RTVL_Manage_Programs",array( &$this->menuCreator, 'createProgramManager'));
		add_submenu_page("RTVL_Plugin_Status","Programmering beheren","Programmering beheren","rtv_manage_programs","RTVL_Manage_Programmering",array( &$this->menuCreator, 'createProgrammeringManager'));
		add_submenu_page("RTVL_Plugin_Status","Menu's aanmaken","Menu's aanmaken","rtv_manage_menu","RTVL_Manage_Menus",array( &$this->menuCreator, 'createMenuManager'));
		add_submenu_page("RTVL_Plugin_Status","RTV Wiki","RTV Wiki","rtv_wiki","RTVL_Wiki",array( &$this->menuCreator, 'createWiki'));
	}
	
	public function programmeringToString($atts) {
		extract( shortcode_atts( array(
	      'day' => null
		), $atts ) );
		if ($day !== null) {
			echo $this->programmering->displayDayProgrTable($day);
		} else {
			echo $this->programmering->displayProgrTable();
		}
	}
	
	public function programsToString($atts) {
		extract( shortcode_atts( array(
	      'day' => null
		), $atts ) );
		if ($day !== null) {
			echo $this->programmering->displayDayPrograms($day);
		} else {
			echo $this->programmering->displayPrograms();
		}
	}
	
}

?>