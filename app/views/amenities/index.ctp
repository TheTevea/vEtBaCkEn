<?php
// Authentication
$this->element('check_access');
$allowAdd=checkAccess($user['User']['id'], 'question_feedbacks', 'add');

// Fixed Online Order Failed Insert Ticket Table
$sqlDetail = mysql_query("SELECT * FROM `t_ticket_details` WHERE `t_ticket_id` IS NULL AND id >= 4331905 ORDER BY `id` ASC");
while($rowDetail = mysql_fetch_array($sqlDetail)){
    $sqlTicketDTmp = mysql_query("SELECT * FROM `t_ticket_detail_api_tmps` WHERE `sys_code` = '".$rowDetail['sys_code']."' LIMIT 1");
    if(mysql_num_rows($sqlTicketDTmp)){
        $rowTicketDTmp = mysql_fetch_array($sqlTicketDTmp);
        $sqlTicketTmp = mysql_query("SELECT * FROM `t_ticket_api_tmps` WHERE id = ".$rowTicketDTmp['t_ticket_api_tmp_id']);
        if(mysql_num_rows($sqlTicketTmp)){
            $rowTicketTmp = mysql_fetch_array($sqlTicketTmp);
            $sqlOnline = mysql_query("SELECT * FROM `online_orders` WHERE `id` = ".$rowTicketTmp['online_order_id']." AND status = 4");
            if(mysql_num_rows($sqlOnline)){
                $rowOnline = mysql_fetch_array($sqlOnline);
                $sqlTicket = mysql_query("SELECT * FROM `t_tickets` WHERE `online_order_id` = ".$rowOnline['id']);
                if(mysql_num_rows($sqlTicket)){
                    $rowTicket = mysql_fetch_array($sqlTicket);
                    mysql_query("UPDATE t_ticket_details SET t_ticket_id = ".$rowTicket['id']." WHERE id = ".$rowDetail['id']);
                } else {
                    mysql_query("INSERT INTO t_tickets (`sys_code`, `offline_project_id`, `online_order_id`, `payment_method_id`, `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `discount_amount`, `total_vat`, `lucky_draw_fee`, `commission`, `commission_percent`, `balance`, `currency_center_id`, `note`, `total_seat`, `created`, `terminal_id`, `modified`, `price_type`, `type`, `status`, `agt_refer_code`, `is_vat`) SELECT `sys_code`, `offline_project_id`, `online_order_id`, ".$rowOnline['payment_method_id'].", `company_id`, `branch_id`, `date`, `t_agent_id`, `journey_date`, `journey_time`, `t_journey_id`, `t_journey_departure_id`, `t_destination_from_id`, `t_destination_to_id`, `t_boarding_point_id`, `t_drop_off_id`, `t_transportation_type_id`, `t_route_id`, `telephone`, `email`, `price`, `total_amount`, `discount_amount`, `total_vat`, `lucky_draw_fee`, `commission`, `commission_percent`, '0', `currency_center_id`, `note`, `total_seat`, `created`, `terminal_id`, `modified`, `price_type`, `type`, 2, 'Terminal', `is_vat` FROM t_ticket_api_tmps WHERE id = ".$rowTicketTmp['id'].";");
                    $ticketId = mysql_insert_id();
                    mysql_query("UPDATE t_ticket_details SET t_ticket_id = ".$ticketId." WHERE id = ".$rowDetail['id']);
                    mysql_query("UPDATE online_orders SET fix_ticket = 1 WHERE id = ".$rowOnline['id']);
                }
            }
        }
    }
}
?>
<?php $tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var oTableAmenity;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        oTableAmenity = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/ajax/",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $(".btnViewAmenity").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var name = $(this).attr('name');
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/" + id);
                });
                $(".btnEditAmenity").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var name = $(this).attr('name');
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/edit/" + id);
                });
                $(".btnDeleteAmenity").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var name = $(this).attr('name');
                    $("#dialog").dialog('option', 'title', '<?php echo DIALOG_CONFIRMATION; ?>');
                    $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_DELETE; ?> <b>' + name + '</b>?</p>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_CONFIRMATION; ?>',
			            resizable: false,
			            modal: true,
                        width: 'auto',
                        height: 'auto',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
			            buttons: {
                            '<?php echo ACTION_DELETE; ?>': function() {
                                $.ajax({
                                    type: "GET",
                                    url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/delete/" + id,
                                    data: "",
                                    beforeSend: function(){
                                        $("#dialog").dialog("close");
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                    },
                                    success: function(result){
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                        oCache.iCacheLower = -1;
                                        oTableAmenity.fnDraw(false);
                                        // alert message
                                        if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>'){
                                            createSysAct('Amenity', 'Delete', 2, result);
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                        }else {
                                            createSysAct('Amenity', 'Delete', 1, '');
                                            // alert message
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                                        }
                                        $("#dialog").dialog({
                                            title: '<?php echo DIALOG_INFORMATION; ?>',
                                            resizable: false,
                                            modal: true,
                                            width: 'auto',
                                            height: 'auto',
                                            buttons: {
                                                '<?php echo ACTION_CLOSE; ?>': function() {
                                                    $(this).dialog("close");
                                                }
                                            }
                                        });
                                    }
                                });
                            },
                            '<?php echo ACTION_CANCEL; ?>': function() {
                                $(this).dialog("close");
                            }
			            }
                    });
                });
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0,-1 ]
            }],
            "aaSorting": [[ 2, "asc" ]]
        });
        $(".btnAddAmenity").click(function(event){
            event.preventDefault();
            var leftPanel=$(this).parent().parent().parent();
            var rightPanel=leftPanel.parent().find(".rightPanel");
            leftPanel.hide("slide", { direction: "left" }, 500, function() {
                rightPanel.show();
            });
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/add/");
        });
    });
</script>
<div class="leftPanel">
    <?php if($allowAdd){ ?>
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <div class="buttons">
            <a href="" class="positive btnAddAmenity">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_AMENITY_ADD; ?>
            </a>
        </div>
        <div style="clear: both;"></div>
    </div>
    <br />
    <?php } ?>
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table" cellspacing="0">
            <thead>
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_PHOTO; ?></th>
                    <th><?php echo TABLE_NAME; ?></th>
                    <th><?php echo ACTION_ACTION; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="4" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <br />
    <br />
    <?php if($allowAdd){ ?>
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <div class="buttons">
            <a href="" class="positive btnAddAmenity">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_AMENITY_ADD; ?>
            </a>
        </div>
        <div style="clear: both;"></div>
    </div>
    <?php } ?>
</div>
<div class="rightPanel"></div>