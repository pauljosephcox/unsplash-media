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


/**
 * Main Unsplash_Media Class
 *
 * @class Unsplash_Media
 * @version 0.1
 */
class Unsplash_Media {

	public $slug = 'unsplash-media';

	function __construct() {

		$this->path = plugin_dir_path(__FILE__);
		$this->folder = basename($this->path);
		$this->dir = plugin_dir_url(__FILE__);
		$this->url = '//'.$_SERVER['HTTP_HOST'].'/wp-content/plugins/unsplash-media';
		$this->version = '1.0.2';

		$this->api = 'https://api.unsplash.com';
		$this->application_id = '7fa28f8e758490ca08252023b476bf194658a5cd27c09ebe15af61b7e4db6548';


		// Actions
		add_action('admin_enqueue_scripts', array($this, 'scripts'));
		add_action('parse_request', array($this , 'custom_url_paths'));
		add_action('admin_menu', array($this, 'register_options_page'));

	}

   	/**
   	 * Get Photos
   	 * @param string $path 
   	 * @param array $vars 
   	 * @return JSON
   	 */

	public function get($path, $vars){

		// Set Headers
		$args = array();
		$args['headers'] = array( 'Authorization' => 'Client-ID ' . $this->application_id);
		$args['timeout'] = 10;
		
		// Set API Path
		$url = $this->api . $path;

		// Build Querystring
		$qs = array();
		foreach($vars as $key=>$value) $qs[] = $key.'='.$value;
		if(!empty($qs)) $querystring = implode('&', $qs);
		if(!empty($querystring)) $url .= '?' .$querystring;


		// Make Request
		$response = wp_remote_get($url, $args);
		if(is_wp_error($response)) $this->output_json(array('error'=>"Let's try that again."));
		
		// Output Result
		header('Content-type: application/json');
		echo $response['body'];
		die;


	}

   /**
    * Import Photo
    * ---------------------------------------------
    * @return null
    * ---------------------------------------------
    **/

	public function import($vars){

		if(!wp_verify_nonce( $vars['_wpnonce'], 'unsplash_media')) $this->output_json(array('error'=>'invalid nonce'));

		if(!empty($vars['photo'])){

			// Include Importer
			require_once(ABSPATH . 'wp-admin/includes/media.php');
			require_once(ABSPATH . 'wp-admin/includes/file.php');
			require_once(ABSPATH . 'wp-admin/includes/image.php');

			// Set Size
			$vars['photo'] .= '&w=1800';

			$filename = basename($vars['photo']).'.jpg';
			$filetype = wp_check_filetype( basename( $filename ), null );


			// Save As
			$local_path = download_url($vars['photo']);
			$data = file_get_contents($local_path);

			$wp_upload_dir = wp_upload_dir();

			// Save File to Uploads Folder
			file_put_contents($wp_upload_dir['path'].'/'.$vars['title'].'.jpg', $data);

			// Sideload Image
			$result = media_sideload_image($wp_upload_dir['url'] . '/' . $vars['title'].'.jpg',1,$vars['credit']);
			
			if($result)
				$this->output_json(array('success'=>'imported'));
			else
				$this->output_json(array('error'=>'Error importing'));

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

		wp_enqueue_script('unsplash', $this->url . '/assets/unsplash.js', array('jquery'), $this->version, true);

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

			case 'api/unsplash/photos/search':
				$this->get('/photos/search',$_GET);
				break;

			case 'api/unsplash/import':
				$this->import($_POST);
				break;

			default:
				break;

		}

	}

   /**
    * Forms
    * ---------------------------------------------
    * @return false
    * ---------------------------------------------
    **/

	public function forms() {

		if (!isset($_POST['unsplash_media_actions'])) return;

		if(!wp_verify_nonce( $_POST['_wpnonce'], 'unsplash_media')){ $this->redirect($_POST['_wp_http_referer']); }

		switch ($_POST['unsplash_media_actions']) {

			case 'import':
				$this->import($_POST);
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

		add_media_page('Unplash Media', 'Unsplash Media', 'manage_options', 'unsplash_media_options', function(){ $this->template_include('photos.php'); });

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


// function custom_media_upload_tab_name( $tabs ) {
//     $newtab = array( 'tab_slug' => 'Your Tab Name' );
//     return array_merge( $tabs, $newtab );
// }

// add_filter( 'media_upload_tabs', 'custom_media_upload_tab_name' );

// function custom_media_upload_tab_content() {
//     // Add you content here.
// }
// add_action( 'media_upload_tab_slug', 'custom_media_upload_tab_content' );


