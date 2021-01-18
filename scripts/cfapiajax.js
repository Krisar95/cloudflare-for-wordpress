$(".authForm").on("submit", function(){
    $.ajax({
        method: "POST",
        url: "/wp-content/plugins/cloudflare-for-wordpress/scripts/cfapiauth.php",
        data: { bearer: $(".bearer").val }
    })

    .done(function( msg ) {
        $(".result").innerHTML += msg
    })
});

