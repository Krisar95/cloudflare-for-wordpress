<?php/*
function cffwp_get_records($bearer, $userid) {

    // create a new cURL resource
    $ch = curl_init();

    // set URL and other appropriate options
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



    // grab URL and pass it to the browser
    $response = curl_exec($ch);

    // close cURL resource, and free up system resources
    curl_close($ch);

    $decode = json_decode($response);

    $success = $decode->success;

    if ($success) {

        $theid = $userid;

        //add_user_meta( $theid, "_userBearerKey", $bearer);

        echo "Cloudflare has been authenticated successfully.\n";
        $zoneid 	= "cee03bb1ee026d129a449b36967ca33d"; // Cloudflare Domain Zone ID

        // Sample query to create an A record in the zone's DNS.
        $listDNS = curl_init("https://api.cloudflare.com/client/v4/zones/".$zoneid."/dns_records?type=A");
        curl_setopt($listDNS, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($listDNS, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($listDNS, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($listDNS, CURLOPT_CUSTOMREQUEST, "GET");                                                                     
        curl_setopt($listDNS, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer '.$bearer.'',
        'Cache-Control: no-cache',
        'Content-Type:application/json'
        ));

        // Output to $sonuc variapble (name arbitrary)
        $sonuc = curl_exec($listDNS);
        // JSON Decode the response
        $jdlist = json_decode($sonuc);

        curl_close($listDNS);
        
        ?>
        <pre><?php //print_r( $jdlist );?></pre>

        <?php
        echo "
            <style>
                p.editButton { color: #0073aa; text-decoration: underline; }
                p.editButton:hover { cursor: pointer; }
                div.dnsItem { float: left; margin-right: 20px; } 
                div.record-list { float: left; }
                h3.zoneName { margin-bottom: 0; padding-bottom: 0; }
                th { text-align: left; padding-right: 20px; }
                tr { border-bottom: 1px solid black; }
                td { padding: 10px 0px; }
                tr.dnsrow:nth-child(even) { background: white; }
                table.record-list { border-collapse: collapse; }
            </style>
        ";
        ?> 
        
        <h3 class="zoneName">Records for <?php echo $jdlist->result[0]->zone_name; ?></h3>
        <p>ID: <?php echo $jdlist->result[0]->zone_id; ?></p>
        <table class="record-list">
            <thead>
                <th>Record name</th>
                <th>Record type</th>
                <th>TTL</th>
                <th>Record content</th>
                <th>Edit</th>
            </thead>
            <tbody>
                <?php $i = 1; ?>
                <?php foreach ($jdlist->result as $res) { ?>
                        <?php $resJson = json_encode($res); ?>
                        <tr class="dnsrow-<?php echo $i; ?>" data-array='<?php echo $resJson; ?>'>
                            <?php if (!$res->priority) {
                                $prio = "with no priority";
                            } else {
                                $prio = "with a priority value of ". $res->priority;
                            } ?>
                            <td><?php echo $res->name; ?></td>
                            <td><?php echo $res->type; ?></td>
                            <?php if(!$res->data) { ?>
                            <td><?php echo $res->ttl; ?></td>
                            <td><?php echo $res->content; ?></td>
                            <?php } if($res->data) { ?>
                            <td><?php echo $res->ttl; ?></td>
                            <td><?php echo $res->data->name; ?></td>
                            <td><?php echo $res->data->port; ?></td>
                            <td><?php echo $res->data->priority; ?></td>
                            <td><?php echo $res->data->proto; ?></td>
                            <td><?php echo $res->data->service; ?></td>
                            <td><?php echo $res->data->target; ?></td>
                            <td><?php echo $res->data->weight; ?></td>
                            <?php } ?>
                            <td><a class="editButton" data-id="<?php echo $i; ?>" href="#">Edit record</a></td>
                        </tr>
                        <?php $i++; ?>
                <?php } ?>
            </tbody>
        </table>
        <div class="zoneEdit">
        <br />
        <form class="recordEditForm" action="">
        <label for="recordName">Record Name</label><br /><br />
        <input class="rName" type="text" name="recordName"><br /><br />
        <label for="recordContent">Record Content</label><br /><br />
        <input class="rCont" type="text" name="recordContent">
        <button class="submitEdit" type="submit">Save changes</button>
        </form>
        <div class="editResult"></div>
        </div>
        <script>
            (function($) {

                function getApiResults(apiKey) {
                    let data = apiKey
                    $.ajax({
                        method: "POST",
                        url: "/wp-content/plugins/cloudflare-for-wordpress/scripts/cfapiauth.php",
                        data: { 
                            bearer: data
                        },
                        success:function( msg ) {
                            $(".result").html(msg);
                        }
                    })
                }

                $(".editButton").on("click", function(e){
                    
                    e.preventDefault();
                    var rowID = $(this).data("id");
                    var array = $(".dnsrow-" + rowID).data("array");
                    console.log(array)
                    var rName = $(".rName")
                    var rCont = $(".rCont")
                    rName.val(array.name)
                    rCont.val(array.content)

                    $(".recordEditForm").on("submit", function(e){
                        e.preventDefault();
                        $.ajax({
                            method: "POST",
                            url: "/wp-content/plugins/cloudflare-for-wordpress/hooks/cfUpdateZones.php",
                            data: { 
                                recordid: array.id,
                                type    : array.type,
                                content : rCont.val(),
                                name    : rName.val(),
                                proxied : array.proxied,
                                ttl     : array.ttl
                            },
                            success:function( msg ) {
                                $(".editResult").html(msg);

                                setTimeout(() => {
                                    getApiResults($(".bearer").val())
                                }, 500);
                                
                            }
                        })
                    });

                });

            })(jQuery);
        </script>
    <?php } ?>


    <?php if (!$success) {
        echo "There was an error authenticating with Cloudflare. Make sure you have the correct token and try again.";
    }
}

function cffwp_update_record($bearer, $recordid, $zoneid) {

    // Sample query to create an A record in the zone's DNS.
    $ch = curl_init("https://api.cloudflare.com/client/v4/zones/".$zoneid."/dns_records/".$recordid);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");                                                                     
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer '.$bearer.'',
    'Cache-Control: no-cache',
    'Content-Type:application/json'
    ));

    // curl params.

    $data = array(
        "type" => $_POST['type'],
        "name" => $_POST['name'],
        "content" => $_POST['content'],
        "ttl" => $_POST['ttl']
    );

    // Encode the string in JSON so cloudflare doesn't complain about it later
    $data_string = json_encode($data);

    // Make sure we POST the request
    curl_setopt($ch, CURLOPT_POST, true);

    // Send the query
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);	


    // Output to $sonuc variable (name arbitrary)
    $putRecord = curl_exec($ch);

    // JSON Decode the response
    $decode = json_decode($putRecord);
    $errors = $decode->errors;

    curl_close($ch);


    //print_r($decode);
    if ($decode->success) {
        echo "Success! Record with ID: ".$decode->result->id." was successfully edited. Reloading results...";
    } else {
        echo "Failure! Error message: ".$decode->errors;
    }
}

*/
?>