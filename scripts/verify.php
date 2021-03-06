<?php
    //
    // Make this page compatable with the rest of wordpress
    //
    require_once(dirname(__FILE__) . '/wp-blog-header.php');
    header("HTTP/1.1 200 OK");
    header("Status: 200 All rosy");

    // Your WordPress functions here...
    //echo site_url();
?>
<html>
<head>
    <title>Oregon Open Primaries Email Verification</title>
    <link href="css/style.css" type="text/css" rel="stylesheet" />
	<?php
	function wga1_test_input($data) {
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	}
    //require_once(dirname(__FILE__) . '/wp-content\plugins\wga-collect-email-list/wga-collect-email-admin.php');
    require_once('./wp-content/plugins/wga-collect-email-list/wga-collect-email-admin.php');
	?>
</head>
<body>
<style>
	#wrap .statusmsg{
		font-size: 20px; /* Set message font size  */
		padding: 5px; /* Some padding to make some more space for our text  */
		background: #EDEDED; /* Add background color to our status message   */
		border: 1px solid #DFDFDF; /* Add border arround our status message   */
        color: red;
	}	
</style>
    <!-- start header div --> 
    <div id="header">
        <img hieght="300" src=<?php echo '"'.site_url().'/wp-content/uploads/2020/12/LogoOregonOpenPrimaries.png"' ?> alt="Let ALL voters vote!"/><br><br>
        <h2>Oregon Open Primaries Email Verification</h2>
    </div>
    <!-- end header div -->   
      
    <!-- start wrap div -->   
    <div id="wrap">
        <!-- start PHP code -->
        <?php
			global $wpdb;
          
			if(isset($_GET['email']) && !empty($_GET['email']) AND isset($_GET['vhash']) && !empty($_GET['vhash']) AND isset($_GET['subscribe']) && !empty($_GET['subscribe'])){
				// Verify data
				$subscribe = wga1_test_input($_GET['subscribe']); // Set email variable
				$email = wga1_test_input($_GET['email']); // Set email variable
				$hash = wga1_test_input($_GET['vhash']); // Set hash variable
							  
				if ($subscribe == 'join') {
					$query   = $wpdb->prepare( 
						//"SELECT email, is_verified, vhash FROM {$wpdb->prefix}wga_contact_list WHERE email ='".$email."' AND vhash='".$hash."' AND is_verified='0'"
						"SELECT id, email, is_verified, vhash FROM {$wpdb->prefix}wga_contact_list WHERE email =%s AND vhash=%s AND is_verified=0", $email, $hash 
					);
					$results = $wpdb->get_results( $query );
					$match = count($results);

					if($match > 0){
						// We have a match, record verification
						$id = $wpdb->get_var(NULL,0,0);
						$updated_at = current_time( 'mysql' );
						if ($wpdb->update(
							"{$wpdb->prefix}wga_contact_list",
							array( 
								'is_verified' => '1',
								'updated_at' => $updated_at,
							),
							array( 
								'id' => "$id",
								'email' => "$email",
								'vhash' => "$hash",
								'is_verified' => '0',
							)
						) == 1) {
							echo '<div class="statusmsg">Your email has been verified, Thank you.</div>';

							do_action('wga_initial_welcome_email_hook', $id);
							//wga_send_initial_email($id);
						} else {
							echo '<div class="statusmsg">Your email has been verified, but there was a problem updating your record. Please notify the admin. Thank you.</div>';
						}
					}else{
						// No match -> invalid url or email has already been verified
						echo '<div class="statusmsg">The url is either invalid or you already verified your email.</div>';
					}
				}
				elseif ($subscribe == 'unsubscribe') {
					//wp_die("not implemented yet");

					$query   = $wpdb->prepare( 
						//"SELECT email, is_verified, vhash FROM {$wpdb->prefix}wga_contact_list WHERE email ='".$email."' AND vhash='".$hash."' AND is_verified='0'"
						"SELECT id, email, is_verified, vhash FROM {$wpdb->prefix}wga_contact_list WHERE email =%s AND vhash=%s AND is_verified=1", $email, $hash 
					);
					$results = $wpdb->get_results( $query );
					$match = count($results);

					if($match > 0){
						// We have a match, unsubscribe if user confirms
						//
						// Add confirmation form here
						//
						$id = $wpdb->get_var(NULL,0,0);
						$updated_at = current_time( 'mysql' );
						if ($wpdb->update(
							"{$wpdb->prefix}wga_contact_list",
							array( 
								'unsubscribed' => '1',
								'updated_at' => $updated_at,
							),
							array( 
								'id' => "$id",
								'email' => "$email",
								'vhash' => "$hash",
								'is_verified' => '1',
							)
						) 
						== 1) 
						{
							echo '<div class="statusmsg">Your email has been unsubscribed, Thank you.</div>';
						} else {
							echo '<div class="statusmsg">Your email could not be unsubscribed, there was a problem updating your record. Please notify the admin. Thank you.</div>';

						}
					}
				}
				else {
					// Invalid approah
					echo '<div class="statusmsg">Invalid approach, please use the link that has been send to your email.</div>';
				}
							  
			}else{
				// Invalid approah
				echo '<div class="statusmsg">Invalid approach, please use the link that has been send to your email.</div>';
			}	
                  
        ?>
        <!-- stop PHP Code -->
  
          
    </div>
    <!-- end wrap div --> 
</body>
</html>