<?php

function wga_admin_mailings(){
    // Options menu
    if(!current_user_can('manage_options')) {
	    die('Access Denied');
    }

	//$m_id = get_option( 'initialwelcomemessageid' );
    $mail_id = -1;

    /*
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
	    if (!empty($_POST["submit"])) {
            if ($_POST["submit"] == 'Assign new message id for the Initial Welcome') {
	            if (!empty($_POST["initwelcome"])) {
	                $m_id = $_POST["initwelcome"] ;
	                add_option( 'initialwelcomemessageid', $m_id );
                    //$filterrecords = sanitize_text_field($_POST["filterrecords"]);
                }
            }
        }
    }
    echo '<pre>';
    print_r($_REQUEST);
    echo '</pre>';
    */

	echo '<div class="wrap">';
	echo '	<h2>Welcome To Mailings page</h2>';
	echo '</div>';
    echo '<br><br>';

    //
    // email list table display
    //
	WGA_Manage_Mailings::get_instance()->wga_plugin_settings_page();

    /*
    //
    // Set Message for Inital Welcome email
    //
    echo '<form method="post">';
    echo '<label for="initialwelcome" >Initial Welcome Message ID</label>';
    echo '<input id="initialwelcome" name="initwelcome" type="number" value="'.$m_id.'" >';
    submit_button("Assign new message id for the Initial Welcome");
    echo '</form>';
    */

    //
    // Create new "mailing"
    //
    if ($mail_id > 0) {
        echo "<h2 style='inline-block; background:#FFFF00; width:30%'> Mailing Created with id: $mail_id </h2>";
    }
    echo '<form method="POST">';
	wp_nonce_field( 'sp_edit_mailings_record', '_wpnonce' );
    echo '<input name="mailings_record" type="hidden" value="-1">';
    echo '<input name="action" type="hidden" value="edit">';
    submit_button("Create New Mailing");
    echo '</form>';

    /*
    //
    // Do mailing for mailing id
    //
    echo '<form method="post">';
    echo '<label for="mailingid" >Mailing ID</label>';
    echo '<input id="mailingid" name="mailing_id" type="number" >';
    submit_button("Do Mailing");
    echo '</form>';
    $my_ob_level = ob_get_level();
    $my_ob_content = ob_get_contents();
    echo '<h1> buffering level is: '.$my_ob_level.'</h1>';
    echo '<h2> buffering content: "'.$my_ob_content.'"</h2>';
    return;
    require_once('githubcsv.php');
    //
    // Future options:
    //   drop table on uninstall, maybe add time limit (or explicit drop done
    //     done anytime = then would need table add on use or the likes)
    //   
    */
}

function get_email_counts($records) {
    $all = 0;
    $active = 0;
    $unverified = 0;
    $unsubscribed = 0;
	foreach ($records as $record) {
        $all++;
        if ($record["is_verified"] == 0) {
            $unverified++;
        }
        if ($record["unsubscribed"] == 1) {
            $unsubscribed++;
        }
        if (($record["is_verified"] == 1) && ($record["unsubscribed"] == 0)) {
            $active++;
        }
    }
    return array(
        'all' => $all,
        'active' => $active,
        'unverified' => $unverified, 
        'unsubscribed' => $unsubscribed,
    );
}

add_action( 'admin_post_generate_csv', 'csv_download_filtered_table' );

function csv_download_filtered_table(/*$filterrecords, $list*/) {
    //https://developer.wordpress.org/reference/hooks/admin_post_action/
    // The above page explains how to connect this function to the 
    // form post.
    status_header(200);

    $filterrecords = 'all';
    if (!empty($_POST["data1"])) {
        $filterrecords = $_POST["data1"];
    }
    $list = get_email_list(); 
    $filename = "wga-email-list-" . time() . ".csv";
    // # add MIME types at the header
    if (true /*orig*/) {
        header('Content-Type: text/csv; charset=UTF-8;');  // browser the is UTF8 CSV file
        header('Content-Disposition: attachment; filename='. $filename );    // tell the browser to let the viewers can download the file with the default filename as provided.
    } elseif (false /*https://phppot.com/php/php-csv-file-export-using-fputcsv/*/) {
        header('Content-type: application/csv');
        header('Content-Disposition: attachment; filename='.$filename);
    } else /*githubcsv.php*/ {
        header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
        header( 'Content-Description: File Transfer' );
        header( 'Content-type: text/csv' );
        header( "Content-Disposition: attachment; filename={$filename}" );
        header( 'Expires: 0' );
        header( 'Pragma: public' ); 
    }
    
    $fh = @fopen( 'php://output', 'w' );
    // I add these 3 byte UTF8 here before print out the first line to CSV file.
    fprintf( $fh, chr(0xEF) . chr(0xBB) . chr(0xBF) );

    $header_row = array(
        'ID',
        'First Name',
        'Last Name',
        'Email',
        'Source',
        'Unsubscribed',
        'Created_at',
        'Updated_at',
        'Is Verified?',
        'Is SPAM?',
        'Hash',
        'Update Record by ID',
    );
    fputcsv( $fh, $header_row );

	foreach ($list as $record) {
        $dorecord = 0;
        if ($filterrecords == "all") {
            $dorecord = 1;
        } elseif ($filterrecords == "active") {
	        if (($record["is_verified"] == 1) && ($record["unsubscribed"] == 0)){
                $dorecord = 1;
            } 
        } elseif ($filterrecords == "unverified") {
	        if ($record["is_verified"] == 0) {
                $dorecord = 1;
            }
        } elseif ($filterrecords == "unsubscribed") {
	        if ($record["unsubscribed"] == 1) {
                $dorecord = 1;
            }
        }
        if ($dorecord == 1) { 
            fputcsv( $fh, $record );
        }
    }
    fclose( $fh );

    exit; // exit or die() after the last print out content to the CSV file.
          // otherwise, you may see all the current HTML code will print out to the CSV file too.
}

add_action( 'admin_post_submit_content', 'csv_submission_processor' );

function csv_submission_processor() {
	// Handle the form in here
    if ( ! isset( $_POST['submit_and_update_table'] )
        || ! wp_verify_nonce( $_POST['submit_and_update_table'], 'submit_content' )) {
        wp_nonce_ays( '' );
    } 
    if (false) {
	    $target_dir = wp_upload_dir();
	    $target_file = $target_dir . basename($_FILES["cvsfile"]["name"]);
	    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
	    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
	        echo "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded.";
	    } else {
	        echo "Sorry, there was an error uploading your file.";
	    }
	    wp_redirect( $_POST["current_url"] );
	    //wp_redirect( site_url() . '/thank-you/' );
	    die();
    }else {
	     $upload = wp_upload_bits( $_FILES['csvfile']['name'], null, file_get_contents( $_FILES['csvfile']['tmp_name'] ) );
	
        /*
	    //echo '<h2> error: '.$upload['error'].' file: '.$upload['file'].' url: '.$upload['url'].' </h2>';
	    $wp_filetype = wp_check_filetype( basename( $upload['file'] ), null );
        echo '<h2> current_url:'.$_POST['current_url'];
        */

        wga_update_table_from_csvfile($upload['file']);
        // File is unlink'd at end of above function

	    wp_redirect( $_POST["current_url"] );
	    die();
    }
}

function wga_update_table_from_csvfile($file) {
    //
    // csv file format based on csv download format. The file contains a 
    // title row followed by the data rows. Each row starts with an id 
    // field. If the row represents a new record the id field should be 
    // blank. The last field is blank on download but can be set to '1' in 
    // the case the row has been modified and is intended to update the
    // datebase record with the same id field. Rows without this field set 
    // will be inserted into the table if the email field value is not 
    // already present in the table. If it is present the row will be 
    // ignored. 
    //
    $row = 1;
    if (($handle = fopen($file, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            /*
            $num = count($data);
            echo "<p> $num fields in line $row: <br /></p>\n";
            $row++;
            for ($c=0; $c < $num; $c++) {
                echo $data[$c] . "<br />\n";
            }
            */
            // Check there is a value email or ignore the row.
            $vemail = $data[3];
            if(strpos($vemail, "@") !== false){
                wga_insert_or_update_record($data);
            }
        }
        fclose($handle);
    } 
    unlink($file);
}

function wga_insert_or_update_record($row_data) {
    global $wpdb;
    $id = $row_data[0];
	$updated_at = current_time( 'mysql' );
    //echo 'CREATED_AT ROW_DATA[6] '.  $row_data[6];
    //
    // field index 10 is Update Record by ID
    //
    $operation_control_field = $row_data[11];
    if ($operation_control_field == 1) {
        //echo 'UPDATING record with ID: '.$row_data[0];
		if ($wpdb->update(
			"{$wpdb->prefix}wga_contact_list",
			array( // data
                'first_name' => $row_data[1],
                'last_name' => $row_data[2],
                'email' => $row_data[3],
                'source' => $row_data[4], 
                'unsubscribed' => $row_data[5],
                'created_at' => date("Y-m-d H:i:s", strtotime($row_data[6])),
				'updated_at' => $updated_at,
                'is_verified' => $row_data[8],
                'is_spam' => $row_data[9],
                'vhash' => $row_data[10],
			),
			array( //where
				'id' => $id,
			)
		) 
		== 1) 
		{
			//echo '<div class="statusmsg">Updated record '.$row_data[0].'</div>';
            return true;
		} else {
            return false;
        }
    
    }elseif ($operation_control_field == 2) {
        //
        // operation_control_file = 2 is Insert record as is 
        //  (i.e. restore data) 
        //  May need to add hash
        //
        //echo 'INSERTING old record with ID: '.$row_data[0];
		    $table_name = $wpdb->prefix . 'wga_contact_list';
            if ($row_data[10] == '') {
		        $row_data[10] = md5( rand(0,1000) ); // Generate random 32 character hash
            }
		    $wpdb->insert( 
			    $table_name, 
			    array( 
                'id' => $row_data[0],
				'first_name' => $row_data[1],
				'last_name' => $row_data[2],
				'email' => $row_data[3],
                'source' => $row_data[4],
                'unsubscribed' => $row_data[5],
                'created_at' => date("Y-m-d H:i:s", strtotime($row_data[6])),
                'updated_at' => date("Y-m-d H:i:s", strtotime($row_data[7])),
                'is_verified' => $row_data[8],
                'is_spam' => $row_data[9],
				'vhash' => $row_data[10], 
			    ) 
            );
            //
            // REturn status????
            // 
    }elseif (empty($id)) {
        //
        // No id field so this entry is new
        //
        //echo 'INSERTING new record with no ID';
		    $table_name = $wpdb->prefix . 'wga_contact_list';
		    $wpdb->insert( 
			    $table_name, 
			    array( 
				'first_name' => $row_data[1],
				'last_name' => $row_data[2],
				'email' => $row_data[3],
                'source' => $row_data[4],
                'unsubscribed' => $row_data[5],
                'created_at' => date("Y-m-d H:i:s", strtotime($row_data[6])),
				'updated_at' => $updated_at,
                'is_verified' => $row_data[8],
				'is_spam' => $row_data[9], 
				'vhash' => $row_data[10], 
			    ) 
            );
            //
            // REturn status????
            // 
    }else{
        //echo 'ignore record';
        //
        // Don't update existing record without field 10 directive.
        //
        return false;
    }
}

function wga_admin_manage() {
    // Manage menu
    $filterrecords = "all";

    if(!current_user_can('manage_options')) {
	    die('Access Denied');
    }

    $list = get_email_list(); 
    $count_array = get_email_counts($list);
   
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
	    if (!empty($_POST["filterrecords"])) {
            $filterrecords = sanitize_text_field($_POST["filterrecords"]);
        }elseif (!empty($_POST["downloadtable"])) {
            add_action( 'wga_admin_init', 'csv_download_filtered_table',10,2);
            do_action( 'wga_admin_init', $filterrecords, $list );
            //do_action( 'wga_admin_init', $filterrecords, $list );
            //csv_download_filtered_table($filterrecords, $list);
        }elseif (!empty($_POST["submit"]) && 
            $_POST["submit"] == 'Save Modified Record'){
            if (!empty($_REQUEST['email_record']) ) {
                $id = $_REQUEST['email_record'];
                $fname = $_POST['fname'];
                $lname = $_POST['lname'];
                $email = $_POST['email'];
                $source = $_POST['source'];
                $unsub = $_POST['unsubscribed'];
                $is_ver = $_POST['is_verified'];
                $is_spam = $_POST['is_spam'];
	            WGA_Manage_Email::get_instance()->email_list_obj->edit_update_email_record( $id, $fname, $lname, $email, $source, $unsub, $is_ver, $is_spam ); 
            }
        }
    }

    //
    // message list table display
    //
    echo '<h2> WGA Collect Email List Manage Page </h2>';

    echo '<style>';
    echo 'tr:nth-child(even){background-color: lightblue}';
    /* Darker background on mouse-over */
    echo '.button button-primary:hover {';
    //echo '  background-color: RoyalBlue;';
    echo '  background-color: lightblue;';
    echo '}';
    echo '</style>';

    echo '<p><h3>Table contains '.count($list).' records, '.$count_array['active'].' active, '.$count_array['unverified'].' unverified, '.$count_array['unsubscribed'].' unsubscribed </h3></p>';

    //
    // Radio button selector for filter
    //
    echo '<div style="display:inline-block; width:95%">';//container of radio and download
	echo '<div class="container" style="float: left;">';//container of radio
	echo '  <h2>Display records:</h2>';
	echo '  <form action="" method="post">'.PHP_EOL;
	//echo '  <form>';
	echo '    <label for="all" class="radio-inline">';
    $t1 = ($filterrecords == "all") ? "checked" : "";
	echo '      <input id="all" type="radio" name="filterrecords" value="all" onChange="this.form.submit();" '. $t1 .' >All';
	echo '    </label>';
	echo '    <label for="active" class="radio-inline">';
    $t1 = ($filterrecords == "active") ? "checked" : "";
	echo '      <input id="active" type="radio" name="filterrecords" value="active" onChange="this.form.submit();"'.$t1.' >Active';
	echo '    </label>';
	echo '    <label for="unverified" class="radio-inline">';
    $t1 = ($filterrecords == "unverified") ? "checked" : "";
	echo '      <input id="unverified" type="radio" name="filterrecords" value="unverified" onChange="this.form.submit();" '.$t1.'>Unverified';
	echo '    </label>';
	echo '    <label for="unsubscribed" class="radio-inline">';
    $t1 = ($filterrecords == "unsubscribed") ? "checked" : "";
	echo '      <input id="unsubscribed" type="radio" name="filterrecords" value="unsubscribed" onChange="this.form.submit();" '.$t1.'>Unsubscribed';
	echo '    </label>';
	echo '  </form>';
	echo '</div>';

    //
    // Download form / button
    //
	echo '<div style="float: right;">';// container of download button
    $myaction=admin_url( 'admin-post.php' );
    echo '<form action="'.$myaction.'" method="post">';
    echo '  <input type="hidden" name="data1" value="'.$filterrecords.'">';
    echo '  <input type="hidden" name="data2" value="foobarid2">';
    echo '  <input type="hidden" name="action" value="generate_csv" />';
    echo '  <input type="submit" name="submit" class="button button-primary" value="Generate & Download CSV File" />';
    //echo '  <button type="submit" class="btn" name="downloadtable" value="Download1" /><i class="fa fa-download"></i> Download</button>';
    echo '</form>';
	echo '</div>';//download container
	echo '</div>';//radio & download container

    echo '<br>';
    //
    // email list table display
    //
	WGA_Manage_Email::get_instance()->wga_plugin_settings_page();


    if (!empty($_GET['message']) && (!empty($_GET['action']) && $_GET['action']=='edit')) {
        // message list table normally would process 'edit' and its nonce 
        // but to edit on this page we do it here
		$nonce = esc_attr( $_REQUEST['_wpnonce'] );
		if ( ! wp_verify_nonce( $nonce, 'sp_edit_email_record' ) ) {
			die( 'Go get a life script kiddies' );
		}
		else {
            $edit_id = absint($_GET['email_record']);
            $m_record = wga_fetch_message($edit_id); 
            if ($m_record) {
                $editor_content = $m_record->message_content;
                $editor_subject = $m_record->message_subject;
                $m_id = $edit_id;
                $edit_id = -1;
            }
        }
    }

    //
    // upload form
    //
    $current_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	//echo '<H2> Current URL: '.$current_url .' </h2>';
    //#006d9e;
?>

    <form action="<?php echo admin_url( 'admin-post.php' ) ?>" method="post" enctype="multipart/form-data">
    <fieldset style="width:95%">

	<?php wp_nonce_field( 'submit_content', 'submit_and_update_table' ); ?>

	<p>
		<input type='file' name='csvfile' accept='csv'>
	</p>

	<p>
		<input type='hidden' name='action' value='submit_content'>
		<input type='hidden' name='current_url' value='<?php echo $current_url ?>' >
        <input type="submit" name="submit" value="Submit CSV File Content" />
	</p>
    <p> CSV file download can act as a template for submitted content. New records should leave the ID field empty. This field will be assigned on entry to the table. Current records to be modified/updated should have a '1' in the final column. Records that should be entered without any changes (i.e., the Updated_at field) should have a '2' in the final column. However, this operation will fail if a record with the same id or email already exists.  All other records will be ignored. </p>
    </fieldset>
</form>
<?php

}

function wga_update_message($id, $subject, $content) {
    global $wpdb;
    //echo 'UPDATING record with ID: '.$id;
	if ($wpdb->update(
		"{$wpdb->prefix}wga_message_list",
		array( // data
			'message_subject' => $subject,
			'message_content' => $content,
            'message_updated_at' => current_time( 'mysql' ),
		),
		array( //where
			'message_id' => $id,
		)
	) == 1) {
		//echo '<div class="statusmsg">Updated record '.$row_data[0].'</div>';
        return true;
	} 
    return false;
}

function wga_insert_update_mailing($id, $mess_id, $verified, $spam, $unsub, $start) {
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

function wga_insert_message($subject, $content) {
    global $wpdb;

        //echo 'INSERTING new record with no ID yet';
		    $table_name = $wpdb->prefix . 'wga_message_list';
		$result = $wpdb->insert( 
			    $table_name, 
			    array( 
				'message_subject' => $subject,
				'message_content' => $content,
                'message_created_at' => current_time( 'mysql' ),
			    ) 
            );
        if ($result) {
            $sql_cmd = $wpdb->prepare("SELECT message_id FROM {$wpdb->prefix}wga_message_list WHERE (message_subject = %s AND message_content = %s)", $subject, $content);
            $results = $wpdb->get_results( $sql_cmd );
            $result = $results[0]->message_id;
        }
        return $result;
}

function wga_fetch_message($edit_id) {
    global $wpdb;
    $sql_cmd = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}wga_message_list WHERE (message_id = %s)", $edit_id);
    $results = $wpdb->get_results( $sql_cmd );
    if (!$results) {
        return 0;
    }
    return $results[0];
    //$result = $results[0]->message_id;
}

// 10 is the priority, higher means executed first
// 1 is number of arguments the function can accept
add_action('wga_initial_welcome_email_hook', 'wga_send_initial_email', 10, 1);

function wga_send_initial_email($email_id) {
    global $wpdb;
    //
	// Send HTML email - Initial email
	//
    echo wga_console_log(__LINE__."send_initial_id:: ".$email_id); 

	$m_id = get_option( 'initialwelcomemessageid' );
    $sql_cmd1 = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}wga_message_list WHERE (message_id = %d)", $m_id);
    $mresults = $wpdb->get_results( $sql_cmd1 );

    $subject = stripslashes($mresults[0]->message_subject);
    $content = stripslashes($mresults[0]->message_content);

    $sql_cmd2 = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}wga_contact_list WHERE (id = %d)", $email_id);
    $email_results = $wpdb->get_results( $sql_cmd2 );

    $first_name = $email_results[0]->first_name;
    $last_name = $email_results[0]->last_name;
    $email = $email_results[0]->email;

    $message = '<html>
                    <head>
                        <style type=“text/css”>
                        </style>
                    </head>
                    <body>
                        <img width="600" src="'.site_url().'/wp-content/uploads/2020/12/LogoOregonOpenPrimaries.png" alt="Let ALL voters vote!"/><br><br>
                        <br><br>
                        '.$content.'
                    </body>
                </html>';
	// get the blog administrator's email address
	//$to = get_option( 'admin_email' );
	
    // test code - button code generated at: https://buttons.cm/
    /*
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
                            <v:roundrect            xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="'.site_url().'/verify.php?subscribe=join&email='.$email.'&vhash='.$hash.'" style="height:50px;v-text-anchor:middle;width:350px;" arcsize="8%" strokecolor="#262661" fillcolor="#262661">
                                <w:anchorlock/>
                                <center style="color:#FFEA0F;font-family:sans-serif;font-size:13px;font-weight:bold;">
                                    Yes, subscribe me to Oregon Open Primaries!
                                </center>
                            </v:roundrect>
                            <![endif]--><a href="'.site_url().'/verify.php?subscribe=join&email='.$email.'&vhash='.$hash.'" 
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
	*/ 
	$to = "$first_name $last_name <$email>";
	//$headers = "From: $name <$email>" . "\r\n";
	$headers = "From: OregonOpenPrimaries.org <info@OregonOpenPrimaries.org> \r\n";
	//$headers .= "Cc:OregonOpenPrimaries.org <info@OregonOpenPrimaries.org> \r\n";
	//$headers .= "Cc:".get_option( 'admin_email' )." \r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html\r\n";

	// original: $email_response = wp_mail( $to, $subject, $message, $headers );
	$email_response = wp_mail( $to, $subject, $message, $headers );
	//$email_response = wp_mail( $to, $subject, $content, $headers );

	return $email_response;
}

function wga_send_mailings_email($mailings_id) {
    $err_msg = "";
    global $wpdb;
    //
	// Send HTML email - Email Mailings
	//

    $err_msg =  '<H2> in send_mailings_email() </h2>';
    //
    // Fetch mailings details
    //
    $sql_cmd0 = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}wga_mailings_list WHERE (mailings_id = %d)", $mailings_id);
    $mailings_details = $wpdb->get_results( $sql_cmd0 );
    if (!isset($mailings_details)) {
        // no mailings with the given id
        $err_msg .=  '<h2> mailings_details empty </h2>';
        return $err_msg;
    }
    $start = stripslashes($mailings_details[0]->mailings_start_date);
    $now = current_time( 'mysql' );
    if ($start > $now) {
        // now schedule to start yet
        $err_msg .=  '<h2> start later than now </h2>';
        return $err_msg;
    }
    $m_id = stripslashes($mailings_details[0]->mailings_message_id);
    $verified = stripslashes($mailings_details[0]->mailings_verified);
    $spam = stripslashes($mailings_details[0]->mailings_spam);
    $unsub = stripslashes($mailings_details[0]->mailings_unsubscribed);

    $sql_cmd1 = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}wga_message_list WHERE (message_id = %d)", $m_id);
    $mresults = $wpdb->get_results( $sql_cmd1 );

    if (!$mresults) {
        $err_msg .= "<h2>Message id: ".$m_id." is not present.</h2>";
        return $err_msg;
    }

    $subject = stripslashes($mresults[0]->message_subject);
    $content = stripslashes($mresults[0]->message_content);

    $where_str = "";
    if ($verified == "true") {
        $where_str .= " is_verified = 1 ";
    }else if ($verified == "false") {
        $where_str .= " is_verified = 0 ";
    }
    $cmd_len = strlen($where_str);
    $and_str = "";
    if ($cmd_len > 0 ) {
        $and_str .= " AND ";
    }
    if ($spam == "true") {
        $where_str .= $and_str . " is_spam = 1 ";
    }else if ($spam == "false") {
        $where_str .= $and_str . " is_spam = 0 ";
    }
    $and_str = "";
    if (strlen($where_str) > $cmd_len ) {
        $and_str .= " AND ";
    }
    if ($unsub == "true") {
        $where_str .= $and_str . " unsubscribed = 1 ";
    }else if ($unsub == "false") {
        $where_str .= $and_str . " unsubscribed = 0 ";
    }
    if (strlen($where_str) > 0 ) {
        $where_str = "WHERE (".$where_str.")";
    }
    echo "<h2> $where_str </h2>";
    
    $sql_cmd2 = "SELECT * FROM {$wpdb->prefix}wga_contact_list ".$where_str;
    $email_results = $wpdb->get_results( $sql_cmd2 );

    foreach ($email_results as $contact) {
	
	    $first_name = $contact->first_name;
	    $last_name = $contact->last_name;
	    $email = $contact->email;

        // Substitute db values for content macros
        $search = array();
        $replace = array();
        foreach($contact as $col => $val) {
            $search[] = "$$".$col."$$";
            $replace[] = $val;
            //echo "<h2> $col => $val </h2>";
        }
        $search[] = "$$"."site_url$$";
        $replace[] = site_url();
        //foreach($search as $key => $val) { echo "<h2> search[$key]: $val </h2>"; }
        $new_content = str_replace($search, $replace, $content);
        $new_subject = str_replace($search, $replace, $subject);
	
	    $message = '<html>
	                    <head>
	                        <style type=“text/css”>
	                        </style>
	                    </head>
	                    <body>
	                        <img width="600" src="'.site_url().'/wp-content/uploads/2020/12/LogoOregonOpenPrimaries.png" alt="Let ALL voters vote!"/><br><br>
	                        <br><br>
	                        '.$new_content.'
	                    </body>
	                </html>';
		// get the blog administrator's email address
		//$to = get_option( 'admin_email' );
		
	    // test code - button code generated at: https://buttons.cm/
	    /*
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
	                            <v:roundrect            xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="'.site_url().'/verify.php?subscribe=join&email='.$email.'&vhash='.$hash.'" style="height:50px;v-text-anchor:middle;width:350px;" arcsize="8%" strokecolor="#262661" fillcolor="#262661">
	                                <w:anchorlock/>
	                                <center style="color:#FFEA0F;font-family:sans-serif;font-size:13px;font-weight:bold;">
	                                    Yes, subscribe me to Oregon Open Primaries!
	                                </center>
	                            </v:roundrect>
	                            <![endif]--><a href="'.site_url().'/verify.php?subscribe=join&email='.$email.'&vhash='.$hash.'" 
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
		*/ 
		$to = "$first_name $last_name <$email>";
		//$headers = "From: $name <$email>" . "\r\n";
		$headers = "From: OregonOpenPrimaries.org <info@OregonOpenPrimaries.org> \r\n";
		//$headers .= "Cc:OregonOpenPrimaries.org <info@OregonOpenPrimaries.org> \r\n";
		$headers .= "Bcc:".get_option( 'admin_email' )." \r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html\r\n";
	
		$email_response = mail( $to, $new_subject, $message, $headers );
        if (!$email_response) {
            // Error, something went wrong, tell someone, some how
            $err_msg .=  "<h2>&& $to failed &&</h2>";
            return $err_msg;
        }

        $err_msg .=  "<h2> @ $first_name $last_name $email @ </h2>";
    }
    return $err_msg;
}

function wga_admin_messages() {
    $m_id = -1;
    $im_id = -1;
    $m_saved = 0;
    $edit_id = -1;
    $ErrStr = '';

    if(!current_user_can('manage_options')) {
	    die('Access Denied');
    }

    $editor_content = "";
    $editor_subject = "";
    $have_title = $have_content = 0;
	$im_id = get_option( 'initialwelcomemessageid' );

	if ($_SERVER["REQUEST_METHOD"] == "POST") {
	    if (!empty($_POST["wga_message_id"])) {
            $m_id = $_POST['wga_message_id'];
        }
	    if (!empty($_POST["wga_message_content"])) {
            //$editor_content = esc_sql($_POST['wga_message_content']);
            $editor_content = stripslashes($_POST['wga_message_content']);
            $have_content = 1;
        }
	    if (!empty($_POST["wga_message_subject"])) {
            //$editor_subject = esc_sql($_POST['wga_message_subject']);
            $editor_subject = stripslashes($_POST['wga_message_subject']);
            $have_title = 1;
        }
        if (!empty($_POST['wga_edit_id'])) {
            $edit_id = $_POST['wga_edit_id'];
        }
        if (!empty($_POST['submit'])) {
            if ($_POST["submit"] == 'Assign new message id for the Initial Welcome') {
	            if (!empty($_POST["initwelcome"])) {
	                $im_id = $_POST["initwelcome"] ;
                    echo "<h1>assigning $im_id to initial welcome </h1>";
	                update_option( 'initialwelcomemessageid', $im_id );
                }
            } elseif ($_POST['submit']=='Save content'){
                if ($have_title != 1) {
                    $ErrStr = 'To Save the content you must first give it a subject.';
                }else{
                    $m_id = wga_insert_message($editor_subject, $editor_content);
                    //$ErrStr = 'NOT AN ERROR: message_id: '. $m_id;
                    if (!$m_id) {
                        $ErrStr = 'insert_message() failed';
                    }
                }
            }elseif ($_POST['submit']=='Update content'){
                    $success = wga_update_message($m_id, $editor_subject, $editor_content);
                    if (!$success) {
                        $ErrStr = 'update_message(id: '.$m_id.') failed';
                    }
            }elseif ($_POST['submit']=='Delete'){
            }elseif ($_POST['submit']=='Add new'){
                if (/*editor content changed*/ $m_saved == 0 ){
                    // has the current message, if there is one, been saved?
                    // is there a specific message id requested?
                    // is it different than the current m_id?
                    // if all is safe load the new message from the db
                    if ($edit_id > 0) {
                        $m_record = wga_fetch_message($edit_id); // need to check fetch success
                        //$result = $m_record->message_id;
                        if ($m_record) {
                            $editor_content = $m_record->message_content;
                            $editor_subject = $m_record->message_subject;
                            $m_id = $edit_id;
                            $edit_id = -1;
                        }
                    }else{
                        $m_id = -1;
                        $editor_content = "";
                        $editor_subject = "";
                        $have_title = $have_content = 0;
                    }
                }
            }
        }
    }

    echo '<h1> Message page </h1>';

    //echo '<pre>'; print_r($_REQUEST); echo '</pre>';
    //
    // Set Message for Inital Welcome email
    //
    echo '<form method="post">';
    echo '<label for="initialwelcome" >Initial Welcome Message ID</label>';
    echo '<input id="initialwelcome" name="initwelcome" type="number" value="'.$im_id.'" >';
    submit_button("Assign new message id for the Initial Welcome");
    echo '</form>';

    //
    // message list table display
    //
    echo '<style>';
    echo 'tr:nth-child(even){background-color: lightblue}';
    echo '</style>';
	WGA_Messages::get_instance()->wga_plugin_settings_page();

    if (!empty($_GET['message']) && (!empty($_GET['action']) && $_GET['action']=='edit')) {
        // message list table normally would process 'edit' and its nonce 
        // but to edit on this page we do it here
		$nonce = esc_attr( $_REQUEST['_wpnonce'] );
		if ( ! wp_verify_nonce( $nonce, 'sp_edit_message' ) ) {
			die( 'Go get a life script kiddies' );
		}
		else {
            $edit_id = absint($_GET['message']);
            $m_record = wga_fetch_message($edit_id); 
            if ($m_record) {
                $editor_content = $m_record->message_content;
                $editor_subject = $m_record->message_subject;
                $m_id = $edit_id;
                $edit_id = -1;
            }
        }
    }
    // 
    // message editing section below
    //
	$subject_args = array(
	    'textarea_rows' => 1,
	    'teeny' => true,
	    'quicktags' => false
	);
	$letter_args = array(
	    'textarea_rows' => 15,
	);

    echo '<form method="post">';
    if ($ErrStr != '') {
        echo 'Error: '.$ErrStr;
    }
    if ($m_id > 0) {
        echo '<div style="width:50%;" >';
        echo '<div style="display: inline-block"> ';
        echo '<h2>Currently editing message id: '.$m_id.'  </h2>';
        echo '</div>';
        echo '<div style="display: inline-block; float: right;"> ';
        echo '<form method="post">';
        echo '<input name="wga_edit_id" id="m_id" type="hidden" value="-1">';
	    submit_button( 'Edit new message' );
        echo '</form>';
        echo '</div>';
        echo '</div>';
    }else {
        echo '<h2>Currently editing new message</h2>';
    }

    echo '<div style="width:95%;">';
    echo '<form method="post">';
    echo '<input type="hidden" name="wga_message_id" value="'.$m_id.'">';
    echo '<input type="hidden" name="wga_message_saved" value="'.$m_id.'">';
    echo '<label for="subject" ><h2>Letter Subject:</h2></label>';
    echo '<input name="wga_message_subject" id="subject" type="text" size="60" value="'.$editor_subject.'">';
    echo '<br>';
    echo '<br>';
    wp_editor( $editor_content, 'wga_message_content', $letter_args );
    if ($m_id > 0){
	    submit_button( 'Update content' );
    }else {
	    submit_button( 'Save content' );
    }
    echo '</form>';
    echo '</div>';
}

?>