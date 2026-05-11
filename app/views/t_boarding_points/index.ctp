<?php
// Authentication
$this->element('check_access');
$allowAdd=checkAccess($user['User']['id'], $this->params['controller'], 'add');
$tblName = "tbl" . rand(); 

$rnd = rand();
$btnExport = "btnExport" . $rnd;
?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var oTableTBoardingPoint;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#boradingPointFilterOrigin, #boradingPointFilterBranch").chosen({width: 200});
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        oTableTBoardingPoint = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/ajax/"+$("#boradingPointFilterOrigin").val()+"/"+$("#boradingPointFilterBranch").val(),
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $(".btnViewTBoardingPoint").click(function(event){
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
                $(".btnEditTBoardingPoint").click(function(event){
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
                $(".btnDeleteTBoardingPoint").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var name = $(this).attr('name');
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
                                        oTableTBoardingPoint.fnDraw(false);
                                        // alert message
                                        if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>'){
                                            createSysAct('TBoardingPoint', 'Delete', 2, result);
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                        }else {
                                            createSysAct('TBoardingPoint', 'Delete', 1, '');
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
            }]
        });
        $(".btnAddTBoardingPoint").click(function(event){
            event.preventDefault();
            var leftPanel=$(this).parent().parent().parent();
            var rightPanel=leftPanel.parent().find(".rightPanel");
            leftPanel.hide("slide", { direction: "left" }, 500, function() {
                rightPanel.show();
            });
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/add/");
        });

        $("#<?php echo $btnExport; ?>").click(function(event){
            event.preventDefault();
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/exportExcel/",
                data: "action=export",
                beforeSend: function(){
                    $("#<?php echo $btnExport; ?>").attr('disabled','disabled');
                    $("#<?php echo $btnExport; ?>").find('img').attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                },
                success: function(){
                    $("#<?php echo $btnExport; ?>").removeAttr('disabled');
                    $("#<?php echo $btnExport; ?>").find('img').attr("src", "<?php echo $this->webroot; ?>img/button/csv.png");
                    window.open("<?php echo $this->webroot; ?>public/report/boarding_point.csv", "_blank");
                }
            });
        });

        $("#boradingPointFilterOrigin, #boradingPointFilterBranch").unbind('change').change(function(){
            filterBoradingPoint();
        });
    });

    function filterBoradingPoint(){
        var Tablesetting = oTableTBoardingPoint.fnSettings();
        Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+$("#boradingPointFilterOrigin").val()+"/"+$("#boradingPointFilterBranch").val();
        oCache.iCacheLower = -1;
        oTableTBoardingPoint.fnDraw(false);
    }
</script>
<div class="leftPanel">
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <?php if($allowAdd && $user['User']['type'] != 1){ ?>
        <div class="buttons">
            <a href="" class="positive btnAddTBoardingPoint">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_ADD_NEW_BOARDING_POINT; ?>
            </a>
        </div>
        <?php } ?>
        <div class="buttons">
            <button type="button" id="<?php echo $btnExport; ?>" class="positive">
                <img src="<?php echo $this->webroot; ?>img/button/csv.png" alt=""/>
                <?php echo ACTION_EXPORT_TO_EXCEL; ?>
            </button>
        </div>
        <div style="width: 700px; float: right;">
            <table cellpadding="5" cellspacing="0" style="width: 100%;">
                <tr>
                    <td style="width: 70px;"><label for="boradingPointFilterOrigin"><?php echo "Origin" ?></label> :</td>
                    <td style="width: 200px;">
                        <select id="boradingPointFilterOrigin" style="width: 200px; height: 30px; font-size: 12px;">
                            <option value="all"><?php echo TABLE_ALL; ?></option>
                            <?php
                            foreach($tDestinations AS $tDestination){
                                echo '<option value="'.$tDestination['TDestination']['id'].'">'.$tDestination['TDestination']['name'].'</option>';    
                            }
                            ?>
                        </select>
                    </td>
                    <td style="width: 70px;"><label for="boradingPointFilterBranch"><?php echo MENU_BRANCH ?></label> :</td>
                    <td style="width: 200px;">
                        <select id="boradingPointFilterBranch" style="width: 200px; height: 30px; font-size: 12px;">
                            <option value="all"><?php echo TABLE_ALL; ?></option>
                            <?php
                            foreach($branches AS $branch){
                                echo '<option value="'.$branch['Branch']['id'].'">'.$branch['Branch']['name'].'</option>';    
                            }
                            ?>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
        <div style="clear: both;"></div>
    </div>
    <br />
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table" cellspacing="0">
            <thead>
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo MENU_BRANCH; ?></th>
                    <th><?php echo TABLE_NAME; ?></th>
                    <th><?php echo TABLE_ADDRESS; ?></th>
                    <th><?php echo 'Map'; ?></th>
                    <th><?php echo ACTION_ACTION; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <br />
    <br />
    <?php if($allowAdd && $user['User']['type'] != 1){ ?>
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <div class="buttons">
            <a href="" class="positive btnAddTBoardingPoint">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_ADD_NEW_BOARDING_POINT; ?>
            </a>
        </div>
        <div style="clear: both;"></div>
    </div>
    <?php } ?>
</div>
<div class="rightPanel"></div>