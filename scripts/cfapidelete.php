<?php 

    // Load WP functions
    $rootpath = "/home/apitest.kryocloud.no/public_html/";
    require_once($rootpath."wp-load.php");

    // include functions.php for this dir
    require_once( dirname(__FILE__) ."/functions.php" );
    
    /**
     * Grab value from the user's meta fields
     * Post that value into the update record function along with an array
     * that's populated with relevant information
     */

    $userid = get_current_user_id();
    $apiKey = get_user_meta($userid, "_userBearerKey", true);
    $args = array (
        "zoneid"    => $_POST["zoneid"],
        "recordid"  => $_POST["recordid"]
    );

    cffwp_del_record($apiKey, $args);

?>