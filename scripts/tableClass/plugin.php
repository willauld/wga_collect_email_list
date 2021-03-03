<?php

/*
Plugin Name: WP_List_Table Class Example
Plugin URI: https://www.sitepoint.com/using-wp_list_table-to-create-wordpress-admin-tables/
Description: Demo on how WP_List_Table Class works
Version: 1.0
Author: Collins Agbonghama
Author URI:  https://w3guy.com
*/

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WGA_Message_List extends WP_List_Table {

	/** Class constructor */
	public function __construct() {

		parent::__construct( [
			'singular' => __( 'Message', 'sp' ), //singular name of the listed records
			'plural'   => __( 'Messages', 'sp' ), //plural name of the listed records
			'ajax'     => false, //does this table support ajax?
			'screen'   => 'Campaign'
		] );

	}


	/**
	 * Retrieve customers data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_messages( $per_page = 5, $page_number = 1 ) {

		global $wpdb;

		$sql = "SELECT * FROM {$wpdb->prefix}wga_message_list";

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
	 * Delete a customer record.
	 *
	 * @param int $id customer ID
	 */
	public static function delete_message( $id ) {
		global $wpdb;

		$wpdb->delete(
			"{$wpdb->prefix}wga_message_list",
			[ 'message_id' => $id ],
			[ '%d' ]
		);
	}


	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}wga_message_list";

		return $wpdb->get_var( $sql );
	}


	/** Text displayed when no customer data is available */
	public function no_items() {
		_e( 'No messages avaliable.', 'sp' );
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
				return $item[ 'message_id' ];
			case 'Subject':
				return $item[ 'message_subject' ];
			case 'Content':
				return $item[ 'message_content' ];
			case 'Created_at':
				return $item[ 'message_created_at' ];
			case 'Updated_at':
				return $item[ 'message_updated_at' ];
				//return $item[ 'message_'.$column_name ];
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
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['message_id']
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

		$delete_nonce = wp_create_nonce( 'sp_delete_message' );

		$title = '<strong>' . $item['message_id'] . '</strong>';

		$actions = [
			'delete' => sprintf( '<a href="?page=%s&action=%s&message=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['message_id'] ), $delete_nonce )
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
			'Subject'    => __( 'Subject', 'sp' ),
			'Content' => __( 'Content', 'sp' ),
			'Created_at'    => __( 'Created_at', 'sp' ),
			'Updated_at'    => __( 'Updated_at', 'sp' )
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
			'id' => array( 'message_id', true ),
			'Subject' => array( 'message_subject', true ),
			//'Content' => array( 'message_content', false ),
			'Created_at' => array( 'message_created_at', true ),
			'Updated_at' => array( 'message_updated_at', true )
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
			'bulk-delete' => 'Delete'
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

		$per_page     = $this->get_items_per_page( 'messages_per_page', 5 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );

		$this->items = self::get_messages( $per_page, $current_page );
	}

   //&&&& add_action( 'admin_post_apply_bulk_action', 'process_bulk_action' );&&&& FIXME trying at construction of WGA_Plugin();

	public function process_bulk_action() {
		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );
			if ( ! wp_verify_nonce( $nonce, 'sp_delete_message' ) ) {
				die( 'Go get a life script kiddies' );
			}
			else {
				print_r($_GET['message']);
				
    echo '<pre>';
    print_r($_REQUEST);
    print_r($_GET);
	print_r(absint($_GET['message']));
    echo '</pre>';
				self::delete_message( absint( $_GET['message'] ) );

		        // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
		        // add_query_arg() return the current url
		    	//wp_redirect( 'http://wp2.test/wp-admin/admin.php?page=Campaign' );
		        //wp_redirect( esc_url_raw(add_query_arg()) );
				//echo 'Ob_get_level(): '.ob_get_level();
				//echo 'ob_get_content():: '.ob_get_contents();
	            //wp_redirect( $_POST["current_url"] );
				//exit;
			}

		}

		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
		     || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
		) {

			$delete_ids = esc_sql( $_POST['bulk-delete'] );

			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				self::delete_message( $id );

			}

			// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
		    // add_query_arg() return the current url
		    //wp_redirect( esc_url_raw(add_query_arg()) );
		    //wp_redirect( 'http://wp2.test/wp-admin/admin.php?page=Campaign' );
				//echo 'Ob_get_level(): '.ob_get_level();
				//echo 'ob_get_content():: '.ob_get_contents();
	        //wp_redirect( $_POST["current_url"] );
			//exit;
		}
	}

}


class WGA_Plugin {

	// class instance
	static $instance;

	// customer WP_List_Table object
	public $messages_obj;

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
    <form action='<?php admin_url( 'admin-post.php' ); ?>'' method="post">
	 */
	public function wga_plugin_settings_page() {
		?>
		<div class="wrap">
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
                            <form method="post">
		                        <input type='hidden' name='action' value='apply_bulk_action'>
		                        <input type='hidden' name='current_url' value='<?php echo $current_url ?>' >
								<?php
								$this->messages_obj->prepare_items();
								$this->messages_obj->display(); ?>
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
			'label'   => 'Messages',
			'default' => 5,
			'option'  => 'messages_per_page'
		];

		add_screen_option( $option, $args );

		$this->messages_obj = new WGA_Message_List();
	}


	/** Singleton instance */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}


add_action( 'plugins_loaded', function () {
    WGA_Plugin::get_instance();
    //add_action( 'admin_post_apply_bulk_action', 'process_bulk_action' );
} );
