<?php
$tblName  = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var oTableOnlineOrder;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#OnlineOrderPaymentType").chosen({width: 350});
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        oTableOnlineOrder = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/ajax/",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $(".btnViewOnlineOrder").unbind("click").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/" + id);
                });
                $(".btnOnlineOrderViewAPi").unbind("click").click(function(event){
                    event.preventDefault();
                    var id   = $(this).attr("rel");
                    $.ajax({
                        type:   "GET",
                        url:    "<?php echo $this->base . "/".$this->params['controller']."/viewApiResponse/"; ?>"+id,
                        beforeSend: function(){
                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                            // modal box - open
                            $("#dialogModal").html('<p style="text-align: center;"><img alt="" src="<?php echo $this->webroot; ?>img/ajax-loader.gif" /></p>');
                            $("#dialogModal").dialog({
                                title: '<?php echo DIALOG_LOADING; ?>',
                                resizable: false,
                                modal: true,
                                closeOnEscape: false,
                                width: 180,
                                height: 100,
                                open: function(event, ui){
                                    $(".ui-dialog-buttonpane").show();
                                    $(".ui-dialog-titlebar-close").hide();
                                },
                                close: function(event, ui){
                                    $(".ui-dialog-titlebar-close").show();
                                }
                            });
                        },
                        success: function(msg){
                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                            $("#dialogModal").dialog("close");
                            $("#dialog").html(msg).dialog({
                                title: 'View Bank APi Response',
                                resizable: false,
                                modal: true,
                                width: 450,
                                height: 600,
                                open: function(event, ui){
                                    $(".ui-dialog-buttonpane").show();
                                },
                                buttons: {
                                    '<?php echo ACTION_CLOSE; ?>': function() {
                                        $(this).dialog("close");
                                    }
                                }
                            });
                        }
                    });
                });
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 6,7,8 ]
            }],
            "aaSorting": [[ 0, "desc" ]]
        });

        // Date
        $("#OnlineOrderDate").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        });

        $("#btnOnlineOrderClearDate").unbind("click").click(function(event){
            event.preventDefault();
            $("#OnlineOrderDate").val("");
        });

        // Find
        $(".btnSearchOnlineOrder").unbind("click").click(function(event){
            var date = "all";
            if($("#OnlineOrderDate").val() != ""){
                date = $("#OnlineOrderDate").val().toString().split("/")[2]+"-"+$("#OnlineOrderDate").val().toString().split("/")[1]+"-"+$("#OnlineOrderDate").val().toString().split("/")[0];
            }
            var payment = "all";
            if($("#OnlineOrderPaymentType").val() != null){
                payment = $("#OnlineOrderPaymentType").val();
            }
            event.preventDefault();
            var Tablesetting = oTableOnlineOrder.fnSettings();
            Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+$("#OnlineOrderType").val()+"/"+$("#OnlineOrderStatus").val()+"/"+date+"/"+payment;
            oCache.iCacheLower = -1;
            oTableOnlineOrder.fnDraw(false);
        });
    });
</script>
<div class="leftPanel">
    <br />
    <div style="width: 100%;">
        <table cellpadding="0" cellspacing="0" style="width: 1300px; float: right;">
            <tr>
                <td style="width: 50px;"><label for="OnlineOrderDate" style="margin-left: 5px;"><?php echo TABLE_DATE; ?></label> :</td>
                <td style="width: 100px;">
                    <input id="OnlineOrderDate" style="width: 90px; height: 25px; font-size: 12px;" value="" />
                    <img style="cursor: pointer;" src="<?php echo $this->webroot; ?>img/button/clear.png" alt="" id="btnOnlineOrderClearDate" />
                </td>
                <td style="width: 90px;"><label for="OnlineOrderPaymentType" style="margin-left: 5px;"><?php echo "Payment"; ?></label> :</td>
                <td style="width: 350px;">
                    <!-- <input type="hidden" id="OnlineOrderPaymentValue" /> -->
                    <select id="OnlineOrderPaymentType" multiple="" data-placeholder="<?php echo TABLE_ALL; ?>">
                        <?php
                        $sqlPayMethod = mysql_query("SELECT * FROM payment_methods WHERE is_active = 1 AND id NOT IN (1,2,3)");
                        while($rowPayMethod = mysql_fetch_array($sqlPayMethod)){
                        ?>
                        <option value="<?php echo $rowPayMethod['id']; ?>"><?php echo $rowPayMethod['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </td>
                <td style="width: 90px;"><label for="OnlineOrderType" style="margin-left: 5px;"><?php echo "Booking Type"; ?></label> :</td>
                <td style="width: 130px;">
                    <select id="OnlineOrderType" style="width: 120px; height: 30px; font-size: 12px;">
                        <option value="all"><?php echo TABLE_ALL; ?></option>
                        <option value="1"><?php echo "Web"; ?></option>
                        <option value="2"><?php echo "App"; ?></option>
                        <option value="3"><?php echo "APi"; ?></option>
                        <option value="4"><?php echo "Terminal"; ?></option>
                        <option value="5"><?php echo "ABA (Mini App)"; ?></option>
                        <option value="6"><?php echo "Internal App"; ?></option>
                    </select>
                </td>
                <td style="width: 50px;"><label for="OnlineOrderStatus" style="margin-left: 5px;"><?php echo "Status"; ?></label> :</td>
                <td style="width: 130px;">
                    <select id="OnlineOrderStatus" style="width: 120px; height: 30px; font-size: 12px;">
                        <option value="all"><?php echo TABLE_ALL; ?></option>
                        <option value="1"><?php echo "Confirmed"; ?></option>
                        <option value="4"><?php echo "Completed"; ?></option>
                        <option value="0"><?php echo "Cancel"; ?></option>
                    </select>
                </td>
                <td style="width: 120px;">
                    <div class="buttons" style="float: left;">
                        <a href="#" class="positive btnSearchOnlineOrder">
                            <img src="<?php echo $this->webroot; ?>img/button/search.png" alt=""/>
                            <span><?php echo GENERAL_SEARCH; ?></span>
                        </a>
                    </div>
                </td>
            </tr>
        </table>
        <div style="clear: both;"></div>
    </div>
    <br />
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table" cellspacing="0">
            <thead>
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_DATE; ?></th>
                    <th><?php echo TABLE_CODE; ?></th>
                    <th><?php echo "Reference"; ?></th>
                    <th><?php echo TABLE_NAME; ?></th>
                    <th><?php echo TABLE_TELEPHONE; ?></th>
                    <th><?php echo TABLE_EMAIL; ?></th>
                    <th><?php echo "Payment Type"; ?></th>
                    <th><?php echo TABLE_TOTAL_AMOUNT; ?></th>
                    <th><?php echo TABLE_TYPE; ?></th>
                    <th><?php echo TABLE_NOTE; ?></th>
                    <th><?php echo TABLE_MODIFIED; ?></th>
                    <th><?php echo TABLE_STATUS; ?></th>
                    <th><?php echo ACTION_ACTION; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="12" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <br />
    <br />
</div>
<div class="rightPanel"></div>