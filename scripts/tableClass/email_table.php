<?php

/*
WGA_Message_list dirived from WP_List_Table Class
Plugin URI: https://www.sitepoint.com/using-wp_list_table-to-create-wordpress-admin-tables/
Description: Based on Demo on how WP_List_Table Class works
Version: 1.0
Author: Will Auld
*/

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WGA_Email_List extends WP_List_Table {

	/** Class constructor */
	public function __construct() {

		parent::__construct( [
			'singular' => __( 'Email', 'sp' ), //singular name of the listed records
			'plural'   => __( 'Emails', 'sp' ), //plural name of the listed records
			'ajax'     => false, //does this table support ajax?
			//'screen'   => 'Messages'
		] );

	}
    protected function get_views() { 
        $status_links = array(
            "All"       => __("<a href='#'>All</a>",'my-plugin-slug'),
            "Active" => __("<a href='#'>Active</a>",'my-plugin-slug'),
            "Unverified"   => __("<a href='#'>Unverified</a>",'my-plugin-slug'),
            "Unsubscribed"   => __("<a href='#'>Unsubscribed</a>",'my-plugin-slug'),
            "SPAM"   => __("<a href='#'>SPAM</a>",'my-plugin-slug'),
        );
        return $status_links;
    }

	/**
	 * Display email edit form  
	 *
	 * @param object $record db row
	 *
	 * @return mixed
	 */
	public static function display_record_edit_form($record){
		echo '<style>';
		//echo '.edit-form {';
  		//echo '	background-color: #F6F7F8;';
  		//echo '	border: 1px solid #D6D9DC;';
  		//echo '	border-radius: 3px;';
  		//echo '	width: 80%;';
  		//echo '	padding: 50px;';
  		//echo '	margin: 0 0 40px 0;';
		//echo '}';
		echo 'label {';
    	echo 'display: inline-block;';
    	echo 'width:100px;';
    	echo 'text-align: right;';
		echo '}';
		echo '</style>';

		echo '<div style="border: 2px solid #D6D9DC; background-color: #F6F7F8; border-radius: 6px; width: 60%; ipadding: 50px; margin: 0 0 40px 0; text-align: center; " >';
        echo '<H1>Email Record id: '.$record->id.'</H1><br>';

        echo '<form method="POST" class="edit-form">';

        echo '<label for="fname">First name:</label>';
        echo '<input type="text" id="fname" name="fname" value="'.$record->first_name.'"><br><br>';

        echo '<label for="lname">Last name:</label>';
        echo '<input type="text" id="lname" name="lname" value="'.$record->last_name.'"><br><br>';

        echo '<label for="email">Email:</label>';
        echo '<input type="email" id="email" name="email" value="'.$record->email.'"><br><br>';

        echo '<label for="src">Source:</label>';
        echo '<input type="text" id="src" name="source" value="'.$record->source.'"><br><br>';

        echo '<label for="unsub">Unsubscribed:</label>';
        echo '<input type="text" id="unsub" name="unsubscribed" value="'.$record->unsubscribed.'"><br><br>';

        echo '<label for="isv">Is Verified?:</label>';
        echo '<input type="text" id="isv" name="is_verified" value="'.$record->is_verified.'"><br><br>';

        echo '<label for="is_s">Is SPAM?</label>';
        echo '<input type="text" id="is_s" name="is_spam" value="'.$record->is_spam.'"><br><br>';

	    submit_button( 'Save Modified Record' );
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
	public static function get_email_list( $per_page = 5, $page_number = 1 ) {

		global $wpdb;

		$sql = "SELECT * FROM {$wpdb->prefix}wga_contact_list";

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
	 * get an email record.
	 *
	 * @param int $id customer ID
	 */
	public static function get_email_record( $id ) {
		global $wpdb;

		$sql = "SELECT * FROM {$wpdb->prefix}wga_contact_list WHERE id = $id";
		$result = $wpdb->get_row( $sql );
        return $result;
	}

	/**
	 * Delete an email record.
	 *
	 * @param int $id customer ID
	 */
	public static function delete_email_record( $id ) {
		global $wpdb;

		$wpdb->delete(
			"{$wpdb->prefix}wga_contact_list",
			[ 'id' => $id ],
			[ '%d' ]
		);
	}

	/**
	 * Set SPAM bit in email record.
	 *
	 * @param int $id customer ID
	 */
	public static function spam_email_record( $id ) {
		global $wpdb;

		$wpdb->update(
			"{$wpdb->prefix}wga_contact_list",
            [ 'is_spam' => 1 ],
			[ 'id' => $id ],
			[ '%d' ]
		);
	}

	/**
	 * Set unsubscribe bit in email record.
	 *
	 * @param int $id customer ID
	 */
	public static function unsubscribe_email_record( $id ) {
		global $wpdb;

		$wpdb->update(
			"{$wpdb->prefix}wga_contact_list",
            [ 'unsubscribed' => 1 ],
			[ 'id' => $id ],
			[ '%d' ]
		);
	}

	/**
	 * Edit update email record.
	 *
	 * @param int $id customer ID
	 */
	public function edit_update_email_record( $id, $fname, $lname, $email, $src, $unsub, $is_ver, $is_spam ) {
		global $wpdb;
		$wpdb->update(
			"{$wpdb->prefix}wga_contact_list",
            [ 
                'first_name' => $fname,
                'last_name' => $lname,
                'email' => $email,
                'source' => $src,
                'unsubscribed' => $unsub,
                'is_verified' => $is_ver,
                'is_spam' => $is_spam,
				'updated_at' => current_time( 'mysql' ),
            ],
			[ 'id' => $id ]
		);
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}wga_contact_list";

		return $wpdb->get_var( $sql );
	}


	/** Text displayed when no data is available */
	public function no_items() {
		_e( 'No email records avaliable.', 'sp' );
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
				return $item[ 'id' ];
			case 'First Name':
				return $item[ 'first_name' ];
			case 'Last Name':
				return $item[ 'last_name' ];
			case 'Email':
				return $item[ 'email' ];
			case 'Source':
				return $item[ 'source' ];
            case 'Unsubscribed':
				return $item[ 'unsubscribed' ];
			case 'Created_at':
				return $item[ 'created_at' ];
			case 'Updated_at':
				return $item[ 'updated_at' ];
				//return $item[ 'message_'.$column_name ];
			case 'Is Verified?':
				return $item[ 'is_verified' ];
			case 'Is SPAM?':
				return $item[ 'is_spam' ];
			case 'Hash':
				return $item[ 'vhash' ];
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
			'<input type="checkbox" name="bulk-ids[]" value="%s" />', $item['id']
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

		$edit_nonce = wp_create_nonce( 'sp_edit_email_record' );
		$delete_nonce = wp_create_nonce( 'sp_delete_email_record' );
		$spam_nonce = wp_create_nonce( 'sp_SPAM_email_record' );
		$unsub_nonce = wp_create_nonce( 'sp_unsubscribe_email_record' );

		$title = '<strong>' . $item['id'] . '</strong>';

		$actions = [
            'edit' => sprintf( '<a href="?page=%s&action=%s&email_record=%s&_wpnonce=%s">Edit</a>', esc_attr( $_REQUEST['page'] ), 'edit', absint( $item['id'] ), $edit_nonce ),
			'delete' => sprintf( '<a href="?page=%s&action=%s&email_record=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['id'] ), $delete_nonce ),
			'SPAM' => sprintf( '<a href="?page=%s&action=%s&email_record=%s&_wpnonce=%s">SPAM</a>', esc_attr( $_REQUEST['page'] ), 'SPAM', absint( $item['id'] ), $spam_nonce ),
			'Unsubscribe' => sprintf( '<a href="?page=%s&action=%s&email_record=%s&_wpnonce=%s">Unsubscribe</a>', esc_attr( $_REQUEST['page'] ), 'Unsubscribe', absint( $item['id'] ), $unsub_nonce ),
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
			'First Name'    => __( 'First Name', 'sp' ),
			'Last Name' => __( 'Last Name', 'sp' ),
			'Email' => __( 'Email', 'sp' ),
			'Source' => __( 'Source', 'sp' ),
			'Unsubscribed' => __( 'Unsubscribed', 'sp' ),
			'Is Verified?'    => __( 'Is Verified?', 'sp' ),
			'Is SPAM?'    => __( 'Is SPAM?', 'sp' ),
			'Created_at'    => __( 'Created_at', 'sp' ),
			'Updated_at'    => __( 'Updated_at', 'sp' ),
			'Hash'    => __( 'Hash', 'sp' ),
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
			'id' => array( 'id', true ),
			'First Name' => array( 'first_name', true ),
			'Last Name' => array( 'last_name', true ),
			'Email' => array( 'email', false ),
			'Source' => array( 'source', false ),
			'Unsubscribed' => array( 'unsubscribed', false ),
			'Created_at' => array( 'created_at', true ),
			'Updated_at' => array( 'updated_at', true ),
			'Is Verified?' => array( 'is_verified', true ),
			'Is SPAM?' => array( 'is_spam', true ),
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
			'bulk-SPAM' => 'SPAM',
			'bulk-unsubscribe' => 'Unsubscribe',
			'bulk-resend-verify' => 'Resend Verify',
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

		$this->items = self::get_email_list( $per_page, $current_page );
	}

	public function process_bulk_action() {
		//Detect when a bulk action is being triggered...
				
        /*
        echo '<pre>';
        echo '<h2> $_REQUEST() </h2>';
        print_r($_REQUEST);
        echo '<h2> $_GET() </h2>';
        print_r($_GET);
	    print_r($this->current_action());
        echo '</pre>';
        */

		if ( 'edit' === $this->current_action() ) {
			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );
			if ( ! wp_verify_nonce( $nonce, 'sp_edit_email_record' ) ) {
				die( 'Go get a life script kiddies' );
			}
			else {
				$email_record_id = absint( $_GET['email_record'] ) ;
				$record = self::get_email_record( $email_record_id );
                echo 'id: '.$record->id;
                self::display_record_edit_form($record);
			}
            // SAVE operation is handled by on-page php code.
        }elseif ( 'delete' === $this->current_action() ) {
			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );
			if ( ! wp_verify_nonce( $nonce, 'sp_delete_email_record' ) ) {
				die( 'Go get a life script kiddies' );
			}
			else {
				$email_record_id = absint( $_GET['email_record'] ) ;
				self::delete_email_record( $email_record_id );
			}
        }elseif ( 'SPAM' === $this->current_action() ) {
			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );
			if ( ! wp_verify_nonce( $nonce, 'sp_SPAM_email_record' ) ) {
				die( 'Go get a life script kiddies' );
			}
			else {
				$email_record_id = absint( $_GET['email_record'] ) ;
				self::spam_email_record( $email_record_id );
			}
        }elseif ( 'Unsubscribe' === $this->current_action() ) {
			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );
			if ( ! wp_verify_nonce( $nonce, 'sp_unsubscribe_email_record' ) ) {
				die( 'Go get a life script kiddies' );
			}
			else {
				$email_record_id = absint( $_GET['email_record'] ) ;
				self::unsubscribe_email_record( $email_record_id );
			}
		}

		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
		     || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
		) {
			$delete_ids = esc_sql( $_POST['bulk-ids'] );

			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				self::delete_email_record( $id );
			}
        } elseif ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-SPAM' )
		     || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-SPAM' )
		) {
			$spam_ids = esc_sql( $_POST['bulk-ids'] );

			// loop over the array of record IDs and set them as SPAM
			foreach ( $spam_ids as $id ) {
				self::spam_email_record( $id );
			}
        } elseif ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-unsubscribe' )
		     || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-unsubscribe' )
		) {
			$unsub_ids = esc_sql( $_POST['bulk-ids'] );

			// loop over the array of record IDs and set them as SPAM
			foreach ( $unsub_ids as $id ) {
				self::unsubscribe_email_record( $id );
			}
		}
	}
}


//
// Instantiated by / near add_submenu_page('Manage') through get_instance()
//
class WGA_Manage_Email {

	// class instance
	static $instance;

	// email WP_List_Table object
	public $email_list_obj;

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
        echo '.wp-list-table .column-First { width: 10em; }';
        echo '.wp-list-table .column-Last { width: 10em; }';
        echo '.wp-list-table .column-Email { width: 10em; }';
        echo '.wp-list-table .column-Source { width: 10em; }';
        echo '.wp-list-table .column-Unsubscribed { width: 7em; }';
        echo '.wp-list-table .column-Is { width: 6em; }';
        echo '.wp-list-table .column-Created_at { width: 7em; }';
        echo '.wp-list-table .column-Updated_at { width: 7em; }';
        echo '.wp-list-table .column-Hash { width: 5em; }';
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
								$this->email_list_obj->prepare_items();
								$this->email_list_obj->get_views();
								$this->email_list_obj->display(); 
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
			'label'   => 'Email Records',
			'default' => 5,
			'option'  => 'messages_per_page'
		];

		add_screen_option( $option, $args );

		$this->email_list_obj = new WGA_Email_List();
	}


	/** Singleton instance */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
