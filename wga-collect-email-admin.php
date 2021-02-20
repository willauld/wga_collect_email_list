<?php


function wga_admin_options(){
    // Options menu
    if(!current_user_can('manage_options')) {
	    die('Access Denied');
    }
	echo '<div class="wrap">';
	echo '	<h2>Welcome To My Options page</h2>';
	echo '</div>';

    /*
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

	    wp_redirect( $_POST["current_url"] );
	    die();
    }
}

function wga_update_table_from_csvfile($file) {
    //
    // csv file format based on csv download format. The file contains a title row
    // followed by the data rows. Each row starts with an id field. If the row 
    // represents a new record the id field should be blank. The last field is blank
    // on download but can be set to '1' in the case the row has been modified and is
    // intended to update the datebase record with the same id field. Rows without this
    // field set will be inserted into the table if the email field value is not already
    // present in the table. If it is present the row will be ignored. 
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
            wga_insert_or_update_record($data);
        }
        fclose($handle);
    } 
}

function wga_insert_or_update_record($row_data) {
    global $wpdb;
    $id = $row_data[0];
	$updated_at = current_time( 'mysql' );
    echo 'CREATED_AT ROW_DATA[6] '.  $row_data[6];
    if ($row_data[10] == 1) {
        // field index 10 is Update Record by ID
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
                'vhash' => $row_data[9],
			),
			array( //where
				'id' => $id,
			)
		) 
		== 1) 
		{
			echo '<div class="statusmsg">Updated record '.$row_data[0].'</div>';
            return true;
		} else {
            return false;
        }
    
    }elseif (empty($id)) {
        //
        // Need to check if email is already in the table before insert!!!!
        //
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
				'vhash' => $row_data[9], 
			    ) 
            );
            //
            // REturn status????
            // 
    }else{
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
        }
        if (!empty($_POST["downloadtable"])) {
            add_action( 'wga_admin_init', 'csv_download_filtered_table',10,2);
            do_action( 'wga_admin_init', $filterrecords, $list );
            //do_action( 'wga_admin_init', $filterrecords, $list );
            //csv_download_filtered_table($filterrecords, $list);
        }
    }

    echo '<h2> WGA Collect Email List Manage Page </h2>';
    echo '<!-- Add icon library -->';
    echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">';
    echo '<style>';
    echo 'div.ex3 {';
    //echo '  background-color: lightblue;';
    echo '  height: 350px;';
    echo '  overflow: auto;';
    echo '}';

    echo 'table {';
    echo '  border-collapse: collapse;';
    echo '  border-spacing: 0;';
    echo '  width: 95%;';
    echo '  border: 1px solid #ddd;';
    echo '}';

    echo 'th, td {';
    echo '  text-align: left;';
    echo '  padding: 8px;';
    echo '}';
    echo 'tr:nth-child(even){background-color: lightblue}';

    /* Darker background on mouse-over */
    echo '.button button-primary:hover {';
    //echo '  background-color: RoyalBlue;';
    echo '  background-color: lightblue;';
    echo '}';
    echo '</style>';
    //echo '<hr>';
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

    echo '<div class="ex3" style="overflow-x:auto; width:95%">';
    echo '<table style="width:95%">';
    echo '  <tr>';
    echo '      <th>ID</th>';
    echo '      <th>First Name</th>';
    echo '      <th>Last Name</th>';
    echo '      <th>Email</th>';
    echo '      <th>Source</th>';
    echo '      <th>Unsubscribed</th>';
    echo '      <th>Created_at</th>';
    echo '      <th>Updated_at</th>';
    echo '      <th>Is Verified?</th>';
    echo '      <th>Hash</th>';
    echo '  </tr>';

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
	        echo '<tr>';
	        echo '  <td>'.$record["id"].'</td>';
	        echo '  <td>'.$record["first_name"].'</td>';
	        echo '  <td>'.$record["last_name"].'</td>';
	        echo '  <td>'.$record["email"].'</td>';
	        echo '  <td>'.$record["source"].'</td>';
	        echo '  <td>'.$record["unsubscribed"].'</td>';
	        echo '  <td>'.$record["created_at"].'</td>';
	        echo '  <td>'.$record["updated_at"].'</td>';
	        echo '  <td>'.$record["is_verified"].'</td>';
	        echo '  <td>'.$record["vhash"].'</td>';
	        echo '</tr>';
        }
    }
    echo '</table>';
	echo '</div>'; //table container

    //
    // upload form
    //
    $current_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	//echo '<H2> Current URL: '.$current_url .' </h2>';
    //#006d9e;
?>

    <style>
    ::-webkit-file-upload-button {
        background: #0570c7; 
        color: white;
        padding: 1em;
        border-radius: 6px;
    }
    </style>

    <form action="<?php echo admin_url( 'admin-post.php' ) ?>" method="post" enctype="multipart/form-data">
    <fieldset style="width:95%">

	<?php wp_nonce_field( 'submit_content', 'my_nonce_field' ); ?>

	<p>
		<input type='file' name='csvfile' accept='csv'>
	</p>

	<p>
		<input type='hidden' name='action' value='submit_content'>
		<input type='hidden' name='current_url' value='<?php echo $current_url ?>' >
        <input type="submit" name="submit" class="button button-primary" value="Submit CSV File Content" />
	</p>
    <p> CSV file download can act as a template for submitted content. New records should leave the ID field empty. Current records to be modified/updated should have a '1' in the final column. All other records will be ignored. </p>
    </fieldset>
</form>
<?php

}



function wga_admin_campaign() {
    // Donate menu
    if(!current_user_can('manage_options')) {
	    die('Access Denied');
    }
    echo '<h1> Campaign page </h1>';
}

?>