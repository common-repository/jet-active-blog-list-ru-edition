<?php
/*
Plugin Name: Jet Blog List
URI: http://milordk.ru
Author: Jettochkin
Author URI: http://milordk.ru
Description: ru-Вывод списка блогов с сортировкой по последнему обновлению (последняя активность на блоге). en-Provides a list of blogs sorted by last update (the last activity on the blog).
Tags: BuddyPress, Wordpress MU, blog
Version: 0.1.2
*/
?>
<?

function jetget_blog_list( $start = 0, $num = 6, $deprecated = '' ) {
	global $wpdb;
	$blogs = get_site_option( "blog_list" );
	$update = false;
        $limit = $num+1;
	if( is_array( $blogs ) ) {
		if( ( $blogs['time'] + 60 ) < time() ) { // cache for 60 seconds.
			$update = true;
		}
	} else {
		$update = true;
	}

	if( $update == true ) {
		unset( $blogs );
		$blogs = $wpdb->get_results( $wpdb->prepare("SELECT blog_id, domain, path FROM $wpdb->blogs WHERE site_id = %d AND public = '1' AND archived = '0' AND mature = '0' AND spam = '0' AND deleted = '0' ORDER BY last_updated DESC LIMIT ".$limit, $wpdb->siteid), ARRAY_A );
		foreach ( (array) $blogs as $details ) {
			$blog_list[ $details['blog_id'] ] = $details;
			$blog_list[ $details['blog_id'] ]['postcount'] = $wpdb->get_var( "SELECT COUNT(ID) FROM " . $wpdb->base_prefix . $details['blog_id'] . "_posts WHERE post_status='publish' AND post_type='post'" );
		}
		unset( $blogs );
		$blogs = $blog_list;
		update_site_option( "blog_list", $blogs );
	}
	if( false == is_array( $blogs ) )
		return array();

	if( $num == 'all' ) {
		return array_slice( $blogs, $start, count( $blogs ) );
	} else {
		return array_slice( $blogs, $start, $num );
	}
}

class BlogList extends WP_Widget {
	function BlogList() {
		parent::WP_Widget(false, $name = __('BlogList','bloglist') );
	}

	function widget($args, $instance) {
		extract( $args );
		echo $before_widget;
		echo $before_title . $instance['title'] . $after_title;
		#echo '<div class="widget"><h2 class="widgettitle">'.$instance['title'].'_0</h2>'. $widget_title;
		$blog_list = jetget_blog_list(1, $instance['number'], true);
        $ii = 0;
echo '<div style="margin-top:0; padding-top:0;">';
echo '<table width="100%">';
echo '<tr>';
foreach ($blog_list AS $blog) {
$ii = $ii+1;
$blog_details = get_blog_details($blog['blog_id']);
$jblogurl = get_blogaddress_by_id($blog['blog_id']);
echo ' <td width="50%"><p style="background:#F8F8F8;margin:0px;padding-left:20px; border-left: 1px #EEE solid; margin-bottom: 10px;"><a href="'.$jblogurl.'">'.$blog_details->blogname.'</a></p>';
echo '</td>';
if ($ii == 2) {
$ii = 0;
echo '</tr><tr>';
}
}
echo '</table></div>';

echo '<p align="right" style="margin:0px; font-size:90%;"><a href="/blogs">&diams; '.$instance['aftertitle'].'</a></p>';

	echo $after_widget;
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['aftertitle'] = strip_tags($new_instance['aftertitle']);
		$instance['number'] = strip_tags($new_instance['number']);
		return $instance;
	}

	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'number'=>''));
		$title = strip_tags( $instance['title']); 
		$number = strip_tags( $instance['number']);
        $aftertitle = strip_tags( $instance['aftertitle']); 	?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'buddypress'); ?>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo attribute_escape( stripslashes( $title ) ); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('aftertitle'); ?>"><?php _e('Aftertitle:', 'buddypress'); ?>
		<input class="widefat" id="<?php echo $this->get_field_id('aftertitle'); ?>" name="<?php echo $this->get_field_name( 'aftertitle' ); ?>" type="text" value="<?php echo attribute_escape( stripslashes( $aftertitle ) ); ?>" /></label></p>		
		<p><?php _e('Blog count', 'buddypress'); ?></p>
		<p><input class="widefat" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo attribute_escape( stripslashes( $number ) ); ?>" /></label></p>
	<?php
	}
}
add_action('widgets_init', create_function('', 'return register_widget("BlogList");'));

?>