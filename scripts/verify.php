<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
  
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

<html xmlns="http://www.w3.org/1999/xhtml">
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
	?>
</head>
<body>
    <!-- start header div --> 
    <div id="header">
        <h3>Oregon Open Primaries Email Verification</h3>
    </div>
    <!-- end header div -->   
      
    <!-- start wrap div -->   
    <div id="wrap">
        <!-- start PHP code -->
        <?php
			global $wpdb;
          
			if(isset($_GET['email']) && !empty($_GET['email']) AND isset($_GET['vhash']) && !empty($_GET['vhash'])){
				// Verify data
				$email = wga1_test_input($_GET['email']); // Set email variable
				$hash = wga1_test_input($_GET['vhash']); // Set hash variable
							  
							  
				$query   = $wpdb->prepare( 
					"SELECT email, is_verified, vhash FROM {$wpdb->prefix}wga_contact_list WHERE email ='".$email."' AND vhash='".$hash."' AND is_verified='0'"
				);
				$results = $wpdb->get_results( $query );
				$match = count($results);

				if($match > 0){
					// We have a match, record verification
					$query   = $wpdb->prepare(
						"UPDATE {$wpdb->prefix}wga_contact_list SET is_verified='1' WHERE email='".$email."' AND vhash='".$hash."' AND is_verified='0'"
						);
					$results = $wpdb->get_results( $query );
					echo '<div class="statusmsg">Your email has been verified, Thank you.</div>';
				}else{
					// No match -> invalid url or email has already been verified
					echo '<div class="statusmsg">The url is either invalid or you already verified your email.</div>';
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