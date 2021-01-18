(function($) {

    function getApiResults(apiKey, zoneid) {
        let data = apiKey
        let zone = zoneid

        $.ajax({
            method: "POST",
            url: "/wp-content/plugins/cloudflare-for-wordpress/scripts/cfapiauth.php",
            data: { 
                bearer  : data,
                zoneid  : zone
            },
            success:function() {
                setTimeout(() => {
                    window.location.reload();
                }, 500); 
            }
        })
    }

    $(".authForm").on("submit", function(e){
        e.preventDefault();
        var bearer = $(".bearer").val()
        var zoneid = $(".zoneid").val()
        getApiResults(bearer, zoneid)
    });

    $(".addnewzonetoggle").on("click", function(e){
        e.preventDefault;
        // Open the "add-new" modal window and render components
        var modal = document.getElementById("addnewzone");
        var span = document.getElementsByClassName("close")[0];
        
        modal.style.display = "block";

        // Close modal when "x" is pressed
        span.onclick = function() {
            modal.style.display = "none";
        }

        // Close modal if clicked outside of modal
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    })

    $(".newzoneform").on("submit", function(e){
        e.preventDefault();

        var zid = $("#zoneid").val();
        console.log(zid);

        var spinner = document.getElementById("spinner");
        var button = $(".nzsubmit button");
            button.contents().filter(function() {
                return this.nodeType === 3;
            }).remove();
        spinner.style.display = "inline-block";
        
        var data = {
            zid: zid
        }
        
        $.ajax({
            method: "POST",
            url: "/wp-content/plugins/cloudflare-for-wordpress/scripts/cfapicheckzone.php",
            data: data,
            
            success:function( msg ) {
                // Output result to editResult div
                console.log(msg);
                spinner.style.display = "none";
                button.addClass("dashicons dashicons-yes");
                console.log("hey it works lol");
                setTimeout(() => {
                    //location.reload();
                }, 500);              
            }
        })
        
    })

    $(".addNewForm").on("submit", function(e){
        // Stop button from reloading page
        e.preventDefault();
        // Ajax POST request to edit php function

        var zoneid  = $(".recordZoneID").val()
        var type    = $(".recordType").val()
        var content = $(".recordContent").val()
        var name    = $(".recordName").val()
        var proxied = $(".recordProxied").is(":checked")
        var prio    = $(".recordPrio").val()
        var ttl     = $(".recordTTL").val()
        var data    = { 

            zoneid      : zoneid,
            type        : type,
            content     : content,
            name        : name,
            proxied     : proxied,
            priority    : prio,
            ttl         : ttl
      
        }
        console.log(data);
        $.ajax({
            method: "POST",
            url: "/wp-content/plugins/cloudflare-for-wordpress/scripts/cfapiaddnew.php",
            data: data,
            
            success:function( msg ) {
                // Output result to editResult div
                $(".addNewResult").html(msg);

                setTimeout(() => {
                    location.reload()
                }, 1000);
                
            }
        })
    });

    $(".showContent").on("click", function(e) {
        e.preventDefault();

        $(this).toggleClass("active");

        var rowID = $(this).data("id");

        $(".dnsContent-" + rowID).toggleClass("show");
    });

    $(".editButton").on("click", function(e){
        
    // Stop link from scrolling to top, set variables to pass to edit function                   
        e.preventDefault();
        var rowID = $(this).data("id");
        var array = $(".editItem-" + rowID).data("array");
        //console.log(array)

        // If "Data" element in array Object contains anything
        if(array.data) {
            // Convert the object to a js array and specify parent div
            var editFields = Object.keys(array.data);
            var parent = document.getElementById("dataFields-"+rowID);

            // if object array isn't empty & the div doesn't already contain child elements
            if(editFields && !parent.childNodes.length) {
                for (let i = 0; i < editFields.length; i++) {
                    var divParent = $(".printedFields-" + rowID);

                    // Create a new div wrapper around the input fields for every "data" element
                    var newElement = document.createElement("div");
                    newElement.className += "newfield edit"+editFields[i];

                    // Create the heading for the input fields
                    var para = document.createElement("p");
                    para.className += 'head';

                    // Create the input fields
                    var input = document.createElement("input");
                    input.type="text";
                    input.name="record"+editFields[i];

                    // Inject content into divs, paras and divs
                    para.append("New data: "+editFields[i] )
                    divParent.append(newElement);
                    newElement.append(para);
                    newElement.append(input);
                }
            }
        }



        // Get the modal
        var modal = document.getElementById("editModal-" + rowID);
        var span = document.getElementsByClassName("close")[0];
        modal.style.display = "block";
        span.onclick = function() {
        modal.style.display = "none";
        }
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        $(".recordEditForm").on("submit", function(e){
            
            var els = this.elements;

            var rCont = els[1].value
            var rName = els[0].value

            
            // Stop button from reloading page
            e.preventDefault();
            console.log(this.elements);
            // Ajax POST request to edit php function
            $.ajax({
                method: "POST",
                url: "/wp-content/plugins/cloudflare-for-wordpress/scripts/cfapiupdate.php",
                data: { 
                    zoneid  : array.zone_id,
                    recordid: array.id,
                    type    : array.type,
                    content : rCont,
                    name    : rName,
                    proxied : array.proxied,
                    ttl     : array.ttl
                },
                success:function( msg ) {
                    // Output result to editResult div
                    $(".editResult").html(msg);
                    
                    // Reload results after .5s
                    setTimeout(() => {
                        //location.reload()
                    }, 500);
                    
                }
            })
        });

    });

    $(".deleteButton").on("click", function(e){
        e.preventDefault();
        
        var rowID = $(this).data("id");
        var array = $(".dnsItem-" + rowID).data("array");
        
        console.log(array);

        var retVal = confirm("Attention! \nYou are about to delete " + array.name + "\nID: " + array.id + " \nAre you SURE you want to continue?");

        if (retVal == true) {

        $.ajax({
            method: "POST",
            url: "/wp-content/plugins/cloudflare-for-wordpress/scripts/cfapidelete.php",
            data: {
                zoneid      : array.zone_id,
                recordid    : array.id
            },
            success:function( msg ) {
                // Output response and reload results
                $(".deleteResp").html(msg);

                setTimeout(() => {
                    location.reload()
                }, 500);
            }
        });

        } else {
            return;
        }
    });

    $(".addnewToggle").on("click", function(e){

        e.preventDefault;
        // Open the "add-new" modal window and render components
        var modal = document.getElementById("addnewtoggle");
        var span = document.getElementsByClassName("close")[0];
        
        modal.style.display = "block";

        // Close modal when "x" is pressed
        span.onclick = function() {
            modal.style.display = "none";
        }

        // Close modal if clicked outside of modal
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        $(".dataFields").hide()

        $(".recordType").on("change", function(e) {
            if($(".recordType").val() === "SRV" || $(".recordType").val() === "TXT" || $(".recordType").val() === "PTR") {
                $(".dataFields").show()
            } else {
                $(".dataFields").hide()
            }
        });
    });

    $(".showRecords").on("click", function(e) {
        $(this).toggleClass("active");
        $(".resultsContainer").toggleClass("show");
    });

    $(".recfilter").on("click", function(e) {
        $(this).toggleClass("active");

    });

})(jQuery);