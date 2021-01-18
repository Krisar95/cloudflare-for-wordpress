<div class="recordsHeader">

    <h3 class="zoneName">Records for <?php echo $jdlist->result[0]->zone_name; ?></h3>
    <span class="dashicons dashicons-arrow-down-alt2 showRecords"></span>

</div>

<div class="resultsContainer">

<p>ID: <?php echo $jdlist->result[0]->zone_id; ?></p>

<div class="recordsFilter">
    <script>
        (function($) {
        var item = document.getElementsByClassName("dnsItem");
        count = 0;
        for (let i = 0; i < item.length; i++) {
            var array = $(item[i]).data("array");
            if (array.type === $(".recfilter").data("type")) {
               count++
            }     
        }
        $(".count").text(count)
        })(jQuery);
    </script>
    <a data-type="A" href="#" class="recfilter a">A/AAAA <span class="count"></span></a>
    <a data-type="CNAME" href="#" class="recfilter cname">CNAME <span class="count"></span></a>
    <a data-type="TXT" href="#" class="recfilter txt">TXT <span class="count"></span></a>
    <a data-type="SRV" href="#" class="recfilter srv">SRV <span class="count"></span></a>
    <a data-type="MX" href="#" class="recfilter mx">MX <span class="count"></span></a>
    <a data-type="PTR" href="#" class="recfilter ptr">PTR <span class="count"></span></a>
</div>

    <div class="add-new-form container">
    
        <div class="addNewHeader">
            
            <h3 class="addNew">Add new record</h3>

            <span class="dashicons dashicons-plus-alt2 addnewToggle"></span>
        
        </div>

        <div  id="addnewtoggle" class="addNewModal modal">

            <div class="modal-content">

            <div class="modal-header">

                <h2>Add new record</h2>

                <span class="close">&times;</span>

            </div>

            <div class="modal-body">
                
                <form action="" method="post" class="addNewForm">

                    <div class="first">
                    
                    <input type="hidden" name="zoneid" value="<?php echo $jdlist->result[0]->zone_id; ?>" class="recordZoneID">

                    <div class="cs">
                    <p>Record type</p>
                    <select name="type" id="recordType" class="recordType">

                        <option value="A">A/AAAA</option>

                        <option value="CNAME">CNAME</option>

                        <option value="MX">MX</option>

                        <option value="TXT">TXT</option>

                        <option value="NS">NS</option>

                        <option value="SOA">SOA</option>

                        <option value="SRV">SRV</option>

                        <option value="PTR">PTR</option>

                    </select>

                    </div>

                    <div class="proxiedCheckbox">

                    <p class="proxiedLabel">Proxy through cloudflare</p>

                    <input type="checkbox" name="proxied" id="recordProxied" value="Proxy through Cloudflare" class="recordProxied">  

                    </div>

                    </div>

                    <div class="fieldset grid-a">
                    
                    <div class="newfield name">
                    <p class="head">Name</p> 
                    <input type="text" name="name" id="recordName" class="recordName">
                    </div>

                    <div class="newfield content">
                    <p class="head">Content</p>
                    <input type="text" name="content" id="recordContent" class="recordContent">
                    </div>

                    <div class="newfield ttl">
                    <p class="head">TTL</p>
                    <input type="text" name="ttl" id="recordTTL" class="recordTTL">
                    </div>

                    <div class="dataFields">
                    
                        <div class="newfield prio">
                        <p class="head">Priority</p>
                        <input type="text" name="priority" id="recordPrio" class="recordPrio">
                        </div>

                        <div class="newfield nameval">
                        <p class="head">Name value</p>
                        <textarea name="nameVal" id="recordnameVal" class="recordnameVal" form="addNewForm" placeholder="Name value"></textarea>
                        </div>

                        <div class="newfield port">
                        <p class="head">Port</p>
                        <input type="text" name="port" id="recordport" class="recordport">
                        </div>

                        <div class="newfield proto">
                        <p class="head">Prototype</p>
                        <input type="text" name="proto" id="recordproto" class="recordproto">
                        </div>

                        <div class="newfield service">
                        <p class="head">Service</p>
                        <input type="text" name="serv" id="recordserv" class="recordserv">
                        </div>

                        <div class="newfield target">
                        <p class="head">Target</p>
                        <textarea name="target" id="recordtarget" class="recordtarget" form="addNewForm" placeholder="Target"></textarea>
                        </div>

                        <div class="newfield weight">
                        <p class="head">Weight</p>
                        <textarea  name="weight" id="recordweight" class="recordweight" form="addNewForm" placeholder="Weight"></textarea>
                        </div>

                    </div>
                
                    <div class="saveRecord">

                        <button type="submit" value="" class="addNewSubmit dashicons dashicons-cloud-upload"></button>

                    </div>
                
                </form>

                </div>

                </div>

                <div class="modal-footer">
                
                <div class="addNewResult"></div>

                </div>

            </div>
            
        </div>
    
    </div>

    <?php if(isset($jdlist)): ?>

    <?php $i = 1; ?>

    <?php foreach ($jdlist->result as $res) { ?>

        <?php $resJson = json_encode($res); ?>

        <div class="dnsItem dnsItem-<?php echo $i; ?>" data-id="<?php echo $i; ?>" data-array='<?php echo $resJson; ?>'>

                <div class="dnsHead">

                    <div class="type"><p><?php echo $res->type; ?></p></div>

                    <div class="name"><h3><?php echo $res->name; ?></h3></div>

                    <div class="dnsActions">
                        
                        <a href="#" data-id="<?php echo $i; ?>" class="showContent dashicons dashicons-arrow-down-alt2"></a>

                        <a class="editButton dashicons dashicons-edit" data-id="<?php echo $i; ?>" href="#"></a>

                        <a href="#" class="deleteButton dashicons dashicons-trash" data-id="<?php echo $i; ?>"></a>

                    </div>

                </div>

                <div class="dnsContent dnsContent-<?php echo $i; ?>">

                    <p><span>TTL</span><?php echo $res->ttl; ?></p>

                    <p><span>Record Content</span><?php echo $res->content; ?></p>

                    <?php if( $res->data->name ) { ?>
                        <p><span>Name</span><?php echo $res->data->name; ?></p>
                    <?php } ?>

                    <?php if( $res->data->port ) {?>
                        <p><span>Port</span><?php echo $res->data->port; ?></p>
                    <?php } ?>

                    <?php if($res->priority) { ?>

                        <p><span>Priority</span><?php echo $res->priority ?></p>

                    <?php } else { ?>

                        <?php if( $res->data->priority ) {?>
                            <p><span>Priority</span><?php echo $res->data->priority; ?></p>
                        <?php } ?>

                    <?php } ?>
                    
                    <?php if( $res->data->proto ) { ?>
                        <p><span>Protocol</span><?php echo $res->data->proto; ?></p>
                    <?php } ?>

                    <?php if( $res->data->service ) { ?>
                        <p><span>Service</span><?php echo $res->data->service; ?></p>
                    <?php } ?>

                    <?php if( $res->data->target ) { ?>
                        <p><span>Target</span><?php echo $res->data->target; ?></p>
                    <?php } ?>

                    <?php if( $res->data->weight ) { ?>
                        <p><span>Weight</span><?php echo $res->data->weight; ?></p>
                    <?php } ?>
                    

                </div>

        </div>

        <div id="editModal-<?php echo $i; ?>" class="modal editItem-<?php echo $i; ?>" data-array='<?php echo $resJson; ?>'>
            
            <div class="modal-content">
        
                <div class="modal-header">
                
                    <h2>Editing record: <?php echo $res->name; ?></h2>
                    <span class="close">&times;</span>
                
                </div>

                <div class="modal-body">

                    <form class="recordEditForm" action="">
                    
                    <div class="givenFields">

                        <div class="newfield editname">
                            <p class="head">New name</p>
                            <input class="rName" type="text" name="recordName" required>
                        </div>

                        <div class="newfield editcontent">
                            <p class="head">New content</p>
                            <input class="rCont" type="text" name="recordContent" required>
                        </div>
                    
                    </div>

                    <div id ="dataFields-<?php echo $i; ?>" class="printedFields-<?php echo $i; ?>"></div>

                    <?php $theid = get_current_user_id(); ?>

                    <div class="editRecord">
                        <button class="submitEdit dashicons dashicons-saved" type="submit"></button>
                    </div>

                    

                    </form>
                
                </div>
                

                <div class="modal-footer">
                    <span class="editResult"></span>
                </div>

            </div>

        </div>

        <?php $i++; ?>

    <?php } ?>

    <?php endif; ?>

</div>