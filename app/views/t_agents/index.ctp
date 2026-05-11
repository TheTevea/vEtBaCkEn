<?php
// Authentication
$this->element('check_access');
$allowAdd=checkAccess($user['User']['id'], $this->params['controller'], 'add');
// $sqlAgency = mysql_query("SELECT * FROM t_agents WHERE status = 1");
// while($rowAgency = mysql_fetch_array($sqlAgency)){
//     $sqlChkCommission = mysql_query("SELECT id FROM t_agents_commission_histories WHERE t_agent_id = ".$rowAgency['id']." LIMIT 1");
//     if(!mysql_num_rows($sqlChkCommission)){
//         mysql_query("INSERT INTO `t_agents_commission_histories` (`id`, `t_agent_id`, `commission`, `commission_type`, `created`) 
//                      VALUES (NULL, '".$rowAgency['id']."', '".$rowAgency['commission']."', '".$rowAgency['commission_type']."', '".$rowAgency['created']."');");
//     }
// }
?>
<?php $tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var oTableTAgent;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#tAgentLocationBranch").chosen({width: 180});
        $("#tAgentBranch").filterOptions('com', '0', '');
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        oTableTAgent = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/ajax/",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $(".btnViewTAgent").click(function(event){
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
                $(".btnEditTAgent").click(function(event){
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
                $(".btnTopupTAgent").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/popBalance/" + id);
                });
                $(".btnDeleteTAgent").click(function(event){
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
                                        oTableTAgent.fnDraw(false);
                                        // alert message
                                        if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>'){
                                            createSysAct('TAgent', 'Delete', 2, result);
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                        }else {
                                            createSysAct('TAgent', 'Delete', 1, '');
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
                $(".btnChangeStatusTAgent").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var name = $(this).attr('name');
                    $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_UPDATE_STATUS; ?> <b>' + name + '</b>?</p>');
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
                            '<?php echo TABLE_ACTIVE; ?>': function() {
                                $.ajax({
                                    type: "GET",
                                    url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/status/" + id+"/1",
                                    data: "",
                                    beforeSend: function(){
                                        $("#dialog").dialog("close");
                                        $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                    },
                                    success: function(msg){
                                        $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                        oCache.iCacheLower = -1;
                                        oTableTAgent.fnDraw(false);
                                    }
                                });
                            },
                            '<?php echo TABLE_INACTIVE; ?>': function() {
                                $.ajax({
                                    type: "GET",
                                    url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/status/" + id+"/2",
                                    data: "",
                                    beforeSend: function(){
                                        $("#dialog").dialog("close");
                                        $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                    },
                                    success: function(msg){
                                        $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                        oCache.iCacheLower = -1;
                                        oTableTAgent.fnDraw(false);
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
        $(".btnAddTAgent").click(function(event){
            event.preventDefault();
            var leftPanel=$(this).parent().parent().parent();
            var rightPanel=leftPanel.parent().find(".rightPanel");
            leftPanel.hide("slide", { direction: "left" }, 500, function() {
                rightPanel.show();
            });
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/add/");
        });
        // Find
        $("#tAgentCompany").change(function(){
            var companyId = $(this).val();
            $("#tAgentBranch").filterOptions('com', companyId, '');
            loadTAgent();
        });
        
        $("#tAgentBranch, #tAgentType, #tAgentGroup, #tAgentLocationBranch").change(function(){
            loadTAgent();
        });
    });
    
    function loadTAgent(){
        var Tablesetting = oTableTAgent.fnSettings();
        Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+$("#tAgentCompany").val()+"/"+$("#tAgentBranch").val()+"/"+$("#tAgentType").val()+"/"+$("#tAgentGroup").val()+"/"+$("#tAgentLocationBranch").val();
        oCache.iCacheLower = -1;
        oTableTAgent.fnDraw(false);
    }
</script>
<div class="leftPanel">
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <?php if($allowAdd){ ?>
        <div class="buttons">
            <a href="" class="positive btnAddTAgent">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_AGENT_ADD; ?>
            </a>
        </div>
        <?php } ?>
        <div style="float: right; width: 1250px;">
            <label for="tAgentCompany"><?php echo MENU_COMPANY_MANAGEMENT; ?></label> :
            <select id="tAgentCompany" style="width: 170px; height: 30px; font-size: 12px;">
                <option value="all"><?php echo TABLE_ALL; ?></option>
                <?php
                foreach($companies AS $company){
                ?>
                <option value="<?php echo $company['Company']['id']; ?>"><?php echo $company['Company']['name']; ?></option>
                <?php
                }
                ?>
            </select>
            <label for="tAgentBranch" style="margin-left: 5px;"><?php echo MENU_BRANCH ?></label> : 
            <select id="tAgentBranch" style="width: 170px; height: 30px; font-size: 12px;">
                <option value="all"><?php echo TABLE_ALL; ?></option>
                <?php
                foreach($branches AS $branch){
                ?>
                <option com="<?php echo $branch['Branch']['company_id']; ?>" value="<?php echo $branch['Branch']['id']; ?>"><?php echo $branch['Branch']['name']; ?></option>
                <?php
                }
                ?>
            </select>
            <label for="tAgentLocationBranch" style="margin-left: 5px;"><?php echo MENU_MAIN_BRANCH ?></label> : 
            <select id="tAgentLocationBranch" style="width: 170px; height: 30px; font-size: 12px;">
                <option value="all"><?php echo TABLE_ALL; ?></option>
                <?php
                $sqlMainBranch = mysql_query("SELECT * FROM main_branches WHERE offline_project_id = 1 AND is_active = 1");
                while($rowData = mysql_fetch_array($sqlMainBranch)){
                ?>
                <option value="<?php echo $rowData['id']; ?>"><?php echo $rowData['name']; ?></option>
                <?php
                }
                ?>
            </select>
            <label for="tAgentGroup" style="margin-left: 5px;"><?php echo MENU_AGENT_TYPE ?></label> : 
            <select id="tAgentGroup" style="width: 170px; height: 30px; font-size: 12px;">
                <option value="all"><?php echo TABLE_ALL; ?></option>
                <?php
                $sqlGroup = mysql_query("SELECT * FROM t_agent_types WHERE offline_project_id = 1 AND is_active = 1");
                while($rowGroup = mysql_fetch_array($sqlGroup)){
                ?>
                <option value="<?php echo $rowGroup['id']; ?>"><?php echo $rowGroup['name']; ?></option>
                <?php
                }
                ?>
            </select>
            <label for="tAgentType" style="margin-left: 5px;"><?php echo TABLE_TYPE ?></label> : 
            <select id="tAgentType" style="width: 170px; height: 30px; font-size: 12px;">
                <option value="all"><?php echo TABLE_ALL; ?></option>
                <option value="1"><?php echo TABLE_ONLINE; ?></option>
                <option value="2"><?php echo TABLE_OFFLINE; ?></option>
                <option value="3"><?php echo TABLE_API; ?></option>
            </select>
        </div>
        <div style="clear: both;"></div>
    </div>
    <br />
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table" cellspacing="0">
            <thead>
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo MENU_MAIN_BRANCH; ?></th>
                    <th><?php echo TABLE_CODE; ?></th>
                    <th><?php echo TABLE_NAME; ?></th>
                    <th><?php echo TABLE_EMAIL; ?></th>
                    <th><?php echo TABLE_TELEPHONE; ?></th>
                    <th><?php echo MENU_COMMISION; ?> (%)</th>
                    <th><?php echo TABLE_BALANCE; ?> ($)</th>
                    <th><?php echo "Max Balance"; ?> ($)</th>
                    <th><?php echo TABLE_TYPE; ?></th>
                    <th><?php echo TABLE_PAYMENT; ?></th>
                    <th><?php echo TABLE_STATUS; ?></th>
                    <th><?php echo USER_USER_NAME; ?></th>
                    <th><?php echo ACTION_ACTION; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="13" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <br />
    <br />
    <?php if($allowAdd){ ?>
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <div class="buttons">
            <a href="" class="positive btnAddTAgent">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_AGENT_ADD; ?>
            </a>
        </div>
        <div style="clear: both;"></div>
    </div>
    <?php } ?>
</div>
<div class="rightPanel"></div>