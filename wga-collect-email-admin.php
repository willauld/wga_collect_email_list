<?php


function wga_admin_options(){
    // Options menu
    if(!current_user_can('manage_options')) {
	    die('Access Denied');
    }
	echo '<div class="wrap">';
	echo '	<h2>Welcome To My Options page</h2>';
	echo '</div>';
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

/**
* The full correct version without any issues.
* It works in MSExcel(2013 and newer), WPS office, Google Sheet online and Openoffice.
* Last test on 2 July 2019
*/
function csv_download_filtered_table($filterrecords, $list) {
   
    $header = 'AppleRinquest Shop,Thailand';   // the data will show in 2 cells
    $content = '"' . 'Date:July2,2019' . '"';  // the data will show in 1 cell. we use double quote around the text in order to print out the comma without split the cell.
    $content .= 'Product title:,Ring001';
    $content .= 'Price per piece:,' . '"' . '150,000 bath' . '"';  // the data will show in 2 cells.
    $footer = html_entity_decode( 'Copyright  © AppleRinquest Shop limited.' , ENT_QUOTES, 'UTF-8');  // add html_entity_decode() to decode the HTML entity from the text if any.
   
    // # add MIME types at the header
    header('Content-Type: text/csv; charset=UTF-8;');  // tell the browser that this is the CSV file and encode UTF8.
    header('Content-Disposition: attachment; filename="'. "wga-email-list-" . time() .'.csv"');    // tell the browser to let the viewers can download the file with the default filename as provided.
    
    // # to protect the MSExcel(2013 and older version) replaces the accent marks to the question mark(?)
    // I add these 3 byte UTF8 here before print out the first line to CSV file.
    echo chr(0xEF);
    echo chr(0xBB);
    echo chr(0xBF);
    
    // # print out your data
    echo $header;
    echo "\n";  // add new line
    echo "\n";  // add new line

    echo 'ID,First Name,Last Name,Email,Source,Unsubscribed,Created_at,Updated_at,Is Verified?,Hash\n';

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
            // Add quote around each field in case they have inbedded commas
	        echo $record["id"].",";
	        echo $record["first_name"].",";
	        echo $record["last_name"].",";
	        echo $record["email"].",";
	        echo $record["source"].",";
	        echo $record["unsubscribed"].",";
	        echo $record["created_at"].",";
	        echo $record["updated_at"].",";
	        echo $record["is_verified"].",";
	        echo $record["vhash"]."\n";
        }
    }

    echo "\n";  // add new line
    echo "\n";  // add new line    
    //echo $footer;
    exit; // add the exit or die() after the last print out content to the CSV file.
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
            csv_download_filtered_table($filterrecords, $list);
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
    echo '<form action="" method="post">';
    echo '  <button type="submit" class="btn" name="downloadtable" value="Download1" /><i class="fa fa-download"></i> Download</button>';
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