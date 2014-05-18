<?php
/**
 * Plugin Name: Killer Apps Table Metaboxes
 * Description:  Adding post metadata via table metabox.
 * Version: 1.0
 * Author: Zosia Sobocinska
 * Author URI: http://www.killeapps.pl
 * License: GPLv2 or later
 */

require_once 'metabox.php';
add_action('admin_enqueue_scripts','killerapps_table_metaboxes_admin_enqueue');
function killerapps_table_metaboxes_admin_enqueue() {
	wp_enqueue_media();
	wp_enqueue_style( 'killerapps-table-metaboxes', plugins_url( '/css/table-metaboxes.css', __FILE__ ) );
	wp_enqueue_script( 'killerapps-table-metaboxes', plugins_url( '/js/table-metaboxes.js', __FILE__), array('jquery'));
	wp_enqueue_script( 'jquery-slugify', plugins_url( '/js/slugify/slugify.js', __FILE__), array('jquery'));
	wp_enqueue_script( 'jquery-json', plugins_url( '/js/jquery.json-2.4.min.js', __FILE__), array('jquery'));
}
