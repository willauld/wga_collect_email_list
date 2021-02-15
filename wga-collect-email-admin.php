<?php


function wga_admin_init(){
    // Options menu
    if(!current_user_can('manage_options')) {
	    die('Access Denied');
    }
	echo '<div class="wrap">';
	echo '	<h2>Welcome To My Options page</h2>';
	echo '</div>';
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

function wga_management() {
    // Manage menu
    $filterrecords = "all";

    if(!current_user_can('manage_options')) {
	    die('Access Denied');
    }
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
	    if (!empty($_POST["filterrecords"])) {
            $filterrecords = sanitize_text_field($_POST["filterrecords"]);
        }
    }

    $list = get_email_list(); 
    $count_array = get_email_counts($list);
   
    echo '<h2> WGA Collect Email List Manage Page </h2>';
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
    echo '</style>';
    //echo '<hr>';
    echo '<p><h3>Table contains '.count($list).' records, '.$count_array['active'].' active, '.$count_array['unverified'].' unverified, '.$count_array['unsubscribed'].' unsubscribed </h3></p>';

	echo '<div class="container">';
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
	echo '</div><br>';


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



function wga_donate() {
    // Donate menu
    if(!current_user_can('manage_options')) {
	    die('Access Denied');
    }
    echo '<h1> Donate page </h1>';
}

?>