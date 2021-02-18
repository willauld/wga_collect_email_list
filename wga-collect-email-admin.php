<?php


function wga_admin_options(){
    // Options menu
    if(!current_user_can('manage_options')) {
	    die('Access Denied');
    }
	echo '<div class="wrap">';
	echo '	<h2>Welcome To My Options page</h2>';
	echo '</div>';

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
        'last Name',
        'Email',
        'Source',
        'Unsubscribed',
        'Created_at',
        'Updated_at',
        'Is Verified?',
        'Hash',
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
    echo '  height: 400px;';
    echo '  overflow: auto;';
    echo '}';

    echo 'table {';
    echo '  border-collapse: collapse;';
    echo '  border-spacing: 0;';
    echo '  width: 100%;';
    echo '  border: 1px solid #ddd;';
    echo '}';

    echo 'th, td {';
    echo '  text-align: left;';
    echo '  padding: 8px;';
    echo '}';

    //echo 'tr:nth-child(even){background-color: #f2f2f2}';
    //echo '  background-color: lightblue;';
    echo 'tr:nth-child(even){background-color: lightblue}';
    //echo '  table, th, td {';
    //echo '  border: 1px solid black;';
    //echo '}';
    echo '.btn {';
    echo '  background-color: DodgerBlue;';
    echo '  border: none;';
	echo '  color: white;';
	echo '  padding: 12px 30px;';
	echo '  cursor: pointer;';
	echo '  font-size: 20px;';
	echo '}';

    /* Darker background on mouse-over */
    echo '.btn:hover {';
    echo '  background-color: RoyalBlue;';
    echo '}';
    echo '</style>';
    //echo '<hr>';
    echo '<p><h3>Table contains '.count($list).' records, '.$count_array['active'].' active, '.$count_array['unverified'].' unverified, '.$count_array['unsubscribed'].' unsubscribed </h3></p>';

    echo '<div style="display:inline-block;">';
	echo '<div class="container" style="float: left;">';
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
	echo '<div style="float: right;">';

    //echo '<form action="" method="post">';
    $myaction=admin_url( 'admin-post.php' );
    echo '<form action="'.$myaction.'" method="post">';
    echo '  <input type="hidden" name="data1" value="'.$filterrecords.'">';
    echo '  <input type="hidden" name="data2" value="foobarid2">';
    echo '  <input type="hidden" name="action" value="generate_csv" />';
    echo '  <input type="submit" name="submit" class="button button-primary" value="Generate & Download CSV File" />';
    //echo '  <button type="submit" class="btn" name="downloadtable" value="Download1" /><i class="fa fa-download"></i> Download</button>';
    echo '</form>';
	echo '</div>';
	echo '</div>';

    echo '<br>';

    echo '<div class="ex3" style="overflow-x:auto;">';
    echo '<table style="width:100%">';
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
    echo '</div>';
    echo '</table>';
}



function wga_admin_campaign() {
    // Donate menu
    if(!current_user_can('manage_options')) {
	    die('Access Denied');
    }
    echo '<h1> Campaign page </h1>';
}

?>