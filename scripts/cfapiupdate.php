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
    $arr = array(

        "zoneid"    => $_POST['zoneid'],
        "recordid"  => $_POST['recordid'],
        "type"      => $_POST['type'],
        "content"   => $_POST['content'],
        "name"      => $_POST['name'],
        "proxied"   => $_POST['proxied'],
        "ttl"       => $_POST['ttl']

    );

    // Finally, call the function and update the record being queried with these params
    cffwp_update_record($apiKey, $arr);

?>