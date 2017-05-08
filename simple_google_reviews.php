<?php
/**
* Plugin Name: Simple Google Reviews
* Plugin URI: https://github.com/jnorthard/google-places-wordpress
* Description: Display your Google places reviews on your WordPress website.
* Version: 1.0.0
* Author: James Northard
* Author URI: https://jamesnorthard.com
* License: GPLv3
*/

/* This plugin would not be possible without the google-places.js code provided by @peledies (https://github.com/peledies/google-places) - please take a moment to thank him for his work. */

class SimpleGoogleReviews {
	private $simple_google_reviews_options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'simple_google_reviews_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'simple_google_reviews_page_init' ) );
	}

	public function simple_google_reviews_add_plugin_page() {
		add_options_page(
			'Simple Google Reviews', // page_title
			'Simple Google Reviews', // menu_title
			'manage_options', // capability
			'simple-google-reviews', // menu_slug
			array( $this, 'simple_google_reviews_create_admin_page' ) // function
		);
	}

	public function simple_google_reviews_create_admin_page() {
		$this->simple_google_reviews_options = get_option( 'simple_google_reviews_option_name' ); ?>

		<div class="wrap">
			<h2>Simple Google Reviews</h2>
			<p>You must enter your custom Google Maps API Key and Google Place ID to display your reviews.</p>
			<p>To use the Simple Google Reviews plugin, you must register your app project on the Google API Console and get a Google API key which you can add to your app.  You can create a Google API key <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">here</a>.</p>
			<p>Place IDs uniquely identify a place in the Google Places database and on Google Maps.  You may find your Google Place ID <a href="https://developers.google.com/places/place-id" target="_blank">here</a>.</p>
			<p>Google restricts us to display a maximum of FIVE reviews.</p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'simple_google_reviews_option_group' );
					do_settings_sections( 'simple-google-reviews-admin' );
					submit_button();
				?>
			</form>
		</div>
	<?php }

	public function simple_google_reviews_page_init() {
		register_setting(
			'simple_google_reviews_option_group', // option_group
			'simple_google_reviews_option_name', // option_name
			array( $this, 'simple_google_reviews_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			'simple_google_reviews_setting_section', // id
			'Settings', // title
			array( $this, 'simple_google_reviews_section_info' ), // callback
			'simple-google-reviews-admin' // page
		);

		add_settings_field(
			'gapikey', // id
			'Google Maps API Key', // title
			array( $this, 'gapikey_callback' ), // callback
			'simple-google-reviews-admin', // page
			'simple_google_reviews_setting_section' // section
		);
		
		add_settings_field(
			'place_id', // id
			'Google Place ID', // title
			array( $this, 'place_id_callback' ), // callback
			'simple-google-reviews-admin', // page
			'simple_google_reviews_setting_section' // section
		);

		add_settings_field(
			'min_rating', // id
			'Minimum Rating', // title
			array( $this, 'min_rating_callback' ), // callback
			'simple-google-reviews-admin', // page
			'simple_google_reviews_setting_section' // section
		);

		add_settings_field(
			'num_ratings', // id
			'Number or Ratings to Display', // title
			array( $this, 'num_ratings_callback' ), // callback
			'simple-google-reviews-admin', // page
			'simple_google_reviews_setting_section' // section
		);

		add_settings_field(
			'rotate_reviews', // id
			'Would you like to rotate the reviews?', // title
			array( $this, 'rotate_reviews_callback' ), // callback
			'simple-google-reviews-admin', // page
			'simple_google_reviews_setting_section' // section
		);
	}

	public function simple_google_reviews_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['gapikey'] ) ) {
			$sanitary_values['gapikey'] = sanitize_text_field( $input['gapikey'] );
		}
		
		if ( isset( $input['place_id'] ) ) {
			$sanitary_values['place_id'] = sanitize_text_field( $input['place_id'] );
		}

		if ( isset( $input['min_rating'] ) ) {
			$sanitary_values['min_rating'] = $input['min_rating'];
		}

		if ( isset( $input['num_ratings'] ) ) {
			$sanitary_values['num_ratings'] = $input['num_ratings'];
		}

		if ( isset( $input['rotate_reviews'] ) ) {
			$sanitary_values['rotate_reviews'] = $input['rotate_reviews'];
		}

		return $sanitary_values;
	}

	public function simple_google_reviews_section_info() {
		
	}

	public function place_id_callback() {
		printf(
			'<input class="regular-text" type="text" name="simple_google_reviews_option_name[place_id]" id="place_id" value="%s">',
			isset( $this->simple_google_reviews_options['place_id'] ) ? esc_attr( $this->simple_google_reviews_options['place_id']) : ''
		);
	}
	
	public function gapikey_callback() {
		printf(
			'<input class="regular-text" type="text" name="simple_google_reviews_option_name[gapikey]" id="gapikey" value="%s">',
			isset( $this->simple_google_reviews_options['gapikey'] ) ? esc_attr( $this->simple_google_reviews_options['gapikey']) : ''
		);
	}

	public function min_rating_callback() {
		?> <select name="simple_google_reviews_option_name[min_rating]" id="min_rating">
			<?php $selected = (isset( $this->simple_google_reviews_options['min_rating'] ) && $this->simple_google_reviews_options['min_rating'] === '5') ? 'selected' : '' ; ?>
			<option value="5" <?php echo $selected; ?>>5</option>
			<?php $selected = (isset( $this->simple_google_reviews_options['min_rating'] ) && $this->simple_google_reviews_options['min_rating'] === '4') ? 'selected' : '' ; ?>
			<option value="4" <?php echo $selected; ?>>4</option>
			<?php $selected = (isset( $this->simple_google_reviews_options['min_rating'] ) && $this->simple_google_reviews_options['min_rating'] === '3') ? 'selected' : '' ; ?>
			<option value="3" <?php echo $selected; ?>>3</option>
			<?php $selected = (isset( $this->simple_google_reviews_options['min_rating'] ) && $this->simple_google_reviews_options['min_rating'] === '2') ? 'selected' : '' ; ?>
			<option value="2" <?php echo $selected; ?>>2</option>
			<?php $selected = (isset( $this->simple_google_reviews_options['min_rating'] ) && $this->simple_google_reviews_options['min_rating'] === '1') ? 'selected' : '' ; ?>
			<option value="1" <?php echo $selected; ?>>1</option>
		</select> <?php
	}

	public function num_ratings_callback() {
		?> <select name="simple_google_reviews_option_name[num_ratings]" id="num_ratings">
			<?php $selected = (isset( $this->simple_google_reviews_options['num_ratings'] ) && $this->simple_google_reviews_options['num_ratings'] === '5') ? 'selected' : '' ; ?>
			<option value="5" <?php echo $selected; ?>>5</option>
			<?php $selected = (isset( $this->simple_google_reviews_options['num_ratings'] ) && $this->simple_google_reviews_options['num_ratings'] === '4') ? 'selected' : '' ; ?>
			<option value="4" <?php echo $selected; ?>>4</option>
			<?php $selected = (isset( $this->simple_google_reviews_options['num_ratings'] ) && $this->simple_google_reviews_options['num_ratings'] === '3') ? 'selected' : '' ; ?>
			<option value="3" <?php echo $selected; ?>>3</option>
			<?php $selected = (isset( $this->simple_google_reviews_options['num_ratings'] ) && $this->simple_google_reviews_options['num_ratings'] === '2') ? 'selected' : '' ; ?>
			<option value="2" <?php echo $selected; ?>>2</option>
			<?php $selected = (isset( $this->simple_google_reviews_options['num_ratings'] ) && $this->simple_google_reviews_options['num_ratings'] === '1') ? 'selected' : '' ; ?>
			<option value="1" <?php echo $selected; ?>>1</option>
		</select> <?php
	}

	public function rotate_reviews_callback() {
		?> <select name="simple_google_reviews_option_name[rotate_reviews]" id="rotate_reviews">
			<?php $selected = (isset( $this->simple_google_reviews_options['rotate_reviews'] ) && $this->simple_google_reviews_options['rotate_reviews'] === 'false') ? 'selected' : '' ; ?>
			<option value="false" <?php echo $selected; ?>>No</option>
			<?php $selected = (isset( $this->simple_google_reviews_options['rotate_reviews'] ) && $this->simple_google_reviews_options['rotate_reviews'] === '5000') ? 'selected' : '' ; ?>
			<option value="5000" <?php echo $selected; ?>>5 seconds</option>
			<?php $selected = (isset( $this->simple_google_reviews_options['rotate_reviews'] ) && $this->simple_google_reviews_options['rotate_reviews'] === '7000') ? 'selected' : '' ; ?>
			<option value="7000" <?php echo $selected; ?>>7 seconds</option>
			<?php $selected = (isset( $this->simple_google_reviews_options['rotate_reviews'] ) && $this->simple_google_reviews_options['rotate_reviews'] === '10000') ? 'selected' : '' ; ?>
			<option value="10000" <?php echo $selected; ?>>10 seconds</option>
			<?php $selected = (isset( $this->simple_google_reviews_options['rotate_reviews'] ) && $this->simple_google_reviews_options['rotate_reviews'] === '15000') ? 'selected' : '' ; ?>
			<option value="15000" <?php echo $selected; ?>>15 seconds</option>
			<?php $selected = (isset( $this->simple_google_reviews_options['rotate_reviews'] ) && $this->simple_google_reviews_options['rotate_reviews'] === '20000') ? 'selected' : '' ; ?>
			<option value="20000" <?php echo $selected; ?>>20 seconds</option>
		</select> <?php
	}

}
if ( is_admin() )
	$simple_google_reviews = new SimpleGoogleReviews();

// Add [simplegooglereviews] Shortcode
function simple_google_reviews() {
	wp_enqueue_script('reviews_sgr_reviews_sgr_googleplacesapi');
	wp_enqueue_style('reviews_sgr_reviews_sgr_googleplaces');
	wp_enqueue_script('reviews_sgr_reviews_sgr_googleplaces');

    $result = '<div id="google-reviews"></div>';
    return $result;
}
add_shortcode( 'simplegooglereviews', 'simple_google_reviews' );

// Load scripts and styles
function reviews_sgr_reviews_sgr_googleplaces() {
	$simple_google_reviews_options = get_option( 'simple_google_reviews_option_name' ); // Array of All Options
	$gapikey = $simple_google_reviews_options['gapikey']; // Google Maps API Key
	$place_id = $simple_google_reviews_options['place_id']; // Google Place ID
	$min_rating = $simple_google_reviews_options['min_rating']; // Minimum Rating
	$num_ratings = $simple_google_reviews_options['num_ratings']; // Number or Ratings to Display
	$rotate_reviews = $simple_google_reviews_options['rotate_reviews']; // Would you like to rotate the reviews?
	wp_register_style('reviews_sgr_reviews_sgr_googleplaces', plugins_url('google-places.css',__FILE__ ));
	wp_register_script( 'reviews_sgr_reviews_sgr_googleplacesapi', 'https://maps.googleapis.com/maps/api/js?key='.$gapikey.'&v=3.exp&signed_in=true&libraries=places', array('jquery'), null, true );
	wp_register_script( 'reviews_sgr_reviews_sgr_googleplaces', plugins_url('google-places.js',__FILE__ ), array('jquery'), null, true );
	$inline_js = 'jQuery(document).ready(function() {
      jQuery("#google-reviews").reviews_sgr_reviews_sgr_googleplaces({placeId: \''.$place_id.'\', render: [\'reviews\'], min_rating: '.$min_rating.', max_rows: '.$num_ratings.', rotateTime: '.$rotate_reviews.'});});';
	wp_add_inline_script('reviews_sgr_reviews_sgr_googleplaces', $inline_js);
}
add_action( 'wp_enqueue_scripts','reviews_sgr_reviews_sgr_googleplaces');

