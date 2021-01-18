<?php
    // Load WP functions
    $rootpath = "/home/apitest.kryocloud.no/public_html/";
    require_once($rootpath."wp-load.php");

    // include functions.php for this dir
    require_once( dirname(__FILE__) ."/functions.php" );
    
    if (get_user_meta($userid, "_userBearerKey", true)) {
        $userid = get_current_user_id();
        $apiKey = get_user_meta($userid, "_userBearerKey", true);
    } else {
        $zoneID = $_POST['zoneid'];
        $apiKey = $_POST['bearer'];
    }

    // Get current user ID
    //$userid = get_current_user_id();
    
    // Call to cffwp_get_records function in functions.php for this dir with bearer and user id as passed args
    cffwp_verify($apiKey, $zoneID);

?>