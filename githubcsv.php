<?php
/**
 * Export Data to CSV file
 * Could be used in WordPress plugin or theme
 */

// A sample link to Download CSV, could be placed somewhere in plugin settings page
?>
<a href="<?php echo admin_url( 'admin.php?page=wga-collect-email-list.php' ) ?>&action=download_csv&_wpnonce=<?php echo wp_create_nonce( 'download_csv' )?>" class="page-title-action"><?php _e('Export to CSV','my-plugin-slug');?></a>

<?php
// Add action hook only if action=download_csv
if ( isset($_GET['action'] ) && $_GET['action'] == 'download_csv' )  {
	// Handle CSV Export
	echo wga_console_log( __LINE__.' WGA:: FilterRecords:: "'.$filterrecords.'"' );
    if (true) {
        csv_export();
    }else{
	    add_action( 'myhook', 'csv_export' );
        do_action('myhook');
    }
}

function csv_export() {

	echo wga_console_log( __LINE__.' WGA:: FilterRecords:: "'.$filterrecords.'"' );
    // Check for current user privileges 
    if( !current_user_can( 'manage_options' ) ){ return false; }

    // Check if we are in WP-Admin
    if( !is_admin() ){ return false; }

    // Nonce Check
    $nonce = isset( $_GET['_wpnonce'] ) ? $_GET['_wpnonce'] : '';
    if ( ! wp_verify_nonce( $nonce, 'download_csv' ) ) {
        die( 'Security check error' );
    }
	echo wga_console_log( __LINE__.' WGA:: FilterRecords:: "'.$filterrecords.'"' );
    //ob_start();

    $domain = $_SERVER['SERVER_NAME'];
    $filename = 'users-' . $domain . '-' . time() . '.csv';
    
    $header_row = array(
        'Email',
        'Name'
    );
    $data_rows = array();
    global $wpdb;
    $sql = 'SELECT * FROM ' . $wpdb->users;
    $users = $wpdb->get_results( $sql, 'ARRAY_A' );
    foreach ( $users as $user ) {
        $row = array(
            $user['user_email'],
            $user['user_name']
        );
        $data_rows[] = $row;
    }
    ob_start();
    //ob_end_clean ();
    $fh = @fopen( 'php://output', 'w' );
    fprintf( $fh, chr(0xEF) . chr(0xBB) . chr(0xBF) );
    header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
    header( 'Content-Description: File Transfer' );
    header( 'Content-type: text/csv' );
    header( "Content-Disposition: attachment; filename={$filename}" );
    header( 'Expires: 0' );
    header( 'Pragma: public' );
    fputcsv( $fh, $header_row );
    foreach ( $data_rows as $data_row ) {
        fputcsv( $fh, $data_row );
    }
    fclose( $fh );
    
    ob_end_flush();
    
    die();
}