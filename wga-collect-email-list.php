<?php
   /*
   Plugin Name: WGA collect email list
   Plugin URI: 
   description: a plugin to collect email addresses and create a list in the database
   Version: 1.0
   Author: Will Auld (WGA)
   Author URI: 
   License: GPL2
   */
   

/*
Author: Agbonghama Collins
Author URI: http://w3guy.com

Also used the following two articles:
- https://premium.wpmudev.org/blog/activate-deactivate-uninstall-hooks/
- https://premium.wpmudev.org/blog/creating-database-tables-for-plugins/
-https://www.copernica.com/en/blog/post/how-to-create-email-buttons-with-just-html-and-css
- https://code.tutsplus.com/tutorials/how-to-implement-email-verification-for-new-members--net-3824
- 

*/

//
//Plugin Activation: wga-collect-email-list
//

register_activation_hook( __FILE__, 'wga_collect_email_list_activation' );
function wga_collect_email_list_activation() {
	
	global $wp_version;
	$php = '7.0'; //'5.3';
	$wp  = '5.0'; //'3.8';
	
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
	
	// does not work --> check_admin_referer( "deactivate-plugin_{$plugin}" );

	add_option( 'wga-collect-email-list-activated', time() );

	if ( version_compare( PHP_VERSION, $php, '<' ) ) {
		deactivate_plugins( basename( __FILE__ ) );
		wp_die(
			'<p>' .
			sprintf(
				__( 'This plugin can not be activated because it requires a PHP version greater than %1$s. Your PHP version can be updated by your hosting company.', 'wga_collect_email_list' ),
				$php
			)
			. '</p> <a href="' . admin_url( 'plugins.php' ) . '">' . __( 'go back', 'wga_collect_email_list' ) . '</a>'
		);
	}

	if ( version_compare( $wp_version, $wp, '<' ) ) {
		deactivate_plugins( basename( __FILE__ ) );
		wp_die(
			'<p>' .
			sprintf(
				__( 'This plugin can not be activated because it requires a WordPress version greater than %1$s. Please go to Dashboard &#9656; Updates to get the latest version of WordPress .', 'wga_collect_email_list' ),
				$wp
			)
			. '</p> <a href="' . admin_url( 'plugins.php' ) . '">' . __( 'go back', 'wga_collect_email_list' ) . '</a>'
		);
	}
	// C:\laragon\www\wp2\wp-content\plugins
	$filename = __DIR__.'/scripts/verify.php';
	$dest1dir = __DIR__.'/../../../';
    $destfile = $dest1dir.'verify.php';

	if (!file_exists($filename)) {
    	wp_die("Could not activate\nThe file $filename does not exist");
	}
	if (!file_exists($dest1dir)) {
    	wp_die("Could not activate\nThe file $dest1dir does not exist");
	}
	if (!copy($filename, $destfile)) {
    	wp_die("Could not activate\nThe file $filename could not be copied");
    }
}

global $wga_db_version;
$wga_db_version = '1.0';

function wga_db_table_install() {
	global $wpdb;
	global $wga_db_version;

	$table_name = $wpdb->prefix . 'wga_contact_list';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id int(10) NOT NULL AUTO_INCREMENT,
		first_name varchar(50),
		last_name varchar(50),
		email varchar(50) NOT NULL,
		source varchar(50),
		unsubscribed tinyint(1) DEFAULT 0 NOT NULL,
		created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		updated_at datetime,
		is_verified tinyint(1) DEFAULT 0,
        vhash varchar(32) NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";


	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	$dbdelta_result = dbDelta( $sql );
	
	if ( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name ) {
		// Table was not created !!
		wp_die(
			'<p>' .
			sprintf( 
			    __( $dbdelta_result ) 
				) .
			sprintf(
				__( 'This plugin can not be activated because the TABLE CREATION FAILED.', 'wga_collect_email_list' )
			)
			. '</p> <a href="' . admin_url( 'plugins.php' ) . '">' . __( 'go back', 'wga_collect_email_list' ) . '</a>'
		);
	}
	
	add_option( 'wga_db_version', $wga_db_version );
}
register_activation_hook( __FILE__, 'wga_db_table_install' );

function wga_install_data() {
	global $wpdb;
	/**/
	$first_name = 'Joe';
	$last_name = 'Smith';
	$email = 'will@auld.com';
	$created_at = current_time( 'mysql' );
	
	$table_name = $wpdb->prefix . 'wga_contact_list';
	
	$wpdb->insert( 
		$table_name, 
		array( 
			'first_name' => $first_name, 
			'last_name' => $last_name, 
			'email' => $email,
			'created_at' => $created_at,
		) 
	);
}
//register_activation_hook( __FILE__, 'wga_install_data' );// only for testing

//
// Plugin deactivation: wga-collect-email-list
//

register_deactivation_hook( __FILE__, 'wga_collect_email_list_deactivation' );
function wga_collect_email_list_deactivation() { // TESTME echos are not displayed!
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
	check_admin_referer( "deactivate-plugin_{$plugin}" );
	
  	// Deactivation rules here
	$dest1dir = __DIR__.'/../../../';
	$destfile = $dest1dir.'verify.php';

	if (!file_exists($destfile)) {
    	wp_die("Could not deactivate\nThe file $destfile does not exist");
	}
	if (!unlink($destfile)) {
    	wp_die("Could not deactivate\nThe file $destfile could not be removed");
	}
}

//
// form code
//

function wga_html_form_code($inpopup) {
	
	global $wpdb;

	/* define variables and set to empty values */
	$nameErr = $emailErr = "";
	$name = $email = "";
	$message = "";
	
	//
	// set for use in or out of a plugin
	//
	if (($_SERVER["REQUEST_METHOD"] == "POST") and (empty($_POST['post_handled'])))
	{
	  if (empty($_POST["cf-name"])) {
	    $nameErr = "Name is required";
	  } else {
	    $name = wga_test_input($_POST["cf-name"]);
	    /* check if name only contains letters and whitespace */ 
	    if (!preg_match("/^[a-zA-Z-' ]*$/",$name)) { 
	      $nameErr = "Letters & white space only";
	    }
	  }
  
	  if (empty($_POST["cf-email"])) {
	    $emailErr = "Email is required";
	  } else {
	    $email = wga_test_input($_POST["cf-email"]);
	    /* check if e-mail address is well-formed */
	    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	      $emailErr = "Invalid email format";
	    } else {
			$query   = $wpdb->prepare( 
				"SELECT * FROM {$wpdb->prefix}wga_contact_list WHERE email = %s", $email 
			);
			$results = $wpdb->get_results( $query );

			if ( count( $results ) > 0 ) {
				//wp_send_json_error( "Email already exits. Results: $results", 400 );
				//wp_die( "Email already exits. Results: ", $results);
				$emailErr = "Email already exits";
			}
		}
	  }
	  if ($nameErr == "" and $emailErr == "") {
		  
		//
		// No errors, clear the fields, email?, Database? HERE
		//
			
		wga_process_input($name, $email);
	
		$name = $email = "";
		
		$_POST['post_handled'] = true;
		
		//if ($inpopup == true) {
		  echo '<h2>Thank you!</h2><br>Please verify your email address by clicking the activation link that has been sent to your email.';
		//}
		echo '<script>'.PHP_EOL;
		echo 'if ( window.history.replaceState ) {'.PHP_EOL;
		echo '	window.history.replaceState( null, null, window.location.href );'.PHP_EOL;
		echo '}'.PHP_EOL;
		echo '</script>'.PHP_EOL;
	  }
	}
	
    //
	// form execution
	//
	if (($inpopup == false) or empty($_POST['post_handled'])) {
		echo '<style>'.PHP_EOL;
		
		echo '.error, .required {'.PHP_EOL;
		//echo '	color: #FF0000;'.PHP_EOL;
		echo '  color: #760000;'.PHP_EOL;
		//echo '  font-size: 0.75em;'.PHP_EOL;
		echo '} '.PHP_EOL;
		
		echo 'fieldset {'.PHP_EOL;
		echo '  margin: 1em 0;'.PHP_EOL;
		echo '  padding 1em;'.PHP_EOL;
		echo '  border: 1px solid #ccc;'.PHP_EOL;
		echo '  background: #3cb5e8;'.PHP_EOL; //#f8f8f8;'.PHP_EOL;
		//echo '  font-size:0.75em;'.PHP_EOL;
		//echo '  display: inline-block;'.PHP_EOL;
		echo '  width: 33em;'.PHP_EOL;
		echo '}'.PHP_EOL;
		
		echo 'legend{'.PHP_EOL;
		//echo '  font-weight: bold;'.PHP_EOL;
		//echo '	  padding: 0.2em 0.5em;'.PHP_EOL;
		//echo '  border:1px solid green;'.PHP_EOL;
		//echo '  color:green;'.PHP_EOL;
		echo '  font-size:90%;'.PHP_EOL;
		//echo '  text-align:right;'.PHP_EOL;
		echo '}'.PHP_EOL;
		
		//echo 'label, required{';
		echo '	label {'.PHP_EOL;
		echo '    float:left;'.PHP_EOL;
		echo '    width:14%;'.PHP_EOL;
		echo '    margin-right:0.5em;'.PHP_EOL;
		echo '    padding-top:0.2em;'.PHP_EOL;
		echo '    text-align:right;'.PHP_EOL;
		//echo '    font-weight:bold;'.PHP_EOL;
		echo '  }'.PHP_EOL;
		
		echo '#pinfo {'.PHP_EOL;
		//echo '  font-size: 0.75em;'.PHP_EOL;
		//echo '  width: 80%;';
		echo '}';
		
		echo 'input[type=submit] {'.PHP_EOL;
		echo '  padding:5px 10px; '.PHP_EOL;
		//echo '  background:#ccc; '.PHP_EOL;
		echo '  border:0 none;'.PHP_EOL;
		//echo '  cursor:pointer;'.PHP_EOL;
		//echo '  -webkit-border-radius: 5px;'.PHP_EOL;
		echo '  border-radius: 5px; '.PHP_EOL;
		echo '}'.PHP_EOL;
		
		echo '</style>'.PHP_EOL;

		echo '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post">'.PHP_EOL;
		if ($inpopup == false) {
			echo '  <fieldset id="pinfo">'.PHP_EOL;
			if (!empty($_POST['post_handled'])) {
		  		echo '<h2>Thank you!</h2><br>Please verify your email address by clicking the activation link that has been sent to your email.';
			}
		}
		echo '    <fieldset>'.PHP_EOL;
		echo '      <legend>Join email list</legend>';
		echo '      <p><span class="error">* required field</span></p> '.PHP_EOL;
		echo '      <p>'.PHP_EOL;
		echo '        <label for="name">Name:<em class="required">*</em> </label>';
		echo '        <input type="text" id="name" name="cf-name" tabindex="1" value="' . $name . '">'.PHP_EOL;
		echo '        <span class="error"> ' . $nameErr . '</span>'.PHP_EOL;
		echo '      </p>'.PHP_EOL;
		echo '      <p>'.PHP_EOL;
		echo '        <label for="email">Email:<em class="required">*</em> </label>';
		echo '        <input type="text" id="email" name="cf-email" tabindex="2" value="' . $email . '">'.PHP_EOL;
		echo '        <span class="error"> ' . $emailErr . '</span>'.PHP_EOL;
		echo '      </p>'.PHP_EOL;
		echo '    </fieldset>'.PHP_EOL;
		echo '    <input type="submit" name="cf-submitted" value="Submit">'.PHP_EOL;
		if ($inpopup == false) {
			echo '  </fieldset>'.PHP_EOL;
		}
		echo '</form>'.PHP_EOL;
		
		//echo '<script>'.PHP_EOL;
		//echo 'if ( window.history.replaceState ) {'.PHP_EOL;
		//echo '	window.history.replaceState( null, null, window.location.href );'.PHP_EOL;
		//echo '}'.PHP_EOL;
		//echo '</script>'.PHP_EOL;
	}
}


function wga_test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

function wga_process_input($name, $email) {
    global $wpdb;
    $source = "?";
	
	$a = explode(" ", $name, 2);
	$first_name = $a[0];
	$last_name = $a[1];
	//
	// Add db record
	//
    $created_at = current_time( 'mysql' );
    $hash = md5( rand(0,1000) ); // Generate random 32 character hash
    // Example output: f4552671f8909587cf485ea990207f3b

	$table_name = $wpdb->prefix . 'wga_contact_list';
	$wpdb->insert( 
		$table_name, 
		array( 
			'first_name' => $first_name, 
			'last_name' => $last_name, 
            'email' => $email,
            'source' => $source,
            'created_at' => $created_at,
            'vhash' => $hash, 
		) 
	);
	//
	// Send HTML mail to:
	//
	$subject = "Confirm subscription to Oregon Open Primaries"; // sanitize_text_field( $_POST["cf-subject"] );

	// get the blog administrator's email address
	//$to = get_option( 'admin_email' );
	
    // test code - button code generated at: https://buttons.cm/
    $message = '<html>
                    <head>
                        <style type=“text/css”>
                        </style>
                    </head>
                    <body>
                        <img width="600" src="'.site_url().'/wp-content/uploads/2020/12/LogoOregonOpenPrimaries.png" alt="Let ALL voters vote!"/><br><br>
                        <br><br>
                        <br>' .
                        $name . ',<br>
                        Please click the following to verify your email address:
                        <table width="100%" cellspacing="50" cellpadding="0">
                            <tr>
                                <td><td>
                        <div><!--[if mso]>
                            <v:roundrect            xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="'.site_url().'/verify.php?email='.$email.'&vhash='.$hash.'" style="height:50px;v-text-anchor:middle;width:350px;" arcsize="8%" strokecolor="#262661" fillcolor="#262661">
                                <w:anchorlock/>
                                <center style="color:#FFEA0F;font-family:sans-serif;font-size:13px;font-weight:bold;">
                                    Yes, subscribe me to Oregon Open Primaries!
                                </center>
                            </v:roundrect>
                            <![endif]--><a href="'.site_url().'/verify.php?email='.$email.'&vhash='.$hash.'" 
                                style="background-color:#262661;border:1px solid #262661;border-radius:4px;color:#FFEA0F;display:inline-block;font-family:sans-serif;font-size:13px;font-weight:bold;line-height:50px;text-align:center;text-decoration:none;width:350px;-webkit-text-size-adjust:none;mso-hide:all;">
                                    Yes, subscribe me to Oregon Open Primaries!
                                </a>
                        </div>
                                </td></td>
                            </tr>
                        </table>
                        Thanks <br>
                        <br>
                        Adding "' .$email. '" to email list. <br>
                        <br>
                    </body>
                </html>';
	 
	$to = "$name <$email>";
	//$headers = "From: $name <$email>" . "\r\n";
	$headers = "From: OregonOpenPrimaries.org <info@OregonOpenPrimaries.org> \r\n";
	$headers .= "Cc:OregonOpenPrimaries.org <info@OregonOpenPrimaries.org> \r\n";
	$headers .= "Cc:".get_option( 'admin_email' )." \r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html\r\n";

	// original: $email_response = wp_mail( $to, $subject, $message, $headers );
	$email_response = wp_mail( $to, $subject, $message, $headers );
	// If email has been process for sending, display a success message
	if ( $email_response ) {
		//echo '<div>';
		//echo '<p>Thanks for contacting me, expect a response soon.</p>';
		//echo '</div>';
	} else {
		//echo 'An unexpected error occurred';
	}
}

function wga_shortcode_popup() {
	ob_start();
	wga_html_form_code(true /*inpopup=true*/);
	return ob_get_clean();
}
add_shortcode( 'wga_popup_email_form', 'wga_shortcode_popup' );

function wga_shortcode_on_page() {
	ob_start();
	wga_html_form_code(false /*inpopup=false*/);
	return ob_get_clean();
}
add_shortcode( 'wga_on_page_email_form', 'wga_shortcode_on_page' );

?>
