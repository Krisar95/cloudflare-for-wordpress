<?php
    
    // Load WP functions
    $rootpath = "/home/apitest.kryocloud.no/public_html/";
    require_once($rootpath."wp-load.php");

    // include functions.php for this dir
    require_once( dirname(__FILE__) ."/functions.php" );

    $zid    = $_POST['zid'];
    $userid = get_current_user_id();
    $bearer = get_user_meta($userid, "_userBearerKey", true);

    cffwp_check_zone($zid, $bearer, $userid);

?>
