<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( ! current_user_can( 'activate_plugins' ) ) {
	return;
}

// Uninstallation actions here

// Delete table <<=== Only after the user has made it clear that is what they want!
//wga_delete_db_table();

delete_option('wga-collect-email-list-activated');
delete_option('wga_db_version');
           

function wga_delete_db_table() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'wga_contact_list';
	//echo 'table name is: ' . $table_name .PHP_EOL;
	$sql = "DROP TABLE IF EXISTS " . $table_name;
	$wpdb->query($sql);
	
	
}
//register_deactivation_hook( __FILE__, 'wga_delete_db_table' );

?>