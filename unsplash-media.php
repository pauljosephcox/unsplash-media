<?php

/**
 *
 * @wordpress-plugin
 * Plugin Name: Unsplash Media
 * Description: Integrate Unsplash into the Wordpress Media Library
 * Author: Paul Joseph Cox
 * Version: 1.0
 * Author URI: http://pauljosephcox.com/
 */


if (!defined('ABSPATH')) { exit; }

/*==========  Activation Hook  ==========*/
register_activation_hook( __FILE__, array( 'Unsplash_Media', 'install' ) );


/**
 * Main Unsplash_Media Class
 *
 * @class Unsplash_Media
 * @version 0.1
 */
class Unsplash_Media {

	public $errors = false;
	public $notices = false;
	public $slug = 'unsplash-media';

	function __construct() {

		$this->path = plugin_dir_path(__FILE__);
		$this->folder = basename($this->path);
		$this->dir = plugin_dir_url(__FILE__);
		$this->version = '1.0';

		$this->errors = false;
		$this->notice = false;

		$this->api = 'https://api.unsplash.com';

		// Actions
		// add_action('init', array($this, 'setup'), 10, 0);
		// add_action('wp_enqueue_scripts', array($this, 'scripts'));
		// add_action('wp_loaded', array($this , 'forms'));
		add_action('parse_request', array($this , 'custom_url_paths'));
		add_action('admin_menu', array($this, 'register_options_page'));


		// Notices (add these when you need to show the notice)
		// add_action( 'admin_notices', array($this, 'admin_success'));
		// add_action( 'admin_notices', array($this, 'admin_error'));

	}

	// ------------------------------------
	// Search Photo
	// ------------------------------------

   /**
    * Search
    * -------------------------------------
    *
    * @param $vars $_GET vars
    * @param query	Search terms.
    * @param category	Category ID(‘s) to filter search. If multiple, comma-separated. (deprecated)
    * @param orientation	Filter search results by photo orientation. Valid values are landscape, portrait, and squarish.
    * @param page	Page number to retrieve. (Optional; default: 1)
    * @param per_page	Number of items per page. (Optional; default: 10)
    * @return JSON
    *
    * -------------------------------------
    **/

	public function get($path, $vars){

		$args = array();
		$args['headers'] = array( 'Authorization' => 'Client-ID ' . get_option('unsplash_media_application_id'));

		$url = $this->api . $path;

		$url .= "?query=".$vars['query'];
		if(!empty($vars['per_page'])) $url .= '&per_page='.$vars['per_page'];
		if(!empty($vars['page'])) $url .= '&page='.$vars['page'];


		$response = wp_remote_get($url, $args);
		$photos = json_decode($response['body']);


		foreach($photos as $photo){

			// echo "<img src=\"". $photo->urls->thumb."\"><br>";
			echo "<img src=\"{$photo->urls->thumb}\">";

		}
		die;

	}



   /**
    * Scripts
    * ---------------------------------------------
    * @return null
    * ---------------------------------------------
    **/

	public function scripts() {

		// wp_enqueue_script('jquery.validate', '//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.11.1/jquery.validate.min.js', array('jquery'), $this->version, true);

	}

   /**
    * Custom URL Paths
    * ---------------------------------------------
    * @param  $wp | Object
    * @return false
    * ---------------------------------------------
    **/

	public function custom_url_paths($wp) {

		$pagename = (isset($wp->query_vars['pagename'])) ? $wp->query_vars['pagename'] : $wp->request;

		switch ($pagename) {

			case 'api/unsplash/search':
				$this->get('/photos/search',$_GET);
				break;

			default:
				break;

		}

	}

   /**
    * Register Options Page
    * ---------------------------------------------
    * @return false
    * ---------------------------------------------
    **/

	public function register_options_page() {

		// main page
		add_options_page('Unsplash Media', 'Unsplash Media', 'manage_options', 'unsplash_media_options', array($this, 'include_options'));
		add_action('admin_init', array($this, 'plugin_options'));

	}


   /**
    * Include Options Page
    * ---------------------------------------------
    * @return false
    * ---------------------------------------------
    **/

	public function include_options() { require('templates/options.php'); }


   /**
    * Plugin Options
    * ---------------------------------------------
    * @return false
    * ---------------------------------------------
    **/

	public function plugin_options() {

		$options = array(
			'unsplash_media_application_id'
		);

		foreach ($options as $option) {
			register_setting('unsplash_media_options', $option);
		}

	}


	/**
	 * Outputs a WordPress error notice
	 *
	 * Push your error to $this->errors then show with:
	 * add_action( 'admin_notices', array($this, 'admin_error'));
	 */
	public function admin_error() {

		if(!$this->errors) return;

		foreach($this->errors as $error) :

	?>

		<div class="error settings-error notice is-dismissible">

			<p><strong><?php print $error ?></strong></p>
			<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>

		</div>

	<?php

		endforeach;

	}

	/**
	 * Outputs a WordPress notice
	 *
	 * Push your error to $this->notices then show with:
	 * add_action( 'admin_notices', array($this, 'admin_success'));
	 */
	public function admin_success() {

		if(!$this->notices) return;

		foreach($this->notices as $notice) :

	?>

		<div class="updated settings-error notice is-dismissible">

			<p><strong><?php print $notice ?></strong></p>
			<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>

		</div>

	<?php

		endforeach;

	}





   /**
    * Template
    * ---------------------------------------------
    * @param $filename | String | name of the template
    * @return false
    * ---------------------------------------------
    **/
	public function template($filename) {

		// check theme
		$theme = get_template_directory() . '/'.$this->slug.'/' . $filename;

		if (file_exists($theme)) {
			$path = $theme;
		} else {
			$path = $this->path . 'templates/' . $filename;
		}
		return $path;

	}


   /**
    * Template Include
    * ---------------------------------------------
    * @param $template | String   | name of the template
    * @param $data     | Anything | Data to pass to a template
    * @param $name     | String   | Data value name
    * @return false
    * ---------------------------------------------
    **/

	public function template_include($template,$data = null,$name = null){

		if(isset($name)){ ${$name} = $data; }
		$path = $this->template($template);
		include($path);
	}

   /**
    * Redirect
    * ---------------------------------------------
    * @param $path | String/Int | url of post id
    * @return false
    * ---------------------------------------------
    **/

	public function redirect($path) {

		if(is_numeric($path)){ $path = get_permalink($path); }
		wp_safe_redirect( $path );
	  	exit();

	}


   /**
    * Output JSON
    * ---------------------------------------------
    * @param $array    | Array/Object | Data to output
    * @return false
    * ---------------------------------------------
    **/

	public function output_json($array) {

		header('Content-type: application/json');
		echo json_encode($array);
		exit();

	}

}


/**
 * @var class Unsplash_Media $unsplash_media
 */

$unsplash_media = new Unsplash_Media();




