<?php
/*
 * Plugin Name: Themedy - 125x125 Ad Unit
 * Description: A widget that allows the selection and configuration of 6 125x125 ad units
 * Version: 1.0
 */

/*
 * Add function to widgets_init that'll load our widget.
 */
add_action( 'widgets_init', 'themedy_ads_widgets' );

/*
 * Register widget.
 */
function themedy_ads_widgets() {
	register_widget( 'Themedy_Ad_Widget' );
}

add_action('init', 'themedy_ads_enqueue');
function themedy_ads_enqueue() {
	/* Paths */
	$themedy_widgets_path = dirname(__FILE__);
	$themedy_widgets_main_file = dirname(__FILE__).'/widget-ad125.php';
	$themedy_widgets_directory = plugin_dir_url($themedy_widgets_main_file);

	if (is_active_widget(false, false, 'themedy_ad_widget') and !is_admin()){
		wp_enqueue_style('themedy-ads-css', $themedy_widgets_directory.'css/widget-ads.css');
	}
}

/*
 * Widget class.
 */
class themedy_ad_widget extends WP_Widget {

	/* ---------------------------- */
	/* -------- Widget setup -------- */
	/* ---------------------------- */

	function __construct() {

		/* Widget settings */
		$widget_ops = array( 'classname' => 'themedy_ad_widget', 'description' => __('A widget that allows the display and configuration of up to six 125x125 Banners.', 'themedy') );

		/* Widget control settings */
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'themedy_ad_widget' );

		/* Create the widget */
		parent::__construct( 'themedy_ad_widget', __('Themedy - 125x125 Ads', 'themedy'), $widget_ops );
	}

	/* ---------------------------- */
	/* ------- Display Widget -------- */
	/* ---------------------------- */

	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$ad1 = $instance['ad1'];
		$ad2 = $instance['ad2'];
		$ad3 = $instance['ad3'];
		$ad4 = $instance['ad4'];
		$ad5 = $instance['ad5'];
		$ad6 = $instance['ad6'];
		$link1 = $instance['link1'];
		$link2 = $instance['link2'];
		$link3 = $instance['link3'];
		$link4 = $instance['link4'];
		$link5 = $instance['link5'];
		$link6 = $instance['link6'];
		$randomize = $instance['random'];

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title;

		//Randomize ads order in a new array
		$ads = array();

		/* Display a containing div */
		echo '<div class="ads-125 clearfix">';
		echo '<ul class="clearfix">';

		/* Display Ad 1. */
		if ( $link1 )
			$ads[] = '<li><a href="' . $link1 . '"><img src="' . $ad1 . '" width="125" height="125" alt="" /></a></li>';

		elseif ( $ad1 )
		 	$ads[] = '<li><img src="' . $ad1 . '" width="125" height="125" alt="" /></li>';

		/* Display Ad 2. */
		if ( $link2 )
			$ads[] = '<li><a href="' . $link2 . '"><img src="' . $ad2 . '" width="125" height="125" alt="" /></a></li>';

		elseif ( $ad2 )
		 	$ads[] = '<li><img src="' . $ad2 . '" width="125" height="125" alt="" /></li>';

		/* Display Ad 3. */
		if ( $link3 )
			$ads[] = '<li><a href="' . $link3 . '"><img src="' . $ad3 . '" width="125" height="125" alt="" /></a></li>';

		elseif ( $ad3 )
		 	echo '<li><img src="' . $ad3 . '" width="125" height="125" alt="" /></li>';

		/* Display Ad 4. */
		if ( $link4 )
			$ads[] = '<li><a href="' . $link4 . '"><img src="' . $ad4 . '" width="125" height="125" alt="" /></a></li>';

		elseif ( $ad4 )
		 	$ads[] = '<li><img src="' . $ad4 . '" width="125" height="125" alt="" /></li>';

		/* Display Ad 5. */
		if ( $link5 )
			$ads[] = '<li><a href="' . $link5 . '"><img src="' . $ad5 . '" width="125" height="125" alt="" /></a></li>';

		elseif ( $ad5 )
		 	$ads[] = '<li><img src="' . $ad5 . '" width="125" height="125" alt="" /></li>';

		/* Display Ad 6. */
		if ( $link6 )
			$ads[] = '<li><a href="' . $link6 . '"><img src="' . $ad6 . '" width="125" height="125" alt="" /></a></li>';

		elseif ( $ad6 )
		 	$ads[] = '<li><img src="' . $ad6 . '" width="125" height="125" alt="" /></li>';

		//Randomize order if user want it
		if ($randomize){
			shuffle($ads);
		}

		//Display ads
		foreach($ads as $ad){
			echo $ad;
		}

		echo '</ul>';
		echo '</div>';

		/* After widget (defined by themes). */
		echo $after_widget;
	}

	/* ---------------------------- */
	/* ------- Update Widget -------- */
	/* ---------------------------- */

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );

		/* No need to strip tags */
		$instance['ad1'] = $new_instance['ad1'];
		$instance['ad2'] = $new_instance['ad2'];
		$instance['ad3'] = $new_instance['ad3'];
		$instance['ad4'] = $new_instance['ad4'];
		$instance['ad5'] = $new_instance['ad5'];
		$instance['ad6'] = $new_instance['ad6'];
		$instance['link1'] = $new_instance['link1'];
		$instance['link2'] = $new_instance['link2'];
		$instance['link3'] = $new_instance['link3'];
		$instance['link4'] = $new_instance['link4'];
		$instance['link5'] = $new_instance['link5'];
		$instance['link6'] = $new_instance['link6'];
		$instance['random'] = $new_instance['random'];

		return $instance;
	}

	/* ---------------------------- */
	/* ------- Widget Settings ------- */
	/* ---------------------------- */

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */

	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array(
		'title' => 'Our Sponsors',
		'ad1' => plugin_dir_url( __FILE__ ) . "images/ad-125x125.png",
		'link1' => 'http://themedy.com',
		'ad2' => plugin_dir_url( __FILE__ ) . "images/ad-125x125.png",
		'link2' => 'http://themedy.com',
		'ad3' => '',
		'link3' => '',
		'ad4' => '',
		'link4' => '',
		'ad5' => '',
		'link5' => '',
		'ad6' => '',
		'link6' => '',
		'random' => false
		);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'themedy') ?></label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
		</p>

		<!-- Ad 1 image url: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'ad1' ); ?>"><?php _e('Ad 1 image url:', 'themedy') ?></label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'ad1' ); ?>" name="<?php echo $this->get_field_name( 'ad1' ); ?>" value="<?php echo $instance['ad1']; ?>" />
		</p>

		<!-- Ad 1 link url: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'link1' ); ?>"><?php _e('Ad 1 link url:', 'themedy') ?></label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'link1' ); ?>" name="<?php echo $this->get_field_name( 'link1' ); ?>" value="<?php echo $instance['link1']; ?>" />
		</p>

		<!-- Ad 2 image url: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'ad2' ); ?>"><?php _e('Ad 2 image url:', 'themedy') ?></label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'ad2' ); ?>" name="<?php echo $this->get_field_name( 'ad2' ); ?>" value="<?php echo $instance['ad2']; ?>" />
		</p>

		<!-- Ad 2 link url: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'link2' ); ?>"><?php _e('Ad 2 link url:', 'themedy') ?></label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'link2' ); ?>" name="<?php echo $this->get_field_name( 'link2' ); ?>" value="<?php echo $instance['link2']; ?>" />
		</p>

		<!-- Ad 3 image url: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'ad3' ); ?>"><?php _e('Ad 3 image url:', 'themedy') ?></label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'ad3' ); ?>" name="<?php echo $this->get_field_name( 'ad3' ); ?>" value="<?php echo $instance['ad3']; ?>" />
		</p>

		<!-- Ad 3 link url: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'link3' ); ?>"><?php _e('Ad 3 link url:', 'themedy') ?></label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'link3' ); ?>" name="<?php echo $this->get_field_name( 'link3' ); ?>" value="<?php echo $instance['link3']; ?>" />
		</p>

		<!-- Ad 4 image url: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'ad4' ); ?>"><?php _e('Ad 4 image url:', 'themedy') ?></label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'ad4' ); ?>" name="<?php echo $this->get_field_name( 'ad4' ); ?>" value="<?php echo $instance['ad4']; ?>" />
		</p>

		<!-- Ad 4 link url: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'link4' ); ?>"><?php _e('Ad 4 link url:', 'themedy') ?></label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'link4' ); ?>" name="<?php echo $this->get_field_name( 'link4' ); ?>" value="<?php echo $instance['link4']; ?>" />
		</p>

		<!-- Ad 5 image url: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'ad5' ); ?>"><?php _e('Ad 5 image url:', 'themedy') ?></label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'ad5' ); ?>" name="<?php echo $this->get_field_name( 'ad5' ); ?>" value="<?php echo $instance['ad5']; ?>" />
		</p>

		<!-- Ad 5 link url: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'link5' ); ?>"><?php _e('Ad 5 link url:', 'themedy') ?></label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'link5' ); ?>" name="<?php echo $this->get_field_name( 'link5' ); ?>" value="<?php echo $instance['link5']; ?>" />
		</p>

		<!-- Ad 6 image url: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'ad6' ); ?>"><?php _e('Ad 6 image url:', 'themedy') ?></label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'ad6' ); ?>" name="<?php echo $this->get_field_name( 'ad6' ); ?>" value="<?php echo $instance['ad6']; ?>" />
		</p>

		<!-- Ad 6 link url: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'link6' ); ?>"><?php _e('Ad 6 link url:', 'themedy') ?></label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'link6' ); ?>" name="<?php echo $this->get_field_name( 'link6' ); ?>" value="<?php echo $instance['link6']; ?>" />
		</p>

		<!-- Randomize? -->
		<p>
			<label for="<?php echo $this->get_field_id( 'random' ); ?>"><?php _e('Randomize ads order?', 'themedy') ?></label>
			<?php if ($instance['random']){ ?>
				<input type="checkbox" id="<?php echo $this->get_field_id( 'random' ); ?>" name="<?php echo $this->get_field_name( 'random' ); ?>" checked="checked" />
			<?php } else { ?>
				<input type="checkbox" id="<?php echo $this->get_field_id( 'random' ); ?>" name="<?php echo $this->get_field_name( 'random' ); ?>"  />
			<?php } ?>
		</p>
	<?php
	}
}
?>
