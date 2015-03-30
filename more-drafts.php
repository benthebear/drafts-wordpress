<?php
/**
 * Plugin Name: More Drafts
 * Plugin URI: 
 * Description: Adds a Link to the Drafts Page to the Admin Bar, and a Dashboard Widget with more drafts
 * Version: 1.0
 * Author: Benjamin Birkenhake <benjamin.birkenhake@palasthotel.de>
 * Author URI: http://palacehotel.company
 * License: GPL2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * 
 * __
 * The devils teeth laughing in the wind.
 * 
 */

// Security first
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Add some action 
add_action( 'admin_bar_menu', 'drafts_admin_bar_menu', 95 );
add_action( 'wp_dashboard_setup', 'drafts_add_dashboard_widgets' );


/**
 * Add the Button to the Drafts to the Admin Menu.	
 */
function drafts_admin_bar_menu() {
  global $wp_admin_bar;
  $wp_admin_bar->add_menu( array( 'id' => 'wp-drafts', 'title' => __( 'Drafts' ), 'href' => get_admin_url('', 'edit.php?post_status=draft'), ) );
}


/**
 * Add a widget to the dashboard.
 *
 * This function is hooked into the 'wp_dashboard_setup' action below.
 */
function drafts_add_dashboard_widgets() {

	wp_add_dashboard_widget(
                 'drafts_extended_drafts_widget',     // Widget slug.
                 'More Recent Drafts',         // Title.
                 'drafts_extended_dashboard_widget_function', // Display function.
                 'drafts_extended_dashboard_widget_control' // Controll Function 
        );	
}


/**
 * Create the function to output the contents of our Dashboard Widget.
 */
function drafts_extended_dashboard_widget_function($drafts = false) {

	$widgets = get_option( 'dashboard_widget_options' );
	$total_items = 5;
	if(isset( $widgets['dashboard_extended_drafts'] ) && isset( $widgets['dashboard_extended_drafts']['items'] )){
		$total_items = 	$widgets['dashboard_extended_drafts']['items'];
	}

	if ( !$drafts ) {
		$drafts_query = new WP_Query( array(
			'post_type' => 'post',
			'post_status' => 'draft',
			'author' => $GLOBALS['current_user']->ID,
			'posts_per_page' => $total_items,
			'orderby' => 'modified',
			'order' => 'DESC'
		) );
		$drafts =& $drafts_query->posts;
	}

	if ( $drafts && is_array( $drafts ) ) {
		$list = array();
		foreach ( $drafts as $draft ) {
			$url = get_edit_post_link( $draft->ID );
			$title = _draft_or_post_title( $draft->ID );
			$item = "<h4><a href='$url' title='" . sprintf( __( 'Edit &#8220;%s&#8221;' ), esc_attr( $title ) ) . "'>" . esc_html($title) . "</a> <abbr title='" . get_the_time(__('Y/m/d g:i:s A'), $draft) . "'>" . get_the_time( get_option( 'date_format' ), $draft ) . '</abbr></h4>';
			if ( $the_content = wp_trim_words( $draft->post_content, 10 ) )
				$item .= '<p>' . $the_content . '</p>';
			$list[] = $item;
		}
		$output = "";
		$output .= "<ul>\n";
		$output .= "<li>".join( "</li>\n<li>", $list )."</li>\n";
		$output .= "</ul>\n";
		$output .= '<p class="textright"><a href="edit.php?post_status=draft" >'.__('View all')."</a></p>\n";
		print $output;
	} else {
		_e('There are no drafts at the moment');
	}
}

/**
 * The more recent comments dashboard widget control.
 *
 * @since 3.0.0
 */
function drafts_extended_dashboard_widget_control() {
	if(!$widget_options = get_option('dashboard_widget_options')){
		$widget_options = array();
  	}

	if(!isset($widget_options['dashboard_extended_drafts'])){
		$widget_options['dashboard_recent_comments'] = array(); 
	}

	if ($_SERVER['REQUEST_METHOD'] == "POST "&& isset($_POST['widget-extended-drafts'])){
		$number = absint( $_POST['widget-extended-drafts']['items'] );
		$widget_options['dashboard_extended_drafts']['items'] = $number;
		update_option( 'dashboard_widget_options', $widget_options );
	}

	$number = isset( $widget_options['dashboard_extended_drafts']['items'] ) ? (int) $widget_options['dashboard_extended_drafts']['items'] : '';

	echo '<p><label for="drafts-number">' . __('Number of drafts to show:') . '</label>';
	echo '<input id="drafts-number" name="widget-extended-drafts[items]" type="text" value="' . $number . '" size="3" /></p>';
}




?>