<?php
/**
*@package an_header
*/
/*
Plugin Name: Job Vacancies
Plugin URI: https://www.facebook.com/an.websites
Description: The best Job Manager ever developed
Version: 1.0.0
Author: Akim Nyoni
Author URI: https://www.facebook.com/akim.nyoni.7
License: GPLv2 or later
*/

if (! defined('ABSPATH')){
	die;
}



class TheTest
{
	function __construct() {
		add_action('plugins_loaded', array($this, 'init') );
		add_action( 'init', array($this, 'custom_post_type' ) );
		add_action( 'init', array($this, 'job_category_taxonomy') );
		add_action( 'init', array($this, 'job_type_taxonomy') );
		add_filter('manage_vacancies_posts_columns', array($this, 'job_edit_columns') );
		add_filter('manage_edit-vacancies_sortable_columns', array($this, 'job_date_column_register_sortable'));
		add_action('manage_vacancies_posts_custom_column', array($this, 'job_custom_columns') );
		add_action('admin_init', array($this, 'jobs_admin_init') );
		add_action('save_post', array($this, 'save_job_details') );
		add_action( 'admin_enqueue_scripts', array($this, 'job_enqueue_date_picker') );
		
		

	}

	// load plugin text domain
	function init() {
		load_plugin_textdomain( 'job-list', false, dirname( plugin_basename( __FILE__ ) ) . '/translation' );
	}
	


	function activate(){
		flush_rewrite_rules();
	}

	function deactivate(){
		$this->custom_post_type();
		flush_rewrite_rules();
	}

	public function job_category_taxonomy() {
		$labels = array(
			'name' => 'Category',
			'singular_name' => 'Category',
			'search_items' => 'Search Categorys',
			'all_items' => 'All Categorys',
			'parent_item' => 'Parent Category',
			'parent_item_colon' => 'Parent Category:',
			'edit_item' => 'Edit Category',
			'update_item' => 'Update Category',
			'add_new_item' => 'Add New Category',
			'new_item_name' => 'New Category Name',
			'menu_name' => 'Job Category'
			);
		$args = array(
			'labels' => $labels,
			'hierarchical' => true,
			'query_var' => true,
			'rewrite' => true
			);
			register_taxonomy( 'job-category', 'vacancies', $args );
	}


	public function job_type_taxonomy() {
		$labels = array(
			'name' => 'Type',
			'singular_name' => 'Types',
			'search_items' => 'Search Types',
			'all_items' => 'All Types',
			'parent_item' => 'Parent Type',
			'parent_item_colon' => 'Parent Type:',
			'edit_item' => 'Edit Type',
			'update_item' => 'Update Type',
			'add_new_item' => 'Add New Type',
			'new_item_name' => 'New Type Name',
			'menu_name' => 'Job Type'
			);
		$args = array(
			'labels' => $labels,
			'hierarchical' => true,
			'query_var' => true,
			'rewrite' => true
			);
			register_taxonomy( 'type', 'vacancies', $args );
	}


	function custom_post_type(){
		$labels = array(
			'name' => 'Job Manager',
			'singular_name' => 'Job',
			'add_new' => 'Add New',
			'add_new_item' => 'Add New',
			'edit_item' => 'Edit',
			'new_item' => 'New',
			'view_item' => 'View',
			'search_items' => 'Search',
			'not_found' => 'Nothing Found',
			'not_found_in_trash' => 'Nothing found in Trash',
			'parent_item_colon' => '',
            'menu_name' => 'Job Manager'
		);

		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => true,
			'has_archive' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array('title', 'editor', 'thumbnail'),
		);
		register_post_type('vacancies', $args);
	}

	//adding custom columns in admin custom post type

	function job_edit_columns($job_columns){
	$job_columns = array(
		"cb" => "<input type=\"checkbox\" />",
		"title" => "Title",
		"job-end-date" => "End Date",
		"job_type" => "Job Type",
		"job_category" => "Job Category",
		"job_location" => "Location",
		"job_city" => "City",
	);
	return $job_columns;
	}
	
	function job_date_column_register_sortable( $job_columns ) {
		$job_columns['job-end-date'] = 'job-end-date';
		$job_columns['job_type'] = 'job_type';
		return $job_columns;
	}
	//printing values in admin panel for job manager
	function job_custom_columns($job_column){
		global $post;
		$custom = get_post_custom();
		switch ($job_column) {
			case "job-end-date":
				echo date('F j, Y', ($custom["job-end-date"][0]));
			break;
			case "job_type":
				echo get_the_term_list( get_the_ID(), 'type');
			break;
			case "job_category":
				echo get_the_term_list( get_the_ID(), 'job-category');
			break;
			case "job_location":
				echo $custom["job_location"][0];
			break;
			case "job_city":
				echo $custom["job_city"][0];
			break;
			}
		return $job_column;
		}

		
	function jobs_admin_init(){
		add_meta_box('job_meta', 'Job Details', array($this, 'job_details_meta'), 'vacancies', 'normal', 'high');
	}

	function get_job_field($job_field) {
		global $post;
		$custom = get_post_custom($post->ID);
		if (isset($custom[$job_field])) {
			return $custom[$job_field][0];
		}
		
	}

	function save_job_details(){
	global $post;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;
		if ( get_post_type($post) != 'vacancies')
			return;
			if(isset($_POST["job-end-date"])) {
				update_post_meta($post->ID, "job-end-date", strtotime($_POST["job-end-date"]));
		}
	$this->save_job_field("job_location");
	$this->save_job_field("job_city");
	}

	function save_job_field($job_field) {
		global $post;
		if(isset($_POST[$job_field])) {
			update_post_meta($post->ID, $job_field, $_POST[$job_field]);
		}
	}

	// set date format for date input fields
	function job_input_dateformat() {
		$dateformat_input = get_option('date_format');
		if ($dateformat_input == 'j F Y' || $dateformat_input == 'd/m/Y' || $dateformat_input == 'd-m-Y') {
			$dateformat_input = 'd-m-Y';
		} else {
			$dateformat_input = 'Y-m-d';
		}
		return $dateformat_input;
	}

	// set local timestamp
	function job_local_timestamp() {
		$current_date = current_datetime();
		$var = $current_date->setTime( 0, 0, 0, 0);
		$today = $var->getTimestamp()+$var->getOffset();
		return $today;
	}

	// set utc timezone
	function job_utc_timezone() {
		$time_zone = new DateTimeZone('UTC');
		return $time_zone;
	}

	// set date format for datepicker
	function job_datepicker_dateformat() {
		$dateformat = get_option('date_format');
		if ($dateformat == 'j F Y' || $dateformat == 'd/m/Y' || $dateformat == 'd-m-Y') {
			$dateformat = 'dd-mm-yy';
		} else {
			$dateformat = 'yy-mm-dd';
		}
		return $dateformat;
	}

	// enqueue datepicker script
	function job_enqueue_date_picker() {
		// set global
		global $wp_locale;
		global $post_type;
		// end set global
		if( 'vacancies' != $post_type )
		return;
		wp_enqueue_script( 'job_datepicker_script', plugins_url('/js/job-datepicker.js',__FILE__), array('jquery', 'jquery-ui-datepicker') );
		wp_enqueue_style( 'job_datepicker_style', plugins_url('/css/job-datepicker.css',__FILE__) );
		// datepicker args
		$job_datepicker_args = array(
			'dateFormat' => $this->job_datepicker_dateformat()
		);
		// localize script with data for datepicker
		wp_localize_script( 'job_datepicker_script', 'objectL10n', $job_datepicker_args );
	}

	function job_details_meta() { 
		$end_date = $this->get_job_field('job-end-date');
		$date_format = $this->job_input_dateformat();
		$local_timestamp = $this->job_local_timestamp();
		$utc_time_zone = $this->job_utc_timezone();

		// get date if saved, else set it to today
		$end_date = !empty( $end_date ) ? $end_date : $local_timestamp;
		
		?>
		<p><label>End Date: </label><input class="widefat" type="text" id="job-end-date" name="job-end-date" placeholder="<?php esc_attr_e( 'Use datepicker', 'job-list' ); ?>" value="<?php echo wp_date( $date_format, esc_attr( $end_date ), $utc_time_zone ) ?>" required /></p>
		<p><label>Location: </label><input class="widefat" type="text" size="70" name="job_location" value="<?php echo $this->get_job_field("job_location"); ?>" required /></p>
		<p><label>City: </label><input class="widefat" type="text" size="50" name="job_city" value="<?php echo $this->get_job_field("job_city"); ?>" /></p>
	<?php }
	
	

}


include 'the_shortcodes.php';


if (class_exists('TheTest')){
	$headerPlugin = new TheTest();
}

// activation
register_activation_hook(__FILE__, array($headerPlugin, 'activate' ));

// deactivation
register_deactivation_hook(__FILE__, array($headerPlugin, 'deactivate' ));

