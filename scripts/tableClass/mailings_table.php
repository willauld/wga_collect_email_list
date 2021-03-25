<?php

/*
WGA_mailings_list dirived from WP_List_Table Class
Plugin URI: https://www.sitepoint.com/using-wp_list_table-to-create-wordpress-admin-tables/
Description: Based on Demo on how WP_List_Table Class works
Version: 1.0
Author: Will Auld
*/

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WGA_Mailings_List extends WP_List_Table {

	/** Class constructor */
	public function __construct() {

		parent::__construct( [
			'singular' => __( 'Mailing', 'sp' ), //singular name of the listed records
			'plural'   => __( 'Mailings', 'sp' ), //plural name of the listed records
			'ajax'     => false, //does this table support ajax?
		] );

	}
    protected function get_views() { 
        $status_links = array(
            "All"       => __("<a href='#'>All</a>",'my-plugin-slug'),
            "Completed" => __("<a href='#'>Completed</a>",'my-plugin-slug'),
            "ToBeDone"   => __("<a href='#'>To Be Done</a>",'my-plugin-slug'),
        );
        return $status_links;
    }

	/**
	 * Display mailings edit form  
	 *
	 * @param object $record db row
	 *
	 * @return mixed
	 */
	public static function display_record_edit_form($mailings_id = -1){
		global $wpdb;
	    //
	    // Form for creating a "mailing"
	    //
	    if ($mailings_id <= 0) {
	        // new mailings
	        $is_verified_chk = "true";
	        $is_spam_chk = "false";
	        $is_unsub_chk = "false";
	        $mailing_m_id = "";
    		$start = current_time( 'mysql' );
	    }else {
	        // edit a mailing
			$sql = "SELECT * FROM {$wpdb->prefix}wga_mailings_list WHERE mailings_id = $mailings_id";
			$result = $wpdb->get_row( $sql );
	
	        if ($result){
	            $mailing_m_id = $result->mailings_message_id;
	            $is_verified_chk = $result->mailings_verified;
	            $is_spam_chk = $result->mailings_spam;
	            $is_unsub_chk = $result->mailings_unsubscribed;
	            $start = $result->mailings_start_date;
	            //$created = $result->mailings_created_at;
	        }else{
	            $mailing_m_id = -2;
	        }
	    }
	
	    echo '<div style="margin: 3em;  padding: 2em; border: 2px solid #262661; border-radius: 5px; width: 50%; " >';

		$midstr = $mailings_id<=0?"TBD":$mailings_id;
		echo '<h2>Mailings ID: '.$midstr.'</h2>';

	    echo '<form method="post">';

	    //echo '<input name="wga_mailing_edit_id" type="hidden" value="'.$mailings_id.'">';
	    echo '<input name="action" type="hidden" value="saveMailing">';
	
	    echo "<label for='messageid' >Message ID</label>";
	    echo '<input id="messageid" name="mailing_message_id" type="number" value="'.$mailing_m_id.'" >';
	    //
	    // Radio button for is_verified
	    //
	    echo '<h2>Filters:</h2>';
		echo '<div >';//container of radio
		echo '  <em style=" display:inline-block; width:6em;">is_verified?</em>';
	
		echo '    <label for="vany" class="radio-is_verified">';
	    $t1 = ($is_verified_chk == "any") ? "checked" : "";
		echo '      <input id="vany" type="radio" name="is_verified_chk" value="any" '. $t1 .' >Any';
		echo '    </label>';
	
		echo '    <label for="vvalue_true" class="radio-is_verified">';
	    $t1 = ($is_verified_chk == "true") ? "checked" : "";
		echo '      <input id="vvalue_true" type="radio" name="is_verified_chk" value="true" '.$t1.' >True';
		echo '    </label>';
	
		echo '    <label for="vvalue_false" class="radio-is_verified">';
	    $t1 = ($is_verified_chk == "false") ? "checked" : "";
		echo '      <input id="vvalue_false" type="radio" name="is_verified_chk" value="false" '.$t1.'>False';
		echo '    </label>';
	
		echo '</div><br>';//container of radio
	
		echo '<div >';//container of radio
		echo '  <em style=" display:inline-block; width:6em;">is_SPAM?</em>';
	
		echo '    <label for="sany" class="radio-is_spam">';
	    $t1 = ($is_spam_chk == "any") ? "checked" : "";
		echo '      <input id="sany" type="radio" name="is_spam_chk" value="any" '. $t1 .' >Any';
		echo '    </label>';
	
		echo '    <label for="svalue_true" class="radio-is_spam">';
	    $t1 = ($is_spam_chk == "true") ? "checked" : "";
		echo '      <input id="svalue_true" type="radio" name="is_spam_chk" value="true" '.$t1.' >True';
		echo '    </label>';
	
		echo '    <label for="svalue_false" class="radio-is_spam">';
	    $t1 = ($is_spam_chk == "false") ? "checked" : "";
		echo '      <input id="svalue_false" type="radio" name="is_spam_chk" value="false" '.$t1.'>False';
		echo '    </label>';
	
		echo '</div><br>';//container of radio
	
		echo '<div >';//container of radio
		echo '  <em style=" display:inline-block; width:6em;">unsubscribed?</em>';
	
		echo '    <label for="uany" class="radio-unsub">';
	    $t1 = ($is_unsub_chk == "any") ? "checked" : "";
		echo '      <input id="uany" type="radio" name="is_unsub_chk" value="any" '. $t1 .' >Any';
		echo '    </label>';
	
		echo '    <label for="uvalue_true" class="radio-unsub">';
	    $t1 = ($is_unsub_chk == "true") ? "checked" : "";
		echo '      <input id="uvalue_true" type="radio" name="is_unsub_chk" value="true" '.$t1.' >True';
		echo '    </label>';
	
		echo '    <label for="uvalue_false" class="radio-unsub">';
	    $t1 = ($is_unsub_chk == "false") ? "checked" : "";
		echo '      <input id="uvalue_false" type="radio" name="is_unsub_chk" value="false" '.$t1.'>False';
		echo '    </label>';
	
		echo '</div><br>';//container of radio
	
	    echo '<label for="startdate" >Start Date for Mailing</label>';
	    $start2 = date("Y-m-d", strtotime($start));
	    echo '<input id="startdate" name="date_to_start" type="date" value="'.$start2.'" >';
	
	    submit_button("Submit Mailing");
	    submit_button("Cancel"); //FIXME
	    echo '</form>';
	
	    echo '</div>';
    }

	/**
	 * Retrieve email data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_mailings_list( $per_page = 5, $page_number = 1 ) {

		global $wpdb;

		$sql = "SELECT * FROM {$wpdb->prefix}wga_mailings_list";

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		}

		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;


		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

		return $result;
	}

	/**
	 * get an mailings record.
	 *
	 * @param int $id customer ID
	 */
	public static function get_mailings_record( $id ) {
		global $wpdb;

		$sql = "SELECT * FROM {$wpdb->prefix}wga_mailings_list WHERE mailings_id = $id";
		$result = $wpdb->get_row( $sql );
        return $result;
	}

	/**
	 * Delete an email record.
	 *
	 * @param int $id customer ID
	 */
	public static function delete_mailings_record( $id ) {
		global $wpdb;

		$wpdb->delete(
			"{$wpdb->prefix}wga_mailings_list",
			[ 'mailings_id' => $id ],
			[ '%d' ]
		);
	}

	/**
	 * Edit update mailings record.
	 *
	 * @param int $id mailings ID
	 */
	//function wga_insert_update_mailing($id, $mess_id, $verified, $spam, $unsub, $start)
	public function edit_update_mailings_record($id, $mess_id, $verified, $spam, $unsub, $start) {
	    global $wpdb;
	
	    //echo 'INSERTING new record with no mailings_id yet';
	    $now = current_time( 'mysql' );
		$table_name = $wpdb->prefix . 'wga_mailings_list';
	    if ($id <= 0) {
			$result = $wpdb->insert( 
				$table_name, 
				array( 
				    'mailings_message_id' => $mess_id,
		            'mailings_verified' => $verified,
		            'mailings_spam' => $spam,
		            'mailings_unsubscribed' => $unsub,
		            'mailings_start_date' =>  $start,
				    'mailings_created_at' =>  $now, 
				) 
		    );
	        if ($result) {
	            $sql_cmd = $wpdb->prepare("SELECT mailings_id FROM {$wpdb->prefix}wga_mailings_list WHERE (mailings_message_id = %s AND mailings_start_date = %s AND mailings_created_at = %s)", $mess_id, $start, $now);
	            $results = $wpdb->get_results( $sql_cmd );
	            $result = $results[0]->mailings_id;
	        }
	    } else {
	        // update
			$rep = $wpdb->update(
				"{$wpdb->prefix}wga_mailings_list",
				array( // data
				    'mailings_message_id' => $mess_id,
		            'mailings_verified' => $verified,
		            'mailings_spam' => $spam,
		            'mailings_unsubscribed' => $unsub,
		            'mailings_start_date' =>  $start,
				    'mailings_updated_at' =>  $now, 
				),
				array( //where
					'mailings_id' => $id,
				)
	        );
	        $result = $id;
	    }
	    return $result;
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}wga_mailings_list";

		return $wpdb->get_var( $sql );
	}


	/** Text displayed when no data is available */
	public function no_items() {
		_e( 'No mailings records avaliable.', 'sp' );
	}


	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'id':
				return $item[ 'mailings_id' ];
			case 'Message id':
				return $item[ 'mailings_message_id' ];
			case 'F verified':
				return $item[ 'mailings_verified' ];
			case 'F SPAM':
				return $item[ 'mailings_spam' ];
            case 'F unsubscribed':
				return $item[ 'mailings_unsubscribed' ];
			case 'Start date':
				return $item[ 'mailings_start_date' ];
			case 'Created at':
				return $item[ 'mailings_created_at' ];
			case 'Updated at':
				return $item[ 'mailings_updated_at' ];
				//return $item[ 'message_'.$column_name ];
			case 'Sent to':
				return $item[ 'mailings_sent_to' ];
			case 'Completed':
				return $item[ 'mailings_completed' ];
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-ids[]" value="%s" />', $item['mailings_id']
		);
	}


	/**
	 * Method for id column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_id( $item ) {

		$edit_nonce = wp_create_nonce( 'sp_edit_mailings_record' );
		$domailing_nonce = wp_create_nonce( 'sp_do_mailings_record' );
		$delete_nonce = wp_create_nonce( 'sp_delete_mailings_record' );

		$title = '<strong>' . $item['mailings_id'] . '</strong>';

		$actions = [
            'edit' => sprintf( '<a href="?page=%s&action=%s&mailings_record=%s&_wpnonce=%s">Edit</a>', esc_attr( $_REQUEST['page'] ), 'edit', absint( $item['mailings_id'] ), $edit_nonce ),
            'domailing' => sprintf( '<a href="?page=%s&action=%s&mailings_record=%s&_wpnonce=%s">Do Mailing</a>', esc_attr( $_REQUEST['page'] ), 'domailing', absint( $item['mailings_id'] ), $domailing_nonce ),
			'delete' => sprintf( '<a href="?page=%s&action=%s&mailings_record=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['mailings_id'] ), $delete_nonce ),
		];

		return $title . $this->row_actions( $actions );
	}


	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = [
			'cb'      => '<input type="checkbox" />',
			'id'    => __( 'id', 'sp' ),
		    'Message id' => 'Message id',
            'F verified' => 'F verified',
            'F SPAM' => 'F SPAM',
            'F unsubscribed' => 'F unsubscribed',
            'Start date' => 'Start date',
		    'Created at' => 'Created at',
		    'Updated at' => 'Updated at', 
            'Sent to' => 'Sent to',
            'Completed' => 'Completed',
		];

		return $columns;
	}


	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'id' => array( 'mailings_id', true ),
			'Message id' => array( 'mailings_message_id', true ),
			'F verified' => array( 'mailings_verified', true ),
			'F SPAM' => array( 'mailings_spam', true ),
			'F unsubscribed' => array( 'mailings_unsubscribed', true ),
			'Start date' => array( 'mailings_start_date', true ),
			'Created at' => array( 'mailings_created_at', true ),
			'Updated at' => array( 'mailings_updated_at', true ),
			'Sent to' => array( 'mailings_sent_to', true ),
			'Completed' => array( 'mailings_completed', true ),
		);

		return $sortable_columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [
			'bulk-delete' => 'Delete',
		];

		return $actions;
	}


	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		$this->_column_headers = $this->get_column_info();

		/** Process bulk action */
		$this->process_bulk_action();

		$per_page     = $this->get_items_per_page( 'records_per_page', 5 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );

		$this->items = self::get_mailings_list( $per_page, $current_page );
	}

	public function process_bulk_action() {
		//Detect when a bulk action is being triggered...
				
        /**/ 
        echo '<pre>';
        echo '<h2> $_REQUEST() </h2>';
        print_r($_REQUEST);
        echo '<h2> $_GET() </h2>';
        print_r($_GET);
	    print_r($this->current_action());
        echo '</pre>';
        /**/

		if ( 'edit' === $this->current_action() && empty($_REQUEST['paged'])) {
			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );
			if ( ! wp_verify_nonce( $nonce, 'sp_edit_mailings_record' ) ) {
				die( 'Go get a life script kiddies' );
			}
			else {
				$record_id = $_REQUEST['mailings_record'];
				//$record = self::get_mailings_record( $record_id );
                //echo 'id: '.$record->id;
                self::display_record_edit_form($record_id);
			}
            // SAVE operation is handled by on-page php code.
        }elseif ( 'saveMailing' === $this->current_action() ) {
            if ($_POST["submit"] == 'Submit Mailing') {
				if ( !empty($_REQUEST['mailings_record']) &&
                isset($_REQUEST['mailings_record']) ) {
                	$id = $_REQUEST['mailings_record'];
				}else{
					$id = -1;
				}
                $mess_id = $_POST['mailing_message_id'];
                $verified = $_POST['is_verified_chk'];
                $spam = $_POST['is_spam_chk'];
                $unsub = $_POST['is_unsub_chk'];
                $start = $_POST['date_to_start'];
                //$created = $_POST['wga_created'];
                //$mail_id = wga_insert_update_mailing($id, $mess_id, $verified, $spam, $unsub, $start); // FIXME move this function into the class
				$mail_id = $this->edit_update_mailings_record($id, $mess_id, $verified, $spam, $unsub, $start);
			} // else do nothing if "cancel" button
			/* trying to get rid of the url get strings
            notecho '<script type="text/javascript">
                    location.reload();
                </script>';
			nocache_headers();
			$url = site_url(). '/' . esc_url( $_SERVER['REQUEST_URI'] );//.'/#comboform"
			wp_safe_redirect( $url );
			exit;
			*/
        }elseif ( 'domailing' === $this->current_action() ) {
			// process the mailings record by sending an email with the specified 
			// message to every email in the contact_list that matches the filters.
			// First verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );
			if ( ! wp_verify_nonce( $nonce, 'sp_do_mailings_record' ) ) {
				die( 'Go get a life script kiddies' );
			} else {
				$record_id = $_REQUEST['mailings_record'];
				echo 'ready to call domailing for record '.$record_id;
				// FIXME will want to make this function part of the class
            	$str = wga_send_mailings_email($record_id);
				print_r($str);
			}
        }elseif ( 'delete' === $this->current_action() ) {
			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );
			if ( ! wp_verify_nonce( $nonce, 'sp_delete_mailings_record' ) ) {
				die( 'Go get a life script kiddies' );
			}
			else {
				$record_id = absint( $_GET['mailings_record'] ) ;
				self::delete_mailings_record( $record_id );
			}
		}

		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
		     || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
		) {
			$delete_ids = esc_sql( $_POST['bulk-ids'] );

			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				self::delete_mailings_record( $id );
			}
		}
	}
}


//
// Instantiated by / near add_submenu_page('Manage') through get_instance()
//
class WGA_Manage_Mailings {

	// class instance
	static $instance;

	// email WP_List_Table object
	public $mailings_list_obj;

	// class constructor
	public function __construct() {
		add_filter( 'set-screen-option', [ __CLASS__, 'set_screen' ], 10, 3 );
		//add_action( 'admin_menu', [ $this, 'plugin_menu' ] );
	}


	public static function set_screen( $status, $option, $value ) {
		return $value;
	}

    /*
	public function plugin_menu() {

		$hook = add_menu_page(
			'Sitepoint WP_List_Table Example',
			'SP WP_List_Table',
			'manage_options',
			'wp_list_table_class',
			[ $this, 'plugin_settings_page' ]
		);

		add_action( "load-$hook", [ $this, 'screen_option' ] );

	}
    */


	/**
	 * Plugin settings page
	 */
	public function wga_plugin_settings_page() {
        echo '<style type="text/css">';
        echo '.wp-list-table .column-id { width: 7em; }';
        echo '.wp-list-table .column-Message { width: 8em; }';
        echo '.wp-list-table .column-F { width: 10em; }';
        echo '.wp-list-table .column-Start { width: 7em; }';
        echo '.wp-list-table .column-Created { width: 7em; }';
        echo '.wp-list-table .column-Updated { width: 8em; }';
        echo '.wp-list-table .column-Sent { width: 6em; }';
        echo '.wp-list-table .column-Completed { width: 8em; }';
        echo '</style>';
		?>
		<div class="wrap">
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
                            <form method="post">
		                        <input type='hidden' name='action' value='apply_bulk_action'>
								<?php
								$this->mailings_list_obj->prepare_items();
								$this->mailings_list_obj->get_views();
								$this->mailings_list_obj->display(); 
								?>
							</form>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</div>
	<?php
	}

	/**
	 * Screen options
	 */
	public function screen_option() {

		$option = 'per_page';
		$args   = [
			'label'   => 'Mailings Records',
			'default' => 5,
			'option'  => 'messages_per_page'
		];

		add_screen_option( $option, $args );

		$this->mailings_list_obj = new WGA_Mailings_List();
	}


	/** Singleton instance */
	public static function get_instance() {
		if ( ! isset( self::$instance ) || self::$instance == null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
