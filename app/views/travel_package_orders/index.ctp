<?php
$tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var oTableTravelPackageOrder;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        var tel = $("#TravelPackageOrderTelephone").val();
        if(tel == ""){
            tel = "all";
        }
        var date = 'all';
        if($("#TravelPackageOrderDate").val() != ""){
            $("#TravelPackageOrderDate").datepicker("option", "dateFormat", "yy-mm-dd");
            date = $("#TravelPackageOrderDate").val();
            $("#TravelPackageOrderDate").datepicker("option", "dateFormat", "dd/mm/yy");
        }
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        oTableTravelPackageOrder = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/ajax/"+$("#TravelPackageOrderPackage").val()+"/"+$("#TravelPackageOrderStatus").val()+"/"+tel+"/"+date,
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $(".BtnTravelPackageOrder").click(function(event){
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

                $(".BtnEditTravelPackageOrder").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/edit/" + id);
                });

                $(".BtnDisableTravelPackageOrder").click(function(event){
                    event.preventDefault();
                    var id   = $(this).attr('rel');
                    var name = $(this).attr('name');
                    $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you want to disable <b>' + name + '</b>?</p>');
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
                                        oTableTravelPackageOrder.fnDraw(false);
                                        // alert message
                                        if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>'){
                                            createSysAct('Travel Package Order', 'Disable', 2, result);
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                        }else {
                                            createSysAct('Travel Package Order', 'Disable', 1, '');
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

                $("#lblSearchTravelPackageOrder").text("<?php echo GENERAL_SEARCH; ?>");
                $("#btnSearchTravelPackageOrder").attr("disabled", false);
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0,-1 ]
            }],
            "aaSorting": [[ 2, "asc" ]]
        });

        $("#TravelPackageOrderDate").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        });
        
        $("#btnTravelPackageOrderClearDate").unbind("click").click(function(){
            $('#TravelPackageOrderDate').val('');
        });

        $(".btnSearchTravelPackageOrder").unbind("click").click(function(event){
            filterTravelPackageCustomer();
        });
    });

    function filterTravelPackageCustomer(){
        $("#lblSearchTravelPackageOrder").text("Loading..");
        $("#btnSearchTravelPackageOrder").attr("disabled", true);
        var tel = $("#TravelPackageOrderTelephone").val();
        if(tel == ""){
            tel = "all";
        }
        var date = 'all';
        if($("#TravelPackageOrderDate").val() != ""){
            $("#TravelPackageOrderDate").datepicker("option", "dateFormat", "yy-mm-dd");
            date = $("#TravelPackageOrderDate").val();
            $("#TravelPackageOrderDate").datepicker("option", "dateFormat", "dd/mm/yy");
        }
        var Tablesetting = oTableTravelPackageOrder.fnSettings();
        Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+$("#TravelPackageOrderPackage").val()+"/"+$("#TravelPackageOrderStatus").val()+"/"+tel+"/"+date;
        oCache.iCacheLower = -1;
        oTableTravelPackageOrder.fnDraw(false);
    }
</script>
<div class="leftPanel">
    <br />
    <div style="width: 100%;">
        <table cellpadding="0" cellspacing="0" style="width: 1400px; float: right;">
            <tr>
                <td style="width: 50px;"><label for="TravelPackageOrderDate" style="margin-left: 5px;"><?php echo TABLE_DATE; ?></label> :</td>
                <td style="width: 120px;">
                    <input id="TravelPackageOrderDate" style="width: 90px; height: 25px; font-size: 12px;" value="" />
                    <img style="cursor: pointer;" src="<?php echo $this->webroot; ?>img/button/clear.png" alt="" id="btnTravelPackageOrderClearDate" />
                </td>
                <td style="width: 50px;"><label for="TravelPackageOrderTelephone" style="margin-left: 5px;"><?php echo TABLE_TELEPHONE; ?></label> :</td>
                <td style="width: 150px;">
                    <input id="TravelPackageOrderTelephone" style="width: 145px; height: 25px; font-size: 12px;" value="" />
                </td>
                <td style="width: 120px;"><label for="TravelPackageOrderPackage" style="margin-left: 5px;"><?php echo "Travel Package"; ?></label> :</td>
                <td style="width: 270px;">
                    <select id="TravelPackageOrderPackage" style="height: 30px; width: 250px;">
                        <option value="all"><?php echo TABLE_ALL; ?></option>
                        <?php
                        $sqlTravelPackage = mysql_query("SELECT * FROM travel_packages WHERE status = 1");
                        while($rowTravelPackage = mysql_fetch_array($sqlTravelPackage)){
                        ?>
                        <option value="<?php echo $rowTravelPackage['id']; ?>"><?php echo $rowTravelPackage['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </td>
                <td style="width: 50px;"><label for="TravelPackageOrderStatus" style="margin-left: 5px;"><?php echo "Status"; ?></label> :</td>
                <td style="width: 130px;">
                    <select id="TravelPackageOrderStatus" style="width: 120px; height: 30px; font-size: 12px;">
                        <option value="all"><?php echo TABLE_ALL; ?></option>
                        <option value="1"><?php echo "Active"; ?></option>
                        <option value="2"><?php echo "Inactive"; ?></option>
                    </select>
                </td>
                <td style="width: 120px;">
                    <div class="buttons" style="float: left;">
                        <a href="#" class="positive btnSearchTravelPackageOrder">
                            <img src="<?php echo $this->webroot; ?>img/button/search.png" alt=""/>
                            <span id="lblSearchTravelPackageOrder"><?php echo GENERAL_SEARCH; ?></span>
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
                    <th class="first" style="font-size: 11px; width: 35px;"><?php echo TABLE_NO; ?></th>
                    <th style="width: 130px !important; font-size: 11px;"><?php echo "Photo"; ?></th>
                    <th style="font-size: 11px;"><?php echo "Travel Package"; ?></th>
                    <th style="width: 130px !important; font-size: 11px;"><?php echo "Date"; ?></th>
                    <th style="width: 130px !important; font-size: 11px;"><?php echo "Package Code"; ?></th>
                    <th style="width: 130px !important; font-size: 11px;"><?php echo "Transaction Code"; ?></th>
                    <th style="width: 150px !important; font-size: 11px;"><?php echo "name"; ?></th>
                    <th style="width: 100px !important; font-size: 11px;"><?php echo "Telephone"; ?></th>
                    <th style="width: 130px !important;  font-size: 11px;"><?php echo "Price"; ?></th>
                    <th style="width: 130px !important; font-size: 11px;"><?php echo "Expiry Date"; ?></th>
                    <th style="width: 130px !important; font-size: 11px;"><?php echo "Status"; ?></th>
                    <th style="width: 130px !important; font-size: 11px;"><?php echo "Action"; ?></th>
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