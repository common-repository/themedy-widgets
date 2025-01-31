<?php
/**
 * Featured Posts Widget
 * Worked from Genesis - Studiopress.com
 */
 
 
function themedy_truncate_phrase($phrase, $max_characters) {

	$phrase = trim( $phrase );
	
	if ( strlen($phrase) > $max_characters ) {
	
		// Truncate $phrase to $max_characters + 1
		$phrase = substr($phrase, 0, $max_characters + 1);
	
		// Truncate to the last space in the truncated string.
		$phrase = trim(substr($phrase, 0, strrpos($phrase, ' ')));
	}
	
	return $phrase;
}
 
function get_the_content_limit($max_char, $more_link_text = '(more...)', $stripteaser = 0) {
	
	$content = get_the_content('', $stripteaser);
	$link = "";
	
	// Strip tags and shortcodes
	$content = strip_tags(strip_shortcodes($content), apply_filters('get_the_content_limit_allowedtags', '<script>,<style>'));
	
	// Inline styles/scripts
	$content = trim(preg_replace('#<(s(cript|tyle)).*?</\1>#si', '', $content));
	
	// Truncate $content to $max_char
	$content = themedy_truncate_phrase($content, $max_char);
	
	// More Link?
	if ( $more_link_text ) {
		$link = apply_filters( 'get_the_content_more_link', sprintf( '%s <a href="%s" class="more-link">%s</a>', '&hellip;', get_permalink(), $more_link_text ) );
		
		$output = sprintf('<p>%s %s</p>', $content, $link);
	}
	else {
		$output = sprintf('<p>%s</p>', $content);
	}
	
	return apply_filters('get_the_content_limit', $output, $content, $link, $max_char);

}

function the_content_limit($max_char, $more_link_text = '(more...)', $stripteaser = 0) {
	
	$content = get_the_content_limit($max_char, $more_link_text, $stripteaser);
	echo apply_filters('the_content_limit', $content);
	
}
 
function themedy_get_image_id($num = 0) {
	global $post;

	$image_ids = array_keys(
		get_children(
			array(
				'post_parent' => $post->ID,
				'post_type' => 'attachment',
				'post_mime_type' => 'image',
				'orderby' => 'menu_order',
				'order' => 'ASC'
			)
		)
	);

	if ( isset($image_ids[$num]) )
		return $image_ids[$num];

	return false;
}

/**
 * Pulls an image from the media gallery and returns it
 *
 * @since 0.1
 */
// pulls an image URL from the media gallery
function themedy_get_image_featured($args = array()) {
	global $post;
	
	$defaults = array(
		'format' => 'html',
		'size' => 'full',
		'num' => 0,
		'attr' => ''
	);
	$defaults = apply_filters('themedy_get_image_default_args', $defaults);
	
	$args = wp_parse_args($args, $defaults);
	
	// Allow child theme to short-circuit this function
	$pre = apply_filters('themedy_pre_get_image', false, $args, $post);
	if ( false !== $pre ) return $pre;

	// check for post image (native WP) 
	if ( has_post_thumbnail() && ($args['num'] === 0) ) { 
		$id = get_post_thumbnail_id(); 
		$html = wp_get_attachment_image($id, $args['size'], false, $args['attr']); 
		list($url) = wp_get_attachment_image_src($id, $args['size'], false, $args['attr']); 
	} 
	// else pull the first image attachment 
	else { 
		$id = themedy_get_image_id($args['num']); 
		$html = wp_get_attachment_image($id, $args['size'], false, $args['attr']); 
		list($url) = wp_get_attachment_image_src($id, $args['size'], false, $args['attr']); 
	}
	
	// source path, relative to the root
	$src = str_replace(get_bloginfo('url'), '', $url);

	// determine output
	if ( strtolower($args['format']) == 'html' )
		$output = $html;
	elseif ( strtolower($args['format']) == 'url' )
		$output = $url;
	else
		$output = $src;
		
	// return FALSE if $url is blank
	if ( empty($url) ) $output = FALSE;
	
	// return FALSE if $src is invalid (file doesn't exist)
	//if ( !file_exists(ABSPATH . $src) ) $output = FALSE;
	
	// return data, filtered
	return apply_filters('themedy_get_image', $output, $args, $id, $html, $url, $src);
}
/**
 * Pulls an image from media gallery
 * and echos it
 *
 * @since 0.1
 */
function themedy_image($args = array()) {
	$image = themedy_get_image_featured($args);
	
	if ( $image )
		echo $image;
	else
		return FALSE;
}
 
function themedy_get_additional_image_sizes() {
	global $_wp_additional_image_sizes;
	
	if ( $_wp_additional_image_sizes )
		return $_wp_additional_image_sizes;

	return array();
}

add_action('widgets_init', create_function('', "register_widget('Featured_Post');"));
class Featured_Post extends WP_Widget {

	/* ---------------------------- */
	/* -------- Widget setup -------- */
	/* ---------------------------- */
	
	function __construct() {
	
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'featuredpost', 'description' => __('Displays featured posts with thumbnails.', 'themedy') );
		$control_ops = array( 'width' => 505, 'height' => 350, 'id_base' => 'featured-post' );

		/* Create the widget. */
		parent::__construct( 'featured-post', __('Themedy - Featured Posts', 'themedy'), $widget_ops, $control_ops  );
	}

	/* ---------------------------- */
	/* ------- Display Widget -------- */
	/* ---------------------------- */

	function widget($args, $instance) {
		extract($args);
		
		// defaults
		$instance = wp_parse_args( (array)$instance, array(
			'title' => '',
			'posts_cat' => '',
			'posts_num' => 1,
			'posts_offset' => 0,
			'orderby' => '',
			'order' => '',
			'show_image' => 0,
			'image_alignment' => '',
			'image_size' => '',
			'show_gravatar' => 0,
			'gravatar_alignment' => '',
			'gravatar_size' => '',
			'show_title' => 0,
			'show_byline' => 0,
			'post_info' => '[post_date] ' . __('By', 'themedy') . ' [post_author_posts_link] [post_comments]',
			'show_content' => 'excerpt',
			'content_limit' => '',
			'more_text' => __('[Read More...]', 'themedy'),
			'extra_num' => '',
			'extra_title' => '',
			'more_from_category' => '',
			'more_from_category_text' => __('More Posts from this Category', 'themedy')
		) );
		
		echo $before_widget;
		
			// Set up the author bio
			if (!empty($instance['title']))
				echo $before_title . apply_filters('widget_title', $instance['title']) . $after_title;

			$featured_posts = new WP_Query(array('post_type' => 'post', 'cat' => $instance['posts_cat'], 'showposts' => $instance['posts_num'],'offset' => $instance['posts_offset'], 'orderby' => $instance['orderby'], 'order' => $instance['order']));
			if($featured_posts->have_posts()) : while($featured_posts->have_posts()) : $featured_posts->the_post();
				
				echo '<div '; post_class(); echo '>';

				if(!empty($instance['show_image'])) :
					printf( '<a href="%s" title="%s" class="%s">%s</a>', get_permalink(), the_title_attribute('echo=0'), esc_attr( $instance['image_alignment'] ), themedy_get_image_featured( array( 'format' => 'html', 'size' => $instance['image_size'] ) ) );
				endif;
				
				if(!empty($instance['show_gravatar'])) :
					echo '<span class="'.esc_attr($instance['gravatar_alignment']).'">';
					echo get_avatar( get_the_author_meta('ID'), $instance['gravatar_size'] );
					echo '</span>';
				endif;
				
				if(!empty($instance['show_title'])) :
					printf( '<h2><a href="%s" title="%s">%s</a></h2>', get_permalink(), the_title_attribute('echo=0'), the_title_attribute('echo=0') );
				endif;
				
				if ( !empty( $instance['show_byline'] ) && !empty( $instance['post_info'] ) ) :
					printf( '<p class="byline post-info">%s</p>', do_shortcode( esc_html( $instance['post_info'] ) ) );
				endif;
				
				if(!empty($instance['show_content'])) :
				
					if($instance['show_content'] == 'excerpt') :
						the_excerpt();
					elseif($instance['show_content'] == 'content-limit') :
						the_content_limit( (int)$instance['content_limit'], esc_html( $instance['more_text'] ) );
					else :
						the_content( esc_html( $instance['more_text'] ) );
					endif;
					
				endif;
				
				echo '</div><!--end post_class()-->'."\n\n";
					
			endwhile; endif;
			
			// The EXTRA Posts (list)
			if ( !empty( $instance['extra_num'] ) ) :

					if ( !empty($instance['extra_title'] ) )
						echo $before_title . esc_html( $instance['extra_title'] ) . $after_title;

					$offset = intval($instance['posts_num']) + intval($instance['posts_offset']);
					$extra_posts = new WP_Query( array( 'cat' => $instance['posts_cat'], 'showposts' => $instance['extra_num'], 'offset' => $offset ) );
					
					$listitems = '';
					if ( $extra_posts->have_posts() ) :
					
						while ( $extra_posts->have_posts() ) :
							
							$extra_posts->the_post();
							$listitems .= sprintf( '<li><a href="%s" title="%s">%s</a></li>', get_permalink(), the_title_attribute('echo=0'), get_the_title() );

						endwhile;
						
						if ( strlen($listitems) > 0 ) {
							printf( '<ul>%s</ul>', $listitems );
						}

					endif;

			endif;
			
			if(!empty($instance['more_from_category']) && !empty($instance['posts_cat'])) :
			
				echo '<p class="more-from-category"><a href="'.get_category_link($instance['posts_cat']).'" title="'.get_cat_name($instance['posts_cat']).'">'.esc_html($instance['more_from_category_text']).'</a></p>';
			
			endif;
		
		echo $after_widget;
		wp_reset_query();
	}

	function update($new_instance, $old_instance) {
		return $new_instance;
	}

	function form($instance) { 
		
		// ensure value exists
		$instance = wp_parse_args( (array)$instance, array(
			'title' => '',
			'posts_cat' => '',
			'posts_num' => 0,
			'posts_offset' => 0,
			'orderby' => '',
			'order' => '',
			'show_image' => 0,
			'image_alignment' => '',
			'image_size' => '',
			'show_gravatar' => 0,
			'gravatar_alignment' => '',
			'gravatar_size' => '',
			'show_title' => 0,
			'show_byline' => 0,
			'post_info' => '[post_date] ' . __('By', 'themedy') . ' [post_author_posts_link] [post_comments]',
			'show_content' => 'excerpt',
			'content_limit' => '',
			'more_text' => __('[Read More...]', 'themedy'),
			'extra_num' => '',
			'extra_title' => '',
			'more_from_category' => '',
			'more_from_category_text' => __('More Posts from this Category', 'themedy')
		) );
		
?>
			
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'themedy'); ?>:</label>
		<input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" style="width:99%;" /></p>
		
	<div style="float: left; width: 250px;">
		
		<div style="background: #f1f1f1; border: 1px solid #DDD; padding: 10px 10px 0px 10px;">
		
		<p><label for="<?php echo $this->get_field_id('posts_cat'); ?>"><?php _e('Category', 'themedy'); ?>:</label>
		<?php wp_dropdown_categories(array('name' => $this->get_field_name('posts_cat'), 'selected' => $instance['posts_cat'], 'orderby' => 'Name' , 'hierarchical' => 1, 'show_option_all' => __("All Categories", 'themedy'), 'hide_empty' => '0')); ?></p>
		
		<p><label for="<?php echo $this->get_field_id('posts_num'); ?>"><?php _e('Number of Posts to Show', 'themedy'); ?>:</label>
		<input type="text" id="<?php echo $this->get_field_id('posts_num'); ?>" name="<?php echo $this->get_field_name('posts_num'); ?>" value="<?php echo esc_attr( $instance['posts_num'] ); ?>" size="2" /></p>
		
		<p><label for="<?php echo $this->get_field_id('posts_offset'); ?>"><?php _e('Number of Posts to Offset', 'themedy'); ?>:</label>
		<input type="text" id="<?php echo $this->get_field_id('posts_offset'); ?>" name="<?php echo $this->get_field_name('posts_offset'); ?>" value="<?php echo esc_attr( $instance['posts_offset'] ); ?>" size="2" /></p>
		
		<p><label for="<?php echo $this->get_field_id('orderby'); ?>"><?php _e('Order By', 'themedy'); ?>:</label>
		<select id="<?php echo $this->get_field_id('orderby'); ?>" name="<?php echo $this->get_field_name('orderby'); ?>">
			<option style="padding-right:10px;" value="date" <?php selected('date', $instance['orderby']); ?>><?php _e('Date', 'themedy'); ?></option>
			<option style="padding-right:10px;" value="title" <?php selected('title', $instance['orderby']); ?>><?php _e('Title', 'themedy'); ?></option>
			<option style="padding-right:10px;" value="parent" <?php selected('parent', $instance['orderby']); ?>><?php _e('Parent', 'themedy'); ?></option>
			<option style="padding-right:10px;" value="ID" <?php selected('ID', $instance['orderby']); ?>><?php _e('ID', 'themedy'); ?></option>
			<option style="padding-right:10px;" value="comment_count" <?php selected('comment_count', $instance['orderby']); ?>><?php _e('Comment Count', 'themedy'); ?></option>
			<option style="padding-right:10px;" value="rand" <?php selected('rand', $instance['orderby']); ?>><?php _e('Random', 'themedy'); ?></option>
		</select></p>
		
		<p><label for="<?php echo $this->get_field_id('order'); ?>"><?php _e('Sort Order', 'themedy'); ?>:</label>
		<select id="<?php echo $this->get_field_id('order'); ?>" name="<?php echo $this->get_field_name('order'); ?>">
			<option style="padding-right:10px;" value="DESC" <?php selected('DESC', $instance['order']); ?>><?php _e('Descending (3, 2, 1)', 'themedy'); ?></option>
			<option style="padding-right:10px;" value="ASC" <?php selected('ASC', $instance['order']); ?>><?php _e('Ascending (1, 2, 3)', 'themedy'); ?></option>
		</select></p>
		
		</div>
		<div style="background: #f1f1f1; border: 1px solid #DDD; padding: 10px 10px 0px 10px; margin-top: 10px;">
		
		<p><input id="<?php echo $this->get_field_id('show_gravatar'); ?>" type="checkbox" name="<?php echo $this->get_field_name('show_gravatar'); ?>" value="1" <?php checked(1, $instance['show_gravatar']); ?>/> <label for="<?php echo $this->get_field_id('show_gravatar'); ?>"><?php _e('Show Author Gravatar', 'themedy'); ?></label></p>
		
		<p><label for="<?php echo $this->get_field_id('gravatar_size'); ?>"><?php _e('Gravatar Size', 'themedy'); ?>:</label>
		<select id="<?php echo $this->get_field_id('gravatar_size'); ?>" name="<?php echo $this->get_field_name('gravatar_size'); ?>">
			<option style="padding-right:10px;" value="45" <?php selected(45, $instance['gravatar_size']); ?>><?php _e('Small (45px)', 'themedy'); ?></option>
			<option style="padding-right:10px;" value="65" <?php selected(65, $instance['gravatar_size']); ?>><?php _e('Medium (65px)', 'themedy'); ?></option>
			<option style="padding-right:10px;" value="85" <?php selected(85, $instance['gravatar_size']); ?>><?php _e('Large (85px)', 'themedy'); ?></option>
			<option style="padding-right:10px;" value="125" <?php selected(105, $instance['gravatar_size']); ?>><?php _e('Extra Large (125px)', 'themedy'); ?></option>
		</select></p>
		
		<p><label for="<?php echo $this->get_field_id('gravatar_alignment'); ?>"><?php _e('Gravatar Alignment', 'themedy'); ?>:</label>
		<select id="<?php echo $this->get_field_id('gravatar_alignment'); ?>" name="<?php echo $this->get_field_name('gravatar_alignment'); ?>">
			<option style="padding-right:10px;" value="">- <?php _e('None', 'themedy'); ?> -</option>
			<option style="padding-right:10px;" value="alignleft" <?php selected('alignleft', $instance['gravatar_alignment']); ?>><?php _e('Left', 'themedy'); ?></option>
			<option style="padding-right:10px;" value="alignright" <?php selected('alignright', $instance['gravatar_alignment']); ?>><?php _e('Right', 'themedy'); ?></option>
		</select></p>
		
		</div>
		<div style="background: #f1f1f1; border: 1px solid #DDD; padding: 10px 10px 0px 10px; margin-top: 10px;">
		
		<p><input id="<?php echo $this->get_field_id('show_image'); ?>" type="checkbox" name="<?php echo $this->get_field_name('show_image'); ?>" value="1" <?php checked(1, $instance['show_image']); ?>/> <label for="<?php echo $this->get_field_id('show_image'); ?>"><?php _e('Show Featured Image', 'themedy'); ?></label></p>

		<p><label for="<?php echo $this->get_field_id('image_size'); ?>"><?php _e('Image Size', 'themedy'); ?>:</label>
		<?php $sizes = themedy_get_additional_image_sizes(); ?>
		<select id="<?php echo $this->get_field_id('image_size'); ?>" name="<?php echo $this->get_field_name('image_size'); ?>">
			<option style="padding-right:10px;" value="thumbnail">thumbnail (<?php echo get_option('thumbnail_size_w'); ?>x<?php echo get_option('thumbnail_size_h'); ?>)</option>
			<?php
			foreach((array)$sizes as $name => $size) :
			echo '<option style="padding-right: 10px;" value="'.esc_attr($name).'" '.selected($name, $instance['image_size'], FALSE).'>'.esc_html($name).' ('.$size['width'].'x'.$size['height'].')</option>';
			endforeach;
			?>
		</select></p>	
		
		<p><label for="<?php echo $this->get_field_id('image_alignment'); ?>"><?php _e('Image Alignment', 'themedy'); ?>:</label>
		<select id="<?php echo $this->get_field_id('image_alignment'); ?>" name="<?php echo $this->get_field_name('image_alignment'); ?>">
			<option style="padding-right:10px;" value="">- <?php _e('None', 'themedy'); ?> -</option>
			<option style="padding-right:10px;" value="alignleft" <?php selected('alignleft', $instance['image_alignment']); ?>><?php _e('Left', 'themedy'); ?></option>
			<option style="padding-right:10px;" value="alignright" <?php selected('alignright', $instance['image_alignment']); ?>><?php _e('Right', 'themedy'); ?></option>
		</select></p>
		
		</div>
		
	</div>
	
	<div style="float: left; width: 250px; margin-left: 10px;">
		
		<div style="background: #f1f1f1; border: 1px solid #DDD; padding: 10px 10px 0px 10px;">
		
		<p><input id="<?php echo $this->get_field_id('show_title'); ?>" type="checkbox" name="<?php echo $this->get_field_name('show_title'); ?>" value="1" <?php checked(1, $instance['show_title']); ?>/> <label for="<?php echo $this->get_field_id('show_title'); ?>"><?php _e('Show Post Title', 'themedy'); ?></label></p>
		
		<p><input id="<?php echo $this->get_field_id('show_byline'); ?>" type="checkbox" name="<?php echo $this->get_field_name('show_byline'); ?>" value="1" <?php checked(1, $instance['show_byline']); ?>/> <label for="<?php echo $this->get_field_id('show_byline'); ?>"><?php _e('Show Post Info', 'themedy'); ?></label>
		
		<input type="text" id="<?php echo $this->get_field_id('post_info'); ?>" name="<?php echo $this->get_field_name('post_info'); ?>" value="<?php echo esc_attr($instance['post_info']); ?>" style="width: 99%;" />
			
		</p>
		
		<p><label for="<?php echo $this->get_field_id('show_content'); ?>"><?php _e('Content Type', 'themedy'); ?>:</label>
		<select id="<?php echo $this->get_field_id('show_content'); ?>" name="<?php echo $this->get_field_name('show_content'); ?>">
			<option value="content" <?php selected('content' , $instance['show_content'] ); ?>><?php _e('Show Content', 'themedy'); ?></option>
			<option value="excerpt" <?php selected('excerpt' , $instance['show_content'] ); ?>><?php _e('Show Excerpt', 'themedy'); ?></option>
			<option value="content-limit" <?php selected('content-limit' , $instance['show_content'] ); ?>><?php _e('Show Content Limit', 'themedy'); ?></option>
			<option value="" <?php selected('' , $instance['show_content'] ); ?>><?php _e('No Content', 'themedy'); ?></option>
		</select>
		
		<br /><label for="<?php echo $this->get_field_id('content_limit'); ?>"><?php _e('Limit content to', 'themedy'); ?></label> <input type="text" id="<?php echo $this->get_field_id('image_alignment'); ?>" name="<?php echo $this->get_field_name('content_limit'); ?>" value="<?php echo esc_attr(intval($instance['content_limit'])); ?>" size="3" /> <?php _e('characters', 'themedy'); ?></p>
		
		<p><label for="<?php echo $this->get_field_id('more_text'); ?>"><?php _e('More Text (if applicable)', 'themedy'); ?>:</label>
		<input type="text" id="<?php echo $this->get_field_id('more_text'); ?>" name="<?php echo $this->get_field_name('more_text'); ?>" value="<?php echo esc_attr($instance['more_text']); ?>" /></p>
		
		</div>
		<div style="background: #f1f1f1; border: 1px solid #DDD; padding: 10px 10px 0px 10px; margin-top: 10px;">
		
		<p><?php _e('To display an unordered list of more posts from this category, please fill out the information below', 'themedy'); ?>:</p>
		
		<p><label for="<?php echo $this->get_field_id('extra_title'); ?>"><?php _e('Title', 'themedy'); ?>:</label>
		<input type="text" id="<?php echo $this->get_field_id('extra_title'); ?>" name="<?php echo $this->get_field_name('extra_title'); ?>" value="<?php echo esc_attr($instance['extra_title']); ?>" style="width:95%;" /></p>
		
		<p><label for="<?php echo $this->get_field_id('extra_num'); ?>"><?php _e('Number of Posts to Show', 'themedy'); ?>:</label>
		<input type="text" id="<?php echo $this->get_field_id('extra_num'); ?>" name="<?php echo $this->get_field_name('extra_num'); ?>" value="<?php echo esc_attr($instance['extra_num']); ?>" size="2" /></p>
		
		</div>
		<div style="background: #f1f1f1; border: 1px solid #DDD; padding: 10px 10px 0px 10px; margin: 10px 0;">
		
		<p><input id="<?php echo $this->get_field_id('more_from_category'); ?>" type="checkbox" name="<?php echo $this->get_field_name('more_from_category'); ?>" value="1" <?php checked(1, $instance['more_from_category']); ?>/> <label for="<?php echo $this->get_field_id('more_from_category'); ?>"><?php _e('Show Category Archive Link', 'themedy'); ?></label></p>
		
		<p><label for="<?php echo $this->get_field_id('more_from_category_text'); ?>"><?php _e('Link Text', 'themedy'); ?>:</label>
		<input type="text" id="<?php echo $this->get_field_id('more_from_category_text'); ?>" name="<?php echo $this->get_field_name('more_from_category_text'); ?>" value="<?php echo esc_attr($instance['more_from_category_text']); ?>" style="width:95%;" /></p>
		
		</div>
		
	</div>
			
	<?php 
	}
}

//
// WIDGET SHORTCODES
//

add_shortcode( 'post_date', 'themedy_post_date_shortcode' );
/**
 * This function produces the date of post publication
 * 
 * @since Unknown
 * 
 * @example <code>[post_date]</code> is the default usage
 * @example <code>[post_date format="F j, Y" before="<em>" after="</em>"]</code>
 * 
 * @param array $atts Shortcode attributes
 * @return string 
 */
function themedy_post_date_shortcode( $atts ) {

	$defaults = array(
		'format' => get_option( 'date_format' ),
		'before' => '',
		'after'  => '',
		'label'  => ''
	);
	$atts = shortcode_atts( $defaults, $atts );

	$display = ( 'relative' == $atts['format'] ) ? human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'themedy' ) : get_the_time( $atts['format'] );

	$output = sprintf( '<span class="date published time" title="%5$s">%1$s%3$s%4$s%2$s</span> ', $atts['before'], $atts['after'], $atts['label'], $display, get_the_time( 'Y-m-d\TH:i:sO' ) );

	return apply_filters( 'themedy_post_date_shortcode', $output, $atts );

}

add_shortcode( 'post_author', 'themedy_post_author_shortcode' );
/**
 * This function produces the author of the post (display name)
 * 
 * @since Unknown
 * 
 * @example <code>[post_author]</code> is the default usage
 * @example <code>[post_author before="<em>" after="</em>"]</code>
 * 
 * @param array $atts Shortcode attributes
 * @return string 
 */
function themedy_post_author_shortcode( $atts ) {

	$defaults = array(
		'before' => '',
		'after'  => ''
	);
	$atts = shortcode_atts( $defaults, $atts );

	$output = sprintf( '<span class="author vcard">%2$s<span class="fn">%1$s</span>%3$s</span>', esc_html( get_the_author() ), $atts['before'], $atts['after'] );

	return apply_filters( 'themedy_post_author_shortcode', $output, $atts );

}

add_shortcode( 'post_author_link', 'themedy_post_author_link_shortcode' );
/**
 * This function produces the author of the post (link to author URL)
 * 
 * @since Unknown
 * 
 * @example <code>[post_author_link]</code> is the default usage
 * @example <code>[post_author_link before="<em>" after="</em>"]</code>
 * 
 * @param array $atts Shortcode attributes
 * @return string 
 */
function themedy_post_author_link_shortcode( $atts ) {

	$defaults = array(
		'nofollow' => FALSE,
		'before'   => '',
		'after'    => ''
	);
	$atts = shortcode_atts( $defaults, $atts );

	$author = get_the_author();

	//	Link?
	if ( get_the_author_meta( 'url' ) )

		//	Build the link
		$author = '<a href="' . get_the_author_meta( 'url' ) . '" title="' . esc_attr( sprintf( __( "Visit %s&#8217;s website" ), $author) ) . '" rel="external">' . $author . '</a>';

	$output = sprintf( '<span class="author vcard">%2$s<span class="fn">%1$s</span>%3$s</span>', $author, $atts['before'], $atts['after'] );

	return apply_filters( 'themedy_post_author_link_shortcode', $output, $atts );

}

add_shortcode( 'post_author_posts_link', 'themedy_post_author_posts_link_shortcode' );
/**
 * This function produces the author of the post (link to author archive)
 * 
 * @since Unknown
 * 
 * @example <code>[post_author_posts_link]</code> is the default usage
 * @example <code>[post_author_posts_link before="<em>" after="</em>"]</code>
 * 
 * @param array $atts Shortcode attributes
 * @return string 
 */
function themedy_post_author_posts_link_shortcode( $atts ) {

	$defaults = array(
		'before' => '',
		'after'  => ''
	);
	$atts = shortcode_atts( $defaults, $atts );

	// Darn you, WordPress!
	ob_start();
	the_author_posts_link();
	$author = ob_get_clean();

	$output = sprintf( '<span class="author vcard">%2$s<span class="fn">%1$s</span>%3$s</span>', $author, $atts['before'], $atts['after'] );

	return apply_filters( 'themedy_post_author_posts_link_shortcode', $output, $atts );

}

add_shortcode( 'post_comments', 'themedy_post_comments_shortcode' );
/**
 * This function produces the comment link
 * 
 * @since Unknown
 * 
 * @example <code>[post_comments]</code> is the default usage
 * @example <code>[post_comments zero="No Comments" one="1 Comment" more="% Comments"]</code>
 * 
 * @param array $atts Shortcode attributes
 * @return string 
 */
function themedy_post_comments_shortcode( $atts ) {

	$defaults = array(
		'zero'        => __( 'Leave a Comment', 'themedy' ),
		'one'         => __( '1 Comment', 'themedy' ),
		'more'        => __( '% Comments', 'themedy' ),
		'hide_if_off' => 'enabled',
		'before'      => '',
		'after'       => ''
	);
	$atts = shortcode_atts( $defaults, $atts );



	// Darn you, WordPress!
	ob_start();
	comments_number( $atts['zero'], $atts['one'], $atts['more'] );
	$comments = ob_get_clean();

	$comments = sprintf( '<a href="%s">%s</a>', get_comments_link(), $comments );

	$output = sprintf( '<span class="post-comments">%2$s%1$s%3$s</span>', $comments, $atts['before'], $atts['after'] );

	return apply_filters( 'themedy_post_comments_shortcode', $output, $atts );

}