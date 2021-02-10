<?php
   /*
   Plugin Name: WGA collect email list
   Plugin URI: 
   description: a plugin to collect email addresses and create a list in the database
   Version: 1.1
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
function wga_collect_email_list_deactivation() { 
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

function wga_html_form_code($inpopup, $contact_form) {
	
	global $wpdb;

	/* define variables and set to empty values */
	$nameErr = $emailErr = "";
    $name = $email = "";
    $input_message = "";
    $remember = 0;
	$was_remembered = 0;
    
    //echo __LINE__.":: contact_form: $contact_form remember: $remember\n";
    //if ($contact_form==0) {
    //    debug_print_backtrace();
    //}
    echo '<!-- Posted1:';
    if ($_POST) {
        echo var_dump($_POST);
    }
    echo '-->';
	if ($_POST) {
		$page="post";
	}elseif($_GET) {
		$page="get";
	}else{
		$page="?";
	}
	//$error_string = print_r($e->getTrace(), true);

	echo wga_console_log( __LINE__." WGA:: page:$page, inpopup:$inpopup, contact_form:$contact_form" );
	//echo wga_console_log( "WGA:: ".print_r($e->getTrace(), true) );
	//
	// set for use in or out of a plugin
	//
	if (($_SERVER["REQUEST_METHOD"] == "POST") and (empty($_POST['cf-post_handled'])))
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
      
      if (!empty($_POST["cf-remember"])) { // checkbox
        if ($_POST["cf-remember"] == "on") {
			$remember = 1;
			$was_remembered = 1;
		} else {
			$remember = 0;
			$was_remembered = 0;
		}
      }

      if (!empty($_POST["cf-text"])) {
          $input_message = wga_test_input($_POST["cf-text"]);
      }
  
	  if (empty($_POST["cf-email"])) {
	    $emailErr = "Email is required";
	  } else {
	    $email = wga_test_input($_POST["cf-email"]);
	    /* check if e-mail address is well-formed */
	    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	      $emailErr = "Invalid email format";
	    } elseif ($contact_form==0 or $remember==1) {
            /*
			$query   = $wpdb->prepare( 
				"SELECT * FROM {$wpdb->prefix}wga_contact_list WHERE email = %s", $email 
			);
            $results = $wpdb->get_results( $query );
            */
            $results = wga_is_active_email($email);

			if ( count( $results ) > 0 ) {
				$emailErr = "Email already exits";
			}
		}
	  }
	  if ($nameErr == "" and $emailErr == "") {
		  
		//
		// No errors, clear the fields, email?, Database? HERE
		//
        //echo __LINE__.":: contact_form: $contact_form\n";
			
        wga_process_input($name, $email, $remember, $input_message, $contact_form);
	
        $name = $email = "";
        $input_message = "";
        $remember = 0;
		
		$_POST['cf-post_handled'] = true;
		
		if ($inpopup == 1) {
		  echo '<h2>Thank you!</h2><br>Please verify your email address by clicking the activation link that has been sent to your email.';
		}
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
	if (($inpopup == 0) or empty($_POST['cf-post_handled'])) {
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
		
        echo '  .reg_label {'.PHP_EOL;
		echo '    float:left;'.PHP_EOL;
		echo '    width:14%;'.PHP_EOL;
		echo '    margin-right:0.5em;'.PHP_EOL;
		echo '    padding-top:0.2em;'.PHP_EOL;
		echo '    text-align:right;'.PHP_EOL;
		//echo '    font-weight:bold;'.PHP_EOL;
		echo '  }'.PHP_EOL;
		
		echo '#pinfo {'.PHP_EOL;
		//echo '  font-size: 0.75em;'.PHP_EOL;
		//echo '  width: 80%;'.PHP_EOL;
		echo '  position: relative;'.PHP_EOL;
		echo '  left:-2.5em;'.PHP_EOL;
		echo '}'.PHP_EOL;
		
		echo 'input[type=submit] {'.PHP_EOL;
		echo '  padding:5px 10px; '.PHP_EOL;
		//echo '  background:#ccc; '.PHP_EOL;
		echo '  border:0 none;'.PHP_EOL;
		//echo '  cursor:pointer;'.PHP_EOL;
		//echo '  -webkit-border-radius: 5px;'.PHP_EOL;
		echo '  border-radius: 5px; '.PHP_EOL;
		echo '}'.PHP_EOL;
		echo 'textarea {'.PHP_EOL;
		echo '  width: 500px;'.PHP_EOL;
		echo '  height: 150px;'.PHP_EOL;
		echo '}'.PHP_EOL;
		echo 'required{'.PHP_EOL;
		echo '  font-size: 0.75em;'.PHP_EOL;
		echo '  color: #760000;'.PHP_EOL;
		echo '}'.PHP_EOL;
        echo '</style>'.PHP_EOL;

        echo '<a id="comboform"/><br></a>'.PHP_EOL;
	    echo '<form action="'.site_url(). '/' . esc_url( $_SERVER['REQUEST_URI'] ) . '/#comboform" method="post">'.PHP_EOL;
		if ($inpopup == 0) {
			echo '  <fieldset id="pinfo">'.PHP_EOL;
			if (!empty($_POST['cf-post_handled']))  {
                // FIXME something wrong here
				//echo __LINE__.":: Contact_form: $contact_form, Was_remembered: $was_remembered"; 
				if (($contact_form == 0) or ($was_remembered == 1)) {
		  			echo '<h2>Thank you!</h2><br>Please verify your email address by clicking the activation link that has been sent to your email.';
				}
			}
		}
		echo '    <fieldset>'.PHP_EOL;
		if ($contact_form==1) {
		echo '      <legend>Contact Form</legend>';
        } else {
		echo '      <legend>Join email list</legend>';
        }
		echo '      <p><span class="error">* required field</span></p> '.PHP_EOL;
		echo '      <p>'.PHP_EOL;
		echo '        <label class="reg_label" for="name">Name:<em class="required">*</em> </label>';
		echo '        <input type="text" id="name" name="cf-name" value="' . $name . '">'.PHP_EOL;
		echo '        <span class="error"> ' . $nameErr . '</span>'.PHP_EOL;
		echo '      </p>'.PHP_EOL;
		echo '      <p>'.PHP_EOL;
		echo '        <label class="reg_label" for="email">Email:<em class="required">*</em> </label>';
		echo '        <input type="text" id="email" name="cf-email" value="' . $email . '">'.PHP_EOL;
		echo '        <span class="error"> ' . $emailErr . '</span>'.PHP_EOL;
		echo '      </p>'.PHP_EOL;
		if ($contact_form==1) {
        if ($remember == 1){
		echo '		<p><input type="checkbox" id="remember" name="cf-remember" checked>'.PHP_EOL;
        } else {
        echo '		<p><input type="checkbox" id="remember" name="cf-remember" >'.PHP_EOL;
        }
		echo '		<label for="remember">Join Mailing List?</label></p>'.PHP_EOL;
		echo '		<p><label class="reg_label" for="text">Message: </label>'.PHP_EOL;
		echo '		<textarea name="cf-text" id="text" cols="20" rows="10" >'.$input_message.'</textarea>'.PHP_EOL;
		echo '		</p>'.PHP_EOL;
		}
		echo '	  </fieldset>'.PHP_EOL;
		echo '    <input type="submit" name="cf-submitted" value="Submit">'.PHP_EOL;
		if ($inpopup == 0) {
			echo '  </fieldset>'.PHP_EOL;
		}
		echo '</form>'.PHP_EOL;
		
	}
	/*
	if (!empty($_POST['cf-post_handled'])) {
        if (true) {
		echo '<script>'.PHP_EOL;
		echo 'if ( window.history.replaceState ) {'.PHP_EOL;
		echo '	window.history.replaceState( null, null, window.location.href );'.PHP_EOL;
		echo '}'.PHP_EOL;
		echo '</script>'.PHP_EOL;
        }
    }
	*/
}

function wga_pancake_email_form() {
	
	global $wpdb;

	/* define variables and set to empty values */
	$Err = $emailErr = "";
    $name = $email = "";
	$fullMsg = '';
    
    //echo __LINE__.":: contact_form: $contact_form remember: $remember\n";
	if ($_POST) {
		$page="post";
	}elseif($_GET) {
		$page="get";
	}else{
		$page="?";
	}
    echo '<!-- Posted1:';
    if ($_POST) {
        echo var_dump($_POST);
    }
    echo '-->';

	echo wga_console_log( __LINE__." WGA:: page:$page, fullMsg: $fullMsg" );
	//
	// set for use in or out of a plugin
	//
	if (($_SERVER["REQUEST_METHOD"] == "POST") and (empty($_POST['wga-post_handled'])))
	{
	  if (empty($_POST["wga-name"]) or empty($_POST["wga-email"])) {
	    $Err = "both fields are required";
	  } 
	  if (!empty($_POST["wga-name"])) {
	    $name = wga_test_input($_POST["wga-name"]);
      }
	  if (!empty($_POST["wga-email"])) {
	    $email = wga_test_input($_POST["wga-email"]);
	    /* check if e-mail address is well-formed */
	    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	      $emailErr = "Invalid email format";
	    } else {
            /*
			$query   = $wpdb->prepare( 
				"SELECT * FROM {$wpdb->prefix}wga_contact_list WHERE email = %s", $email 
			);
            $results = $wpdb->get_results( $query );
            */
            $results = wga_is_active_email($email);

			if ( count( $results ) > 0 ) {
				$emailErr = "Email already exits";
			}
		}
	  }
	  if ($Err == "" and $emailErr == "") {
		  
		//
		// No errors, clear the fields, email?, Database? HERE
		//
        //echo __LINE__.":: contact_form: $contact_form\n";
		
		echo wga_console_log( __LINE__." WGA:: page:$page, fullMsg: $fullMsg" ); 
		
		$noval = 0; 
		
		wga_process_input($name, $email, $noval, "", $noval);
        	
        $name = $email = "";
		
		$_POST['wga-post_handled'] = true;
		
		$fullMsg = '<span style="color: red; font-size:20px;">Thank you!</span><br>Please verify your email address by clicking the activation link that has been sent to your email.';

		//echo '<script>'.PHP_EOL;
		//echo 'if ( window.history.replaceState ) {'.PHP_EOL;
		//echo '	window.history.replaceState( null, null, window.location.href );'.PHP_EOL;
		//echo '}'.PHP_EOL;
		//echo '</script>'.PHP_EOL;

		echo wga_console_log( __LINE__." WGA:: page:$page, fullMsg: $fullMsg" ); 
      } else {
		  if ((!empty($Err)) and (!empty($emailErr))) {
			  $fullMsg = $emailErr." and ".$Err;
		  }else{
			  $fullMsg = $emailErr.$Err;
		  }
		echo wga_console_log( __LINE__." WGA:: page:$page, fullMsg: $fullMsg" ); 
	  }
	}
	
    //
	// form execution
	//
		echo wga_console_log( __LINE__." WGA:: page:$page, fullMsg: $fullMsg" ); 

	echo '	<style>'.PHP_EOL;
	echo '	#wrap{'.PHP_EOL;
	//echo '		background: #FFFFFF; /* Set content background to white */'.PHP_EOL;
	//echo '		background: #19B2E4; /* Set content background to bluish */'.PHP_EOL;
	//echo '		background: #2E115E; /* Set content background to site background */'.PHP_EOL;
	echo '		background: #262261; /* Set content background to site background */'.PHP_EOL;
	//echo '	    width: 540px; /*615px; /* Set the width of our content area */'.PHP_EOL;
	echo '	    margin: 0 auto; /* Center our content in our browser */'.PHP_EOL;
	//echo '	    margin-top: 50px; /* Margin top to make some space between the header and the content */'.PHP_EOL;
	echo '	    padding: 10px; /* Padding to make some more space for our text */'.PHP_EOL;
	//echo '	    border: 1px solid #DFDFDF; /* Small border for the finishing touch */'.PHP_EOL;
	echo '	    text-align: center; /* Center our content text */'.PHP_EOL;
	//echo '		color: #464646; /* Set global text color */'.PHP_EOL;
	echo '	}'.PHP_EOL;
	echo '	  '.PHP_EOL;
	echo '	/* Form & Input field styles */'.PHP_EOL;
	echo '	  '.PHP_EOL;
	echo '	form{'.PHP_EOL;
	//echo '	    margin-top: 10px; /* Make some more distance away from the description text */'.PHP_EOL;
	echo '	}'.PHP_EOL;
	echo '	  '.PHP_EOL;
	echo '	form .submit_button{'.PHP_EOL;
	echo '	    font: normal 16px Georgia; /* Set font for our input fields */'.PHP_EOL;
	echo '	    background: #F9F9F9; /* Set button background */'.PHP_EOL;
	echo '	    border: 1px solid #DFDFDF; /* Small border around our submit button */'.PHP_EOL;
	echo '	    padding: 8px; /* Add some more space around our button text */'.PHP_EOL;
    echo '      border-radius: 12px; /*25%;*/'.PHP_EOL;
	echo '		margen: 10px;'.PHP_EOL;
	//echo '		margen-top: 10px;'.PHP_EOL;
	echo '	}'.PHP_EOL;
	echo '	  '.PHP_EOL;
	echo '	input{'.PHP_EOL;
	echo '	    font: normal 16px Georgia; /* Set font for our input fields */'.PHP_EOL;
	//echo '	    border: 1px solid #DFDFDF; /* Small border around our input field */'.PHP_EOL;
	echo '	    padding: 0px; /* Add some more space around our text */'.PHP_EOL;
    echo '	}'.PHP_EOL;

	echo '	.flex-container {'.PHP_EOL;
	echo '	  display: flex;'.PHP_EOL;
	//echo '	  flex-direction: column;'.PHP_EOL;
	echo '	  //height: 600px;'.PHP_EOL;
	echo '	  flex-wrap: wrap;'.PHP_EOL;
	echo '	  //align-content: flex-end;'.PHP_EOL;
	echo '	  align-items: center; '.PHP_EOL;
	echo '	  justify-content: center;'.PHP_EOL;
	//echo '	  background-color: DodgerBlue;'.PHP_EOL;
	//echo '    background-color: #B2D6FF;    /* Medium blue */'.PHP_EOL;
	echo '		background: #262261; /* Set content background to site background */'.PHP_EOL;
	echo '	}'.PHP_EOL;
	echo '@media (max-width: 540px) {'.PHP_EOP; 
	echo '	.flex-container {'.PHP_EOL;
	echo '	  display: flex;'.PHP_EOL;
	echo '	  flex-direction: column;'.PHP_EOL;
	//echo '	  height: 600px;'.PHP_EOL;
	echo '	  flex-wrap: wrap;'.PHP_EOL;
	echo '	  //align-content: flex-end;'.PHP_EOL;
	echo '	  align-items: center; '.PHP_EOL;
	echo '	  justify-content: center;'.PHP_EOL;
	//echo '	  background-color: DodgerBlue;'.PHP_EOL;
	//echo '    background-color: #B2D6FF;    /* Medium blue */'.PHP_EOL;
	echo '		background: #262261; /* Set content background to site background */'.PHP_EOL;
	echo '	}'.PHP_EOL;
	echo '}'.PHP_EOL;

	echo '	.flex-container > div {'.PHP_EOL;
	echo '	  //background-color: #f1f1f1;'.PHP_EOL;
	echo '	  //width: 100px;'.PHP_EOL;
	echo '	  //margin: 10px;'.PHP_EOL;
	echo '	  text-align: center;'.PHP_EOL;
	echo '	  //line-height: 75px;'.PHP_EOL;
	echo '	  //font-size: 30px;'.PHP_EOL;
	echo '	}'.PHP_EOL;
    echo '  #innerform {'.PHP_EOL;
	//echo '	    margin-bottom: 10px; '.PHP_EOL;
	echo '		border: 1px solid #B2D6FF;    /* Medium blue */;'.PHP_EOL;
//    echo '      padding: 30px;'.PHP_EOL;
	//echo '		overflow: hidden;'.PHP_EOL;
	//echo '  	background-color: #EAEDF0;'.PHP_EOL;
    echo '	}'.PHP_EOL;
	echo '	#wrap .statusmsg {'.PHP_EOL;
    //echo '		clear: both; '.PHP_EOL;
	//echo '	    margin-top: 20px; '.PHP_EOL;
    echo '		font-size: 16px; /* Set message font size  */'.PHP_EOL;
    echo '		padding: 3px; /* Some padding to make some more space for our text  */'.PHP_EOL;
	//echo '	  	background-color: DodgerBlue;'.PHP_EOL;
	echo '		background: #262261; /* Set content background to site background */'.PHP_EOL;
    //echo '		background: #EDEDED; /* Add a background color to our status message   */'.PHP_EOL;
    //echo '		border: 1px solid red; /* #DFDFDF; /* Add a border arround our status message   */'.PHP_EOL;
	//echo '		color: red; /*#464646; /* Set msg text color */'.PHP_EOL;
	echo '		color: red; /* Set msg text color */'.PHP_EOL;
	//echo '		width: 100%; '.PHP_EOL;
    echo '	}'.PHP_EOL;
	echo '	</style>'.PHP_EOL;
	echo '	<!-- start wrap div -->  '.PHP_EOL;
	echo '	    <div id="wrap">'.PHP_EOL;
	echo '	          '.PHP_EOL;
	echo '	        <!-- start php code -->'.PHP_EOL;
	echo '	          '.PHP_EOL;
	echo '	        <!-- stop php code -->'.PHP_EOL;
	echo '	      '.PHP_EOL;
	echo '	          '.PHP_EOL;
    echo '	        <!-- start sign up form -->  '.PHP_EOL;
    echo '          <a id="pancakeform"/><br></a>'.PHP_EOL;
	                echo '<form action="'.site_url(). '/' . esc_url( $_SERVER['REQUEST_URI'] ) . '/#pancakeform" method="post">'.PHP_EOL;
    //echo '	        <form action="" method="post">'.PHP_EOL;
    echo '              <div id="innerform" class="flex-container">'.PHP_EOL;
	echo '					<div>'.PHP_EOL;
	echo '	                	<label for="name">Name:</label>'.PHP_EOL;
	echo '	                	<input type="text" id="name" name="wga-name" value="'.$name.'" />'.PHP_EOL;
	echo '					</div>'.PHP_EOL;
	echo '					<div>'.PHP_EOL;
	echo '	                	<label for="email">Email:</label>'.PHP_EOL;
	echo '	                	<input type="text" id="email" name="wga-email" value="'.$email.'" />'.PHP_EOL;
	echo '					</div>'.PHP_EOL;
	echo '	              '.PHP_EOL;
	echo '					<div>'.PHP_EOL;
	echo '	                	<input type="submit" class="submit_button" value="Join" />'.PHP_EOL;
	echo '					</div>'.PHP_EOL;
    echo '              </div>'.PHP_EOL;
	echo '				<div class="statusmsg">'.$fullMsg.'</div>'.PHP_EOL;
	echo '	        </form>'.PHP_EOL;
	echo '	        <!-- end sign up form -->'.PHP_EOL;
	echo '	          '.PHP_EOL;
	if (!empty($_POST['wga-post_handled'])) {
		echo '<script>'.PHP_EOL;
		echo 'if ( window.history.replaceState ) {'.PHP_EOL;
		echo '	window.history.replaceState( null, null, window.location.href );'.PHP_EOL;
		echo '}'.PHP_EOL;
		echo '</script>'.PHP_EOL;
	}
	echo '	    </div>'.PHP_EOL;
		echo wga_console_log( __LINE__." WGA:: page:$page, fullMsg: $fullMsg" ); 
	echo '	    <!-- end wrap div -->'.PHP_EOL;
		echo wga_console_log( __LINE__." WGA:: page:$page, fullMsg: $fullMsg" ); 
}

function wga_test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

function wga_console_log( $message ) {

    $message = htmlspecialchars( stripslashes( $message ) );
    //Replacing Quotes, so that it does not mess up the script
    $message = str_replace( '"', "-", $message );
    $message = str_replace( "'", "-", $message );

    return "<script>console.log('{$message}')</script>";
}

function wga_is_active_email($email_to_check) {
    global $wpdb;
        //$record = $wpdb->get_results("SELECT * FROM $table_name WHERE (meta_key = 'mfn-post-link1' AND meta_value = '". $from ."')");
	$query   = $wpdb->prepare( 
		"SELECT * FROM {$wpdb->prefix}wga_contact_list WHERE email = %s AND is_verified = 1 AND unsubscribed = 0", $email_to_check 
	);
    $results = $wpdb->get_results( $query );
    return $results;
}

function wga_process_input($name, $email, $remember, $input_message, $contact_form) {
    global $wpdb;
    $source = "?";

    //echo __LINE__.":: contact_form: $contact_form Remember: $remember\n";
	
	echo wga_console_log(__LINE__."wga:: remember:$remember, input:$input_message, contact_form:$contact_form");

    if ($contact_form == 0 or $remember == 1) {
		//
		// Add or update db record
		//
		$a = explode(" ", $name, 2);
		$first_name = $a[0];
        $last_name = $a[1];
		$table_name = $wpdb->prefix . 'wga_contact_list';

        //$record = $wpdb->get_results("SELECT * FROM $table_name WHERE (meta_key = 'mfn-post-link1' AND meta_value = '". $from ."')");

        $record = $wpdb->get_results("SELECT * FROM $table_name WHERE (email =  '". $email ."')");

        //print_r($record);
        echo wga_console_log(__LINE__."wga_record:: ".print_r($record,true)); 

        if (count($record)>0) {
            $id = $wpdb->get_var(NULL, 0, 0); //'id'
            $created_at = $wpdb->get_var(NULL, 6, 0); //'created_at'
		    $updated_at = current_time( 'mysql' );
		    $hash = $wpdb->get_var(NULL, 9, 0); //'vhash';
        } else {
            $id = NULL;
            $created_at = current_time( 'mysql' );
            $updated_at = NULL;
		    $hash = md5( rand(0,1000) ); // Generate random 32 character hash
		    // Example output: f4552671f8909587cf485ea990207f3b
        }
        // use replace insted of insert 
        $wpdb->replace( 
            //string $table, array $data, array|string $format = null 
			$table_name, 
			array( 
                'id' => $id,
				'first_name' => $first_name, 
				'last_name' => $last_name, 
				'email' => $email,
                'source' => $source,
                'unsubscribed' => "0",
                'created_at' => $created_at,
                'updated_at' => $updated_at,
                'is_verified' => "0",
				'vhash' => $hash, 
            ) 
         );


		//echo __LINE__.":: contact_form: $contact_form Remember: $remember\n";

		//
		// Add to db and send verification email
        //
        /*
		$table_name = $wpdb->prefix . 'wga_contact_list';
		$wpdb->insert( 
			$table_name, 
			array( 
				'first_name' => $first_name, 
				'last_name' => $last_name, 
				'email' => $email,
                'source' => $source,
                'unsubscribed' => "0",
                'created_at' => $created_at,
                'is_verified' => "0",
				'vhash' => $hash, 
			) 
        );
        */

        //echo __LINE__.":: contact_form: $contact_form\n";
        $verification_success = wga_send_verification_email($name, $email, $hash);

		echo wga_console_log(__LINE__."wga:: remember:$remember, input:$input_message, contact_form:$contact_form");
    }
	echo wga_console_log(__LINE__."wga:: remember:$remember, input:$input_message, contact_form:$contact_form");

    if ($contact_form==1 and (!empty($input_message))) { 
        $contact_success = wga_send_message($name, $email, $input_message);
    }
}

function wga_send_message($name, $email, $input_message) {
    //
	// Send HTML mail message to info@OregonOpenPrimaries.org:
	//
	$subject = "Message from OregonOpenPrimaries.org contact form"; // sanitize_text_field( $_POST["cf-subject"] );

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
                        $name . ' has sent the following message:<br><br>
                        "'.$input_message.'"<br><br>
                        Please respond to: "' .$email. '"<br><br>
                        Thanks <br>
                        <br>
                    </body>
                </html>';
	 
	$to = "info@OregonOpenPrimaries.org";
	$headers = "From: $name <$email>" . "\r\n";
	//$headers = "From: OregonOpenPrimaries.org <info@OregonOpenPrimaries.org> \r\n";
	$headers .= "Cc:OregonOpenPrimaries.org <info@OregonOpenPrimaries.org> \r\n";
	$headers .= "Cc:".get_option( 'admin_email' )." \r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html\r\n";

	$email_response = wp_mail( $to, $subject, $message, $headers );
	return $email_response;
}

function wga_send_verification_email($name, $email, $hash) {
    //
	// Send HTML mail to verify email address:
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
	return $email_response;
}

add_shortcode( 'wga_popup_email_form', 'wga_sc_email_popup' );
function wga_sc_email_popup() {
	ob_start();
    $inpopup = 1; 
    $contact_form = 0;
    wga_html_form_code($inpopup, $contact_form);
	return ob_get_clean();
}

add_shortcode( 'wga_on_page_email_form', 'wga_sc_email_on_page' );
function wga_sc_email_on_page() {
	ob_start();
    $inpopup = 0;
    $contact_form = 0;
    wga_html_form_code($inpopup, $contact_form);
	return ob_get_clean();
}

add_shortcode( 'wga_1st_contact_form', 'wga_sc_contact_form' );
function wga_sc_contact_form() {
    ob_start();
    $inpopup = 0;
    $contact_form = 1;
    wga_html_form_code($inpopup, $contact_form);
	return ob_get_clean();
}

add_shortcode( 'wga_pancake_email_form', 'wga_sc_pancake_email_form' );
function wga_sc_pancake_email_form() {
    ob_start();
	wga_pancake_email_form(); 
	return ob_get_clean();
}

?>