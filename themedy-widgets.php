<?php
/*
Plugin Name: Themedy Widgets
Plugin URI: https://themedy.com
Description: A selection of widgets to extend your Themedy site even further.
Version: 1.0.9
Author: Themedy
Author URI: https://themedy.com
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// Localization
load_child_theme_textdomain( 'themedy', 'includes/languages');

// Add Widgets - trying to avoid white screen of death if user is using older theme with widgets built in
add_action( 'after_setup_theme', 'themedy_widget_setup' );
function themedy_widget_setup() {
	if ( !class_exists( 'themedy_video_widget' )) {
		include('includes/widgets/widget-video.php');
	}
	if ( !class_exists( 'themedy_ad120x60_widget' )) {
		include('includes/widgets/widget-ad120x60.php');
	}
	if ( !class_exists( 'themedy_ad120x240_widget' )) {
		include('includes/widgets/widget-ad120x240.php');
	}
	if ( !class_exists( 'themedy_ad_widget' )) {
		include('includes/widgets/widget-ad125.php');
	}
	if ( !class_exists( 'themedy_ad300_widget' )) {
		include('includes/widgets/widget-ad300x250.php');
	}
	if ( !class_exists( 'themedy_ad300x600_widget' )) {
		include('includes/widgets/widget-ad300x600.php');
	}
	if ( !class_exists( 'themedy_ad468x60_widget' )) {
		include('includes/widgets/widget-ad468x60.php');
	}
	if ( !class_exists( 'themedy_ad620x100_widget' )) {
		include('includes/widgets/widget-ad620x100.php');
	}
	if ( !class_exists( 'themedy_flickr_widget' )) {
		include('includes/widgets/widget-flickr.php');
	}
	if ( !class_exists( 'themedy_tab_widgets' )) {
		include('includes/widgets/widget-tabbed.php');
	}
	if ( !class_exists( 'social_list_widget' )) {
		include('includes/widgets/widget-social.php');
	}
	if ( !class_exists( 'Featured_Post' ) AND ! defined( 'GENESIS_SETTINGS_FIELD' ) ) {
		include('includes/widgets/featured-post-widget.php');
	}
}
