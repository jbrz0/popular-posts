<?php 

/*
Plugin Name: Popular posts plugin for widgets
Plugin URI:  http://justinbrazeau.com
Description: Plugin for displaying popular posts
Version:     0.1
Author:      Justin Brazeau
Author URI:  http://justinbrazeau.com
License:     GPL2
*/

function my_popular_post_views($postID) {
	$total_key = 'views';
	$total = get_post_meta($postID, $total_key, true);
	if ($total == '') {
		delete_post_meta($postID, $total_key);
		add_post_meta($postID, $total_key, '0');
	}
	else {
		$total++;
		update_post_meta($postID, $total_key, $total);
	}
}

function my_count_popular_posts($post_id) {
	if (! is_single()) return;
	if (! is_user_logged_in()) {
		if (empty($post_id)) {
			global $post;
			$post_id = $post -> ID;
		}
		my_popular_post_views($post_id);
	}
}

add_action('wp_head', 'my_count_popular_posts');

function my_add_views_column($defaults) {
	$defaults['post_views'] = 'View Count';
	return $defaults;
}

add_filter('manage_posts_columns', 'my_add_views_column');

function my_display_views($column_name) {
	if ($column_name === 'post_view') {
		echo (int) get_post_meta(get_the_ID(), 'views', true);
	}
}

add_action('manage_posts_custom_column', 'my_display_views', 5, 2);

class popular_posts extends WP_Widget {

	function __construct() {
		parent::__construct(
			'popular_posts',
			__( 'Popular Posts', 'text_domain' ),
			array( 'description' => __( 'Displays the 5 most popular posts', 'text_domain' ), )
		);
	}

	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
		
		$query_args = array(
			'post_type' => 'post',
			'posts_per_page' => 5,
			'meta_key' => 'views',
			'orderby' => 'meta_value_num',
			'order' => 'DESC',
			'ignore_sticky_posts' => true
			);

		$the_query = new WP_Query( $query_args );

		if ( $the_query->have_posts() ) {
			echo '<ul>';
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				echo '<li>';
				echo '<a href="' . get_the_permalink() . '" rel="bookmark">';
				echo get_the_title();
				echo '(' . get_post_meta(get_the_ID(), 'views', true) . ')';
				echo '</a>';
				echo '</li>';
			}
			echo '</ul>';
			
		} else {
			// no posts found
		}

		wp_reset_postdata();

	}

	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Popular Posts', 'text_domain' );
		?>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( esc_attr( 'Title:' ) ); ?></label> 
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php 
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

} // class popular_posts

function register_popular_posts() {
    register_widget( 'Popular_Posts' );
}
add_action( 'widgets_init', 'register_popular_posts' );

