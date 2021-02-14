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

function wga_management() {
    // Manage menu
    if(!current_user_can('manage_options')) {
	    die('Access Denied');
    }
    echo '<h2> WGA Collect Email List Manage Page </h2>';
}

function wga_donate() {
    // Donate menu
    if(!current_user_can('manage_options')) {
	    die('Access Denied');
    }
    echo '<h1> Donate page </h1>';
}

?>