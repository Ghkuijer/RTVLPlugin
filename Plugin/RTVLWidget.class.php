<?php

/**
 * Adds RTVL_Widget widget.
 */

global $wpdb;
define(RTVL_Programma_Table,$wpdb->prefix . "rtv_programmas");
define(RTVL_Programmering_Table,$wpdb->prefix . "rtv_programmering");
 
class RTVLWidget extends WP_Widget {
	
	private $programmering;
	private $programmas;
	private $dagen = array("Mon","Tue","Wed","Thu","Fri","Sat","Sun");
	private $uren = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23);
	private $progProps = array("naam","omschrijving","presentatie","techniek","redactie","cam");
	
	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		$this->initializeDB();
		parent::__construct(
	 		'RTVLWidget', // Base ID
			'RTVLWidget', // Name
			array( 'description' => __( 'Widget showing which program is playing and which is next.', 'text_domain' ), ) // Args
		);
		wp_register_style( "RTVLPluginStyle", plugins_url('RTVLStyle.css', __FILE__ ) );
		wp_enqueue_style( "RTVLPluginStyle" );
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

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		extract( $instance );
		$pageId = apply_filters( 'page_id', $instance['pageID'] );
		
		$blogtime = current_time('mysql'); 
		list( $today_year, $today_month, $today_day, $hour, $minute, $second ) = split( '([^0-9])', $blogtime );
		$day = date("D", mktime(0, 0, 0, $today_month, $today_day, $today_year));
		$HH = $hour;
		if(substr($HH,0,1) == 0) {
			$H = substr($HH,1,1);
		} else {
			$H = $HH;
		}
		$progNu = $this->programmeringTable[$day][$H];
		if($progNu == "undefined" || empty($progNu) || $progNu == "") {
			$id = "0";
			$name = "Non-stop";
		} else {
			$id = $progNu->getId();
			$name = $progNu->getFeature("naam");
		}
		$uploads = wp_upload_dir();
		$resultString = $before_widget;
		if($title != "" && !empty($title)) 
			$resultString .= $before_title.' '.$title.' '.$after_title;
		$source = $uploads['url'].'/programmas/'.$id.'.jpg';
		if(file_exists($source)) 
			$src = $source;
		else
			$src = $uploads['url'].'/programmas/default.jpg';
		wp_register_script('openLivestream',plugins_url('RTVLJS.js', __FILE__ ));
		$popupVars = array(
			'url' => $livestreamUrl,
			'width' => $livestreamWidth,
			'height' => $livestreamHeight,
			'xPos' => $livestreamX,
			'yPos' => $livestreamY );
		wp_localize_script('openLivestream','popupVars',$popupVars);
		wp_enqueue_script('openLivestream');
		$resultString .= '<div id="nu-op-FM"><p>Met nu: <a href="'.site_url('/').'/programmering/#'.$id.'">'.$name.'</a></p><img src="'.$src.'"><a class="redbutton" href="#" onclick="openLivestream()"><center>Luister nu</center></a>';
		/* if ($H == end($this->uren)) {
			$H = 0;
			$key = array_search($day,$this->dagen);
			$key++;
			if($key == count($this->dagen)) {
				$key = 0;
			}
			$day = $this->dagen[$key];
		} else {
			$H++;
		}
		$progVolgend = $this->programmeringTable[$day][$H];
		if($progVolgend == "undefined" || empty($progVolgend) || $progVolgend == "") {
			$id = "0";
			$name = "Non-stop";
		} else {
			$id = $progVolgend->getId();
			$name = $progVolgend->getFeature("naam");
		}
		$resultString .= 'Het volgende uur:<a href="'.site_url('/').'?page_id='.$pageId.'#'.$id.'">'.$name.'</a>';
		*/
		$resultString .= '</div>';
		echo $resultString;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['livestreamUrl'] = strip_tags( $new_instance['livestreamUrl'] );
		$instance['livestreamHeight'] = strip_tags( $new_instance['livestreamHeight'] );
		$instance['livestreamWidth'] = strip_tags( $new_instance['livestreamWidth'] );
		$instance['livestreamX'] = strip_tags( $new_instance['livestreamX'] );
		$instance['livestreamY'] = strip_tags( $new_instance['livestreamY'] );

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form($instance) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		if ( isset( $instance[ 'livestreamUrl' ] ) ) {
			$livestreamUrl = $instance[ 'livestreamUrl' ];
		}
		if ( isset( $instance[ 'livestreamHeight' ] ) ) {
			$livestreamHeight = $instance[ 'livestreamHeight' ];
		}
		if ( isset( $instance[ 'livestreamWidth' ] ) ) {
			$livestreamWidth = $instance[ 'livestreamWidth' ];
		}
		if ( isset( $instance[ 'livestreamX' ] ) ) {
			$livestreamX = $instance[ 'livestreamX' ];
		}
		if ( isset( $instance[ 'livestreamY' ] ) ) {
			$livestreamY = $instance[ 'livestreamY' ];
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Titel van widget:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'livestreamUrl' ); ?>"><?php _e( 'Url van popup:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'livestreamUrl' ); ?>" name="<?php echo $this->get_field_name( 'livestreamUrl' ); ?>" type="text" value="<?php echo esc_attr( $livestreamUrl ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'livestreamHeight' ); ?>"><?php _e( 'Hoogte van popup:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'livestreamHeight' ); ?>" name="<?php echo $this->get_field_name( 'livestreamHeight' ); ?>" type="text" value="<?php echo esc_attr( $livestreamHeight ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'livestreamWidth' ); ?>"><?php _e( 'Breedte van popup:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'livestreamWidth' ); ?>" name="<?php echo $this->get_field_name( 'livestreamWidth' ); ?>" type="text" value="<?php echo esc_attr( $livestreamWidth ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'livestreamX' ); ?>"><?php _e( 'X positie van popup:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'livestreamX' ); ?>" name="<?php echo $this->get_field_name( 'livestreamX' ); ?>" type="text" value="<?php echo esc_attr( $livestreamX ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'livestreamY' ); ?>"><?php _e( 'Y positie van popup:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'livestreamY' ); ?>" name="<?php echo $this->get_field_name( 'livestreamY' ); ?>" type="text" value="<?php echo esc_attr( $livestreamY ); ?>" />
		</p>
		<?php 
	}

}
?>