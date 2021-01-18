<?php
$rootpath = "/home/apitest.kryocloud.no/public_html/";
require_once($rootpath."wp-load.php");


function cffwp_check_zone($bearer, $userid, $zoneid) {
    $ch = curl_init();

    // set URL and other appropriate options
    curl_setopt($ch, CURLOPT_URL, "https://api.cloudflare.com/client/v4/zones/".$zoneid."/activation_check");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");                                                                     
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer '.$bearer.'',
        'Content-Type:application/json'
    ));
    curl_setopt($ch, CURLOPT_POST , 1);

    // grab URL and pass it to the browser
    $response = curl_exec($ch);

    // close cURL resource, and free up system resources
    curl_close($ch);

    $decode = json_decode($response);

    $success = $decode->success;

    if($success) {
        echo "This zone is active \n";
        echo get_user_meta($userid, "_userBearerKey");
    } else {
        echo "Something went wrong. Error(s): \n";
        foreach ($decode->errors as $e) {
            echo "Code: ".$e->code."\n";
            echo "Code: ".$e->message."\n";
        }
    }


}


function cffwp_list($bearer, $zoneid) {
    $theid = get_current_user_id();

    // Sample query to create an A record in the zone's DNS.
    $listDNS = curl_init("https://api.cloudflare.com/client/v4/zones/".$zoneid."/dns_records");
    curl_setopt($listDNS, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($listDNS, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($listDNS, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($listDNS, CURLOPT_CUSTOMREQUEST, "GET");                                                                     
    curl_setopt($listDNS, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer '.$bearer.'',
    'Cache-Control: no-cache',
    'Content-Type:application/json'
    ));

    $sonuc = curl_exec($listDNS);

    $jdlist = json_decode($sonuc);

    curl_close($listDNS);

    // HTML for output below this line
    include_once(dirname(__FILE__)."/templates/recordlist.php");
    
}

// Verify bearer key
function cffwp_verify($bearer, $zoneid) {
    
    $theid = get_current_user_id();

    if(!get_user_meta($theid, "_userBearerKey", true)):
    
        add_user_meta( $theid, "_userBearerKey", $bearer);
        add_user_meta( $theid, "_userZoneID", $zoneid);
    
    endif;

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://api.cloudflare.com/client/v4/user/tokens/verify");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");                                                                     
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer '.$bearer.'',
        'Content-Type:application/json'
    ));
    curl_setopt($ch, CURLOPT_POST , 1);

    $response = curl_exec($ch);

    curl_close($ch);

    $decode = json_decode($response);

    $success = $decode->success;

    if ($success) { 

        //delete_user_meta( $theid, "_userBearerKey");
        //delete_user_meta( $theid, "_userZoneID");
        cffwp_list($bearer, $zoneid);

    }


    if (!$success) {
        echo "There was an error authenticating with Cloudflare. Make sure you have the correct token and try again.";
        print_r($decode);
    }
}

// Edit existing CF record
function cffwp_update_record($bearer, $args) {
    // Retreive values from $args array and declaring proper vars
    $zoneid     = $args['zoneid'];
    $recordid   = $args['recordid'];
    $type       = $args['type'];
    $content    = $args['content'];
    $name       = $args['name'];
    $proxied    = $args['proxied'];
    $ttl        = $args['ttl'];

    // Query appropriate record with ID provided
    $ch = curl_init("https://api.cloudflare.com/client/v4/zones/".$zoneid."/dns_records/".$recordid);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POST, true);                                                                
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer '.$bearer.'',
    'Cache-Control: no-cache',
    'Content-Type:application/json'
    ));

    // New record values
    $data = array(
        "type" => $type,
        "name" => $name,
        "content" => $content,
        "ttl" => $ttl
    );

    // Encode the string in JSON so cloudflare doesn't complain about it later
    $data_string = json_encode($data);

    // Send the query
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);	

    // Output to $postRecord for use later 
    $putRecord = curl_exec($ch);

    // Decode the response to an usable PHP array
    $decode = json_decode($putRecord);
    $errors = $decode->errors;

    curl_close($ch);


    //print_r($decode);
    if ($decode->success) {
        echo "Success! Record with ID: ".$decode->result->id." was successfully edited. Reloading results...";
    } else {
        echo "Record was not updated. Error code: (".$errors[0]->code.") \n Error Message: ".$errors[0]->message ;
    }
}

// Create new CF record
function cffwp_add_record($bearer, $args) {

    $zoneid     = $args["zoneid"];
    $type       = $args["type"];
    $name       = $args["name"];
    $content    = $args["content"];
    $ttl        = $args["ttl"];
    $priority   = $args["priority"];
    $proxied    = $args["proxied"];

    $ch = curl_init("https://api.cloudflare.com/client/v4/zones/".$zoneid."/dns_records/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POST, true);                                                                
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer '.$bearer.'',
    'Cache-Control: no-cache',
    'Content-Type:application/json'
    ));

    // New record values
    $data = array(
        "type"      => $type,
        "name"      => $name,
        "content"   => $content,
        "ttl"       => $ttl,
        "priority"  => $priority,
        "proxied"   => $proxied
    );

    // Encode the string in JSON so cloudflare doesn't complain about it later
    $data_string = json_encode($data);

    // Send the query
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);	

    // Output to $postRecord for use later 
    $putRecord = curl_exec($ch);

    // Decode the response to an usable PHP array
    $decode = json_decode($putRecord);
    $errors = $decode->errors;

    curl_close($ch);

    var_dump($decode);

}

// Delete existing CF record
function cffwp_del_record($bearer, $args) {
    $zoneid         = $args["zoneid"];
    $recordid       = $args["recordid"];

    $ch = curl_init("https://api.cloudflare.com/client/v4/zones/".$zoneid."/dns_records/".$recordid);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_POST, true);                                                                
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer '.$bearer.'',
    'Cache-Control: no-cache',
    'Content-Type:application/json'
    ));

    // Output to $postRecord for use later 
    $deleteRecord = curl_exec($ch);

    // Decode the response to an usable PHP array
    $decode = json_decode($deleteRecord);
    $errors = $decode->errors;

    curl_close($ch);

    print_r($decode);
}


?>