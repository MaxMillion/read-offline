<?php

class Read_Offline {


	public static $options;
	private static $instance;

	public static function get_instance() {

		if ( self::$instance ) {
			return self::$instance;
		}

		self::$instance = new self();
		return self::$instance;
	}

	private function __construct() {

		self::$options = get_option( 'Read_Offline_Admin_Settings' );
		if (is_admin()) {
			add_action( 'admin_init', array($this, 'read_offline_update' ));
		}
	}

	public static function query_url($id,$name,$format) {
		//$rules = $GLOBALS['wp_rewrite']->wp_rewrite_rules();
		if ( get_option('permalink_structure')) {
			return sprintf("%s/read-offline/%s/%s.%s",home_url(),$id,$name,$format);
		} else {
			return sprintf("%s/index.php?read_offline_id=%s&read_offline_name=%s&&read_offline_format=%s",home_url(),$id,$name,$format);
		}
	}

	// from http://php.net/manual/en/function.imagecreatefromjpeg.php#110547
	public static function image_create_frome_image($filepath) {
	    $type = exif_imagetype($filepath); // [] if you don't have exif you could use getImageSize()
	    $allowedTypes = array(
	        1,  // [] gif
	        2,  // [] jpg
	        3,  // [] png
	        6   // [] bmp
	    );
	    if (!in_array($type, $allowedTypes)) {
	        return false;
	    }
	    switch ($type) {
	        case 1 :
	            $im = imageCreateFromGif($filepath);
	        break;
	        case 2 :
	            $im = imageCreateFromJpeg($filepath);
	        break;
	        case 3 :
	            $im = imageCreateFromPng($filepath);
	        break;
	        case 6 :
	            $im = imageCreateFromBmp($filepath);
	        break;
	    }
	    return $im;
	}
	// from http://wordpress.stackexchange.com/a/54629/14546
	// 
	public static function get_excerpt_by_id($post_id){
	    $the_post = get_post($post_id); //Gets post ID
	    $the_excerpt = $the_post->post_content; //Gets post_content to be used as a basis for the excerpt
	    $excerpt_length = 35; //Sets excerpt length by word count
	    $the_excerpt = strip_tags(strip_shortcodes($the_excerpt)); //Strips tags and images
	    $words = explode(' ', $the_excerpt, $excerpt_length + 1);

	    if(count($words) > $excerpt_length) :
	        array_pop($words);
	        array_push($words, '…');
	        $the_excerpt = implode(' ', $words);
	    endif;

	    $the_excerpt = '<p>' . $the_excerpt . '</p>';

	    return $the_excerpt;
	}

	public function read_offline_update() {

		$options = get_option( "Read_Offline" );
		$version = (isset($options['version'])) ? $options['version'] : '0';
		$version = 0;
		if ( $version != READOFFLINE_VERSION ) {
			$options['version'] = READOFFLINE_VERSION;

			$this->_remove_tmp_directories();

			update_option( "Read_Offline", $options );
		}
		$this->_create_tmp_directories();
	}

	private function _create_tmp_directories() {
		global $wp_filesystem;
		if( ! $wp_filesystem || ! is_object($wp_filesystem) )
			WP_Filesystem();
		if( ! is_object($wp_filesystem) )
			wp_die('WP_Filesystem Error:' . print_r($wp_filesystem,true));

		$directories = array('cache/read-offline/tmp', 'cache/read-offline/font');
		foreach ($directories as $directory) {
			$path = WP_CONTENT_DIR;
			foreach (explode('/', $directory) as $foldername) {
				$path .= '/' . $foldername;
				if ( !$wp_filesystem->exists($path) ) {
					if ( !$wp_filesystem->mkdir($path, FS_CHMOD_DIR) ){
						return add_action( 'admin_notices', function() use ( $path ){
						    $msg[] = '<div class="error"><p>';
						    $msg[] = '<strong>Read Offline</strong>: ';
						    $msg[] = sprintf( __( 'Unable to create directory "<strong>%s</strong>". Is its parent directory writable by the server?','read-offline' ), $path );
						    $msg[] = '</p></div>';
						    echo implode( PHP_EOL, $msg );
						});
					}
				}
			}
		}
	}


	private function _remove_tmp_directories() {
		global $wp_filesystem;
		if( ! $wp_filesystem || ! is_object($wp_filesystem) )
			WP_Filesystem();
		if( ! is_object($wp_filesystem) )
			wp_die('WP_Filesystem Error:' . print_r($wp_filesystem,true));

		$directories = array(WP_CONTENT_DIR . '/cache/read-offline', WP_CONTENT_DIR . '/cache/read-offline-tmp');
		foreach ($directories as $directory) {
			if (file_exists($directory)) {
				if (true !== $wp_filesystem->rmdir( $directory , true )) {
				    return add_action( 'admin_notices', function() use ( $directory ){
						    $msg[] = '<div class="error"><p>';
						    $msg[] = '<strong>Read Offline</strong>: ';
						    $msg[] = sprintf( __( 'Unable to remove cache directory "<strong>%s</strong>". Is it and its directories writable by the server?','read-offline' ), $directory );
						    $msg[] = '</p></div>';
						    echo implode( PHP_EOL, $msg );
						});
				}
			}
		}
	}

}
