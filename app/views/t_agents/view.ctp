<?php
include('includes/function.php');
$rnd       = rand();
$oTable    = "oTable" . $rnd;;
$tblName   = "tbl" . rand(); ?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        <?php
        // if($this->data['TAgent']['payment'] == 1){
        ?>
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        <?php echo $oTable; ?> = $("#<?php echo $tblName; ?>").dataTable({
            "aLengthMenu": [[50, 100, 500, 1000, 5000, 10000, 1000000*1000000], [50, 100, 500, 1000, 5000, 10000, "All"]],
            "iDisplayLength": 1000000*1000000,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/balanceAjax/<?php echo $this->data['TAgent']['id']; ?>",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:nth-child(3)").css("text-align", "center");
                $("#<?php echo $tblName; ?> td:nth-child(4)").css("text-align", "center");
                $("#<?php echo $tblName; ?> td:nth-child(5)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(6)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(7)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(8)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0,1,2,3,4 ]
            }]
        });
        <?php
        // }
        ?>
        $(".btnBackTAgent").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableTAgent.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTAgent">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_AGENT_INFO); ?></legend>
        <table style="width: 100%;" cellpadding="5">
            <tr>
                <th style="width:10%;"><?php __(MENU_COMPANY_MANAGEMENT); ?></th>
                <td style="width:1%;">:</td>
                <td style="width:39%;">
                    <?php 
                    $sqlCom = mysql_query("SELECT GROUP_CONCAT(name) FROM companies WHERE id IN (SELECT company_id FROM t_agent_companies WHERE t_agent_id = ".$this->data['TAgent']['id'].")");
                    $rowCom = mysql_fetch_array($sqlCom);
                    echo $rowCom[0]; 
                    ?>
                </td>
                <th style="width:10%;"><?php __(TABLE_TYPE); ?></th>
                <td style="width:1%;">:</td>
                <td>
                    <?php 
                    if($this->data['TAgent']['type'] == 1){
                        echo "Online";
                    } else if($this->data['TAgent']['type'] == 2){
                        echo "Offline";
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th><?php __(MENU_BRANCH); ?></th>
                <td>:</td>
                <td>
                    <?php 
                    $sqlBranch = mysql_query("SELECT GROUP_CONCAT(name) FROM branches WHERE id IN (SELECT branch_id FROM t_agent_branches WHERE t_agent_id = ".$this->data['TAgent']['id'].")");
                    $rowBranch = mysql_fetch_array($sqlBranch);
                    echo $rowBranch[0]; 
                    ?>
                </td>
                <th style="width:10%;"><?php __(TABLE_PAYMENT); ?></th>
                <td style="width:1%;">:</td>
                <td>
                    <?php 
                    if($this->data['TAgent']['payment'] == 1){
                        echo "Prepaid";
                    } else if($this->data['TAgent']['payment'] == 2){
                        echo "Postpaid (Max Balance: ".number_format($this->data['TAgent']['max_balance'], 2)." $)";
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th><?php __(MENU_AGENT_TYPE); ?></th>
                <td style="width:1%;">:</td>
                <td>
                    <?php echo $this->data['TAgentType']['name']; ?>
                </td>
                <th style="width:10%;">
                    <?php echo MENU_MAIN_BRANCH; ?>
                </th>
                <td style="width:1%;">:</td>
                <td>
                    <?php echo $this->data['MainBranch']['name']; ?>
                </td>
            </tr>
            <tr>
                <th><?php __(TABLE_CODE); ?></th>
                <td style="width:1%;">:</td>
                <td>
                    <?php echo $this->data['TAgent']['code']; ?>
                </td>
                <th><?php __(TABLE_BALANCE); ?></th>
                <td style="width:1%;">:</td>
                <td>
                    <?php 
                    $sqlBalance = mysql_query("SELECT IFNULL((SELECT SUM(credit - debit) FROM `agency_balances` WHERE t_agency_id = ".$this->data['TAgent']['id']."), 0)");
                    $rowBalance = mysql_fetch_array($sqlBalance);
                    echo number_format($rowBalance[0], 2); 
                    ?> ($)
                </td>
            </tr>
            <tr>
                <th><?php __(TABLE_NAME); ?></th>
                <td style="width:1%;">:</td>
                <td>
                    <?php echo $this->data['TAgent']['name']; ?>
                </td>
                <th>
                    <?php __(MENU_COMMISION); ?> (BUS)
                    <?php
                    $symbol = "%";
                    if($this->data['TAgent']['commission_type'] == 3){
                        echo " (Fixed Amount)";
                        $symbol = "$";
                    }
                    ?>
                </th>
                <td style="width:1%;">:</td>
                <td>
                    <?php echo number_format($this->data['TAgent']['commission'], 2); ?> (<?php echo $symbol; ?>)
                </td>
            </tr>
            <tr>
                <th><?php __(TABLE_TELEPHONE); ?></th>
                <td style="width:1%;">:</td>
                <td>
                    <?php echo $this->data['TAgent']['telephone']; ?>
                </td>
                <th>
                    <?php __(MENU_COMMISION); ?> (BUVA SEA)
                    <?php
                    $symbol = "%";
                    if($this->data['TAgent']['commission_buva_sea_type'] == 3){
                        echo " (Fixed Amount)";
                        $symbol = "$";
                    }
                    ?>
                </th>
                <td style="width:1%;">:</td>
                <td>
                    <?php echo number_format($this->data['TAgent']['commission_buva_sea'], 2); ?> (<?php echo $symbol; ?>)
                </td>
            </tr>
            <tr>
                <th><?php __(TABLE_EMAIL); ?></th>
                <td style="width:1%;">:</td>
                <td>
                    <?php echo $this->data['TAgent']['e_mail']; ?>
                </td>
                <?php
                if(!empty($this->data['TAgent']['oauth_token'])){
                ?>
                <th><?php __('API Security Key'); ?></th>
                <td style="width:1%;">:</td>
                <td>
                    <?php echo $this->data['TAgent']['oauth_token']; ?> 
                </td>
                <?php
                } else {
                ?>
                <td colspan="3"></td>
                <?php
                }
                ?>
            </tr>
            <tr>
                <th><?php __(TABLE_ADDRESS); ?></th>
                <td style="width:1%;">:</td>
                <td>
                    <?php echo nl2br($this->data['TAgent']['address']); ?>
                </td>
                <td colspan="3"></td>
            </tr>
            <tr>
                <th><?php __("Updated"); ?></th>
                <td style="width:1%;">:</td>
                <td>
                    <?php 
                    if(!empty($this->data['TAgent']['modified'])){
                        echo dateShort($this->data['TAgent']['modified'], "d/m/Y H:i:s"); 
                    }
                    ?>
                </td>
                <th><?php __("Updated By"); ?></th>
                <td style="width:1%;">:</td>
                <td>
                    <?php
                    if(!empty($this->data['TAgent']['modified_by'])){
                        $sqlUser = mysql_query("SELECT * FROM users WHERE id = ".$this->data['TAgent']['modified_by']);
                        if(mysql_num_rows($sqlUser)){
                            $rowUser = mysql_fetch_array($sqlUser);
                            echo $rowUser['username'];
                        }   
                    }
                    ?>
                </td>
            </tr>
        </table>
 </fieldset>
<?php
// if($this->data['TAgent']['payment'] == 1){
?>
<fieldset>
    <legend><?php __(TABLE_TRANSACTION_DETAIL); ?></legend>
    <br/><br/>
    <table id="<?php echo $tblName; ?>" class="table_report">
        <thead>
            <tr>
                <th class="first" style="text-align: left;"><?php echo TABLE_NO; ?></th>
                <th style="width: 100px !important; text-align: left;"><?php echo GENERAL_DESCRIPTION; ?></th>
                <th style="width: 80px !important;"><?php echo TABLE_DATE; ?></th>
                <th style="width: 80px !important;"><?php echo TABLE_REFERENCE; ?>/Ticket Code</th>
                <th style="width: 100px !important; text-align: right;"><?php echo GENERAL_AMOUNT .' ($)'; ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="8" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
            </tr>
        </tbody>
    </table>
</fieldset>
<?php
// }
?>