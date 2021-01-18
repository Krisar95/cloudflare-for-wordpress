<?php

/**
 * Plugin Name
 *
 * @package           PluginPackage
 * @author            Kristófer Ísar Ingimundarson
 * @copyright         2021 Kristófer Ísar Ingimundarson for KryoCloud
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Cloudflare for Wordpress
 * Plugin URI:        https://kryocloud.no/plugins/
 * Description:       This plugin enables you, the user, to integrate Cloudflare's advanced caching into Wordpress.
 * Version:           0.0.0 (alpha build)
 * Requires at least: 5.2
 * Requires PHP:      7 or above
 * Author:            Kristófer Ísar Ingimundarson for KryoCloud
 * Author URI:        https://kryocloud.no/
 * Text Domain:       cffwp
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

include( dirname(__FILE__) ."/scripts/functions.php" );

add_action('admin_menu', 'cffwp_setup_menu');

// Purge cache on page/post save - deprecated until further notice
/*
    add_action( 'save_post', 'my_save_post_function', 10, 3 );

    function my_save_post_function( $post_ID, $post, $update ) {
        $apikey		= "yA4r1Td1KZqBehcLqJmQt7-zR_WXP5ZEIL0E2xtb"; // Cloudflare Bearer Token
        $zoneid 	= "cee03bb1ee026d129a449b36967ca33d"; // Cloudflare Domain Zone ID

        // Sample query to create an A record in the zone's DNS.
        $ch = curl_init("https://api.cloudflare.com/client/v4/zones/".$zoneid."/purge_cache");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer '.$apikey.'',
        'Cache-Control: no-cache',
        'Content-Type:application/json'
        ));

        // curl params.
        $data = array(

            "purge_everything" => "true"
            
        );

        // Encode the string in JSON so cloudflare doesn't complain about it later
        $data_string = json_encode($data);

        // Make sure we POST the request
        curl_setopt($ch, CURLOPT_POST, true);

        // Send the query
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);	


        // Output to $sonuc variable (name arbitrary)
        $sonuc = curl_exec($ch);

        // JSON Decode the response
        $decode = json_decode($sonuc);
        $errors = $decode->errors;

        curl_close($ch);
    }
*/

// Add menu page in wordpress backend
function cffwp_setup_menu(){
    add_menu_page( 
        'Cloudflare for Wordpress', 
        'Cloudflare', 
        'manage_options', 
        'cffwp', 
        'cffwp_init',
        'dashicons-cloud-saved',
        2
    );
}

// Plugin init
function cffwp_init() {

    // If bearer key is present in user meta, skip authorisation and fire immediately
    $uid = get_current_user_id();
    if(get_user_meta($uid, "_userBearerKey", true) && get_user_meta($uid, "_userZoneID", true)) : ?>
        
        <h1 class="cloudflareTitle">Cloudflare For Wordpress</h1>
    
    <?php
    
        $bearerKey = get_user_meta($uid, "_userBearerKey", true);
        $zoneid = get_user_meta($uid, "_userZoneID", true);
        cffwp_verify($bearerKey, $zoneid);
    
        // if not; go through authorization process
        else:
    ?>

    <h1 class="cloudflareTitle">Cloudflare For Wordpress</h1>

    <form class='authForm' action='' method='POST'>
    
        <input class='bearer' type='password' name='bearer' placeholder='Bearer key' />
        <input class='zoneid' type="text"     name="zoneid" placeholder='Zone ID' />
        <button type='submit'>Submit</button>
    
    </form>
    <?php endif; ?>
    <div class='result'></div>
<?php } ?>

<?php 
// Add record for post - deprecated until further notice
/*
    // Check if user wanted to add a new CNAME for the post

    function cname_box_html($post) { ?>
    <script>

    (function($){

        $("#post_add_cname").on("focusin", function(){

            $("#cnametooltip").show();

            $(this).find("input[type='text']").on("input", function(){
                var type = $(this).data("type")
                if($(this).val().length < 1) {
                    var text = "["+type+"]";
                } else {
                    var text = $(this).val()
                }
                $("#cnametooltip span."+type).text(text)
            })

        })

        $("#post_add_cname").on("focusout", function(){

            $("#cnametooltip").hide();

        })

    })(jQuery);

    </script>
        <div id="cnametooltip" style="display:none;">
            <p class="cnamevalue"><span class="name">[name]</span>.kryocloud.no is an alias of <span class="target">[target]</span>.</p>
        </div>
        <div class="components-panel__row">
            <label class="components-checkbox-control__label" for="cnameTick">Would you like to add a CNAME record for this post?</label>
            <input class="components-checkbox-control__input" type="checkbox" name="cnameTick" id="cnameTick">
        </div>
        <div class="components-panel__row">
            <label class="components-base-control__label" for="cnameContent">Alias name</label>
            <input data-type="name" class="components-text-control__input" type="text" name="cnameName" id="cnameContent" placeholder="example">
        </div>
        <div class="components-panel__row">
            <label class="components-base-control__label" for="cnameTarget">Alias target</label>
            <input data-type="target" class="components-text-control__input" type="text" name="cnameTarget" id="cnameTarget" placeholder="">
        </div>
    <?php }

    function cffwp_add_cname_metabox() {
        $screens = ['post', 'page', 'events'];
        foreach ($screens as $screen) {
            add_meta_box(
                'post_add_cname',
                'Add CNAME record for post',
                'cname_box_html',
                $screen,
                'side',
                'high'
            );
        }
        $userid = get_current_user_id();
        $user = get_user_by( 'id', $userid ); 
        $order = get_user_option("meta-box-order_post", $user->ID);
        
        $current_order = array(); 
        $current_order = explode(",",$order['side']);
        
        for($i=0 ; $i <= count ($current_order) ; $i++){
        $temp = $current_order[$i];
        if ( $current_order[$i] == 'post_add_cname' && $i != 1) {
            $current_order[$i] = $current_order[1];         
            $current_order[1] = $temp;          
            }
        }
        
        $order['side'] = implode(",",$current_order);
        update_user_option($user->ID, "meta-box-order_page", $order, true);
        update_user_option($user->ID, "meta-box-order_post", $order, true);
    }
    add_action('add_meta_boxes', 'cffwp_add_cname_metabox');

    function cffwp_save_postdata( $post_id ) {
        if ( array_key_exists( 'post_add_cname', $_POST ) ) {
            update_post_meta(
                $post_id,
                '_cffwp_cname_record',
                array($_POST['cnameContent'], $_POST['cnameTarget'])
            );
        }
    }
    add_action( 'save_post', 'cffwp_save_postdata' );
*/
?>

<?php

// Enqueue proper JS libraries
function cffwp_enqueue_script() {
    wp_enqueue_script('colorbox-min', plugin_dir_url( __FILE__ ) . 'scripts/js/colorbox-min.js', array('jquery'), 1.1, true);
    wp_enqueue_script('cfapiajax', plugin_dir_url( __FILE__ ) . 'scripts/js/cfapiajax.js', array('jquery'), 1.1, true);
    wP_enqueue_script('jquery', true);
}
add_action('admin_enqueue_scripts', 'cffwp_enqueue_script');

function utm_user_scripts() {
    $plugin_url = plugin_dir_url( __FILE__ );
    wp_enqueue_style( 'style',  $plugin_url . "/css/style.css");
}

add_action( 'admin_print_styles', 'utm_user_scripts' );

?>
