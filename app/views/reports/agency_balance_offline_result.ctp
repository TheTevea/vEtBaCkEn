<?php
include('includes/function.php');
$rnd       = rand();
$oTable    = "oTable" . $rnd;
$printArea = "printArea" . $rnd;
$btnPrint  = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;
$tblName   = "tbl" . rand(); 

$tableName = "agency_balance_offline_" . $user['User']['id'];
mysql_query("DROP TABLE `".$tableName."`;");
mysql_query("SET max_heap_table_size = 1024*1024*1024");
mysql_query("CREATE TABLE IF NOT EXISTS `$tableName` (
                  `id` bigint(20) NOT NULL AUTO_INCREMENT,
                  `debit` double DEFAULT NULL,
                  `credit` double DEFAULT NULL,
                  `t_agency_id` bigint(20) DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `t_agency_id` (`t_agency_id`)
                ) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
mysql_query("TRUNCATE $tableName") or die(mysql_error());

$glCondition = "1";
$agencyCon   = "";
if ($_POST['date_from'] != '') {
    $glCondition != '' ? $glCondition .= ' AND ' : '';
    $glCondition .= '"' . dateConvert(str_replace("|||", "/", $_POST['date_from'])) . '" > DATE(created)';
}
if ($_POST['agency'] != '') {
    $glCondition != '' ? $glCondition .= ' AND ' : '';
    $glCondition .= 't_agency_id=' . $_POST['agency'];
    $agencyCon   .= " AND id = ". $_POST['agency'];
} else {
    $glCondition != '' ? $glCondition .= ' AND ' : '';
    $glCondition .= 't_agency_id IN (SELECT id FROM t_agents WHERE offline_project_id = 1 AND status = 1 AND type = 2 AND payment = 2)';
}
mysql_query("INSERT INTO `".$tableName."` (t_agency_id, debit, credit) SELECT t_agency_id, SUM(debit), SUM(credit) FROM agency_balances WHERE ".$glCondition." GROUP BY t_agency_id");
$agencyBgBalance = array();
// Beging Balance
$sqlBg = mysql_query("SELECT t_agency_id, SUM(credit - debit) AS balance FROM `".$tableName."` WHERE 1 GROUP BY t_agency_id");
while($rowBg = mysql_fetch_array($sqlBg)){
    $agencyBgBalance[$rowBg['t_agency_id']] = $rowBg['balance'];
}
?>
<!-- <script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script> -->
<script type="text/javascript">
    var <?php echo $oTable; ?>;
    $(document).ready(function(){
        // $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        // <?php echo $oTable; ?> = $("#<?php echo $tblName; ?>").dataTable({
        //     "aLengthMenu": [[50, 100, 500, 1000, 5000, 10000, 1000000*1000000], [50, 100, 500, 1000, 5000, 10000, "All"]],
        //     "iDisplayLength": 1000000*1000000,
        //     "bProcessing": true,
        //     "bServerSide": true,
        //     "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/agencyBalanceOfflineAjax/<?php echo str_replace("/", "|||", implode(',', $_POST)); ?>",
        //     "fnServerData": fnDataTablesPipeline,
        //     "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
        //         $("#<?php echo $tblName; ?>_length, #<?php echo $tblName; ?>_filter, #<?php echo $tblName; ?>_info, #<?php echo $tblName; ?>_paginate").hide();
        //         $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        //         $("#<?php echo $tblName; ?> td:nth-child(3)").css("text-align", "left");
        //         $("#<?php echo $tblName; ?> td:nth-child(4)").css("text-align", "left");
        //         $("#<?php echo $tblName; ?> td:nth-child(5)").css("text-align", "right");
        //         $("#<?php echo $tblName; ?> td:nth-child(6)").css("text-align", "right");
        //         $("#<?php echo $tblName; ?> td:nth-child(7)").css("text-align", "right");
        //         $("#<?php echo $tblName; ?> td:nth-child(8)").css("text-align", "right");
        //         $("#<?php echo $tblName; ?> td:nth-child(9)").css("text-align", "right");
        //         $("#<?php echo $tblName; ?> td:nth-child(10)").css("text-align", "right");
        //         $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
        //         return sPre;
        //     },
        //     "fnDrawCallback": function(oSettings, json) {
        //         $("#<?php echo $tblName; ?> .colspanParent").parent().attr("colspan", 5);
        //         $("#<?php echo $tblName; ?> .colspanParent").parent().next().remove();
        //         $("#<?php echo $tblName; ?> .colspanParent").parent().next().remove();
        //         $("#<?php echo $tblName; ?> .colspanParent").parent().next().remove();
        //         $("#<?php echo $tblName; ?> .colspanParent").parent().next().remove();
        //     },
        //     "aoColumnDefs": [{
        //         "sType": "numeric", "aTargets": [ 0 ],
        //         "bSortable": false, "aTargets": [ 0,1,2,3,4,5 ]
        //     }]
        // });
        $("#<?php echo $btnPrint; ?>").click(function(){
            $(".dataTables_length").hide();
            $(".dataTables_filter").hide();
            $(".dataTables_paginate").hide();
            w=window.open();
            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
            w.document.write($("#<?php echo $printArea; ?>").html());
            w.document.close();
            w.print();
            w.close();
            $(".dataTables_length").show();
            $(".dataTables_filter").show();
            $(".dataTables_paginate").show();
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . REPORT_AGENCY_BALANCE . ' (Offline)</b><br /><br />';
    if($_POST['date_from']!='') {
        $msg .= REPORT_FROM.': '.$_POST['date_from'];
    }
    if($_POST['date_to']!='') {
        $msg .= ' '.REPORT_TO.': '.$_POST['date_to'];
    }
    echo $this->element('/print/header-report',array('msg'=>$msg));
    ?>
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table_report">
            <thead>
                <tr>
                    <th class="first" style="text-align: left;"><?php echo TABLE_NO; ?></th>
                    <th style="width: 100px !important; text-align: left;"><?php echo GENERAL_DESCRIPTION; ?></th>
                    <th style="width: 80px !important; text-align: left;"><?php echo TABLE_DATE; ?></th>
                    <th style="width: 80px !important; text-align: left;"><?php echo TABLE_REFERENCE; ?></th>
                    <th style="width: 100px !important; text-align: right;"><?php echo GENERAL_AMOUNT .' ($)'; ?></th>
                    <th style="width: 100px !important; text-align: right;"><?php echo GENERAL_BALANCE .' ($)'; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalBalance = 0;
                $sqlAgency = mysql_query("SELECT * FROM t_agents WHERE offline_project_id = 1 AND status = 1 AND type = 2 AND payment = 2".$agencyCon);
                if(mysql_num_rows($sqlAgency)){
                    while($rowAgency = mysql_fetch_array($sqlAgency)){
                        $bgBalance  = 0;
                        $endBalance = 0;
                        $sqlBalance = mysql_query("SELECT gl.module, gl.created, gl.reference, IF(gl.credit>0,gl.credit,gl.debit*-1) AS balance 
                                                   FROM agency_balances AS gl 
                                                   WHERE gl.t_agency_id = ".$rowAgency['id']." AND DATE(gl.created) >= '".dateConvert(str_replace("|||", "/", $_POST['date_from']))."' AND DATE(gl.created) <= '".dateConvert(str_replace("|||", "/", $_POST['date_to']))."'");
                        if(mysql_num_rows($sqlBalance)){
                ?>
                <tr>
                    <td colspan="4" class="first"><?php echo $rowAgency['code']." - ".$rowAgency['name']; ?></td>
                    <td>Beginning Balance</td>
                    <td style="text-align: right;">
                        <?php
                            if(!empty($agencyBgBalance[$rowAgency['id']])){
                                $bgBalance = $agencyBgBalance[$rowAgency['id']];
                                echo number_format($agencyBgBalance[$rowAgency['id']], 2);
                            } else {
                                echo "0.00";
                            }
                        ?>
                    </td>
                </tr>
                <?php
                            $index = 0;
                            while($rowBalance = mysql_fetch_array($sqlBalance)){
                ?>
                <tr>
                    <td class="first"><?php echo ++$index; ?></td>
                    <td><?php echo $rowBalance['module']; ?></td>
                    <td><?php echo dateShort($rowBalance['created'], "d/m/Y H:i:s"); ?></td>
                    <td><?php echo $rowBalance['reference']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($rowBalance['balance'], 2); ?></td>
                    <td style="text-align: right;">
                        <?php 
                        if($index == 1){
                            $endBalance   = $bgBalance + $rowBalance['balance'];
                        } else {
                            $endBalance   = $endBalance + $rowBalance['balance'];
                        }
                        echo number_format($endBalance, 2);
                        ?>
                    </td>
                </tr>
                <?php
                            }
                            $totalBalance += $endBalance;
                        } else {
                            if(!empty($agencyBgBalance[$rowAgency['id']])){
                                $endBalance = $agencyBgBalance[$rowAgency['id']];
                                $totalBalance += $endBalance;
                            }
                        }
                ?>
                <tr>
                    <td colspan="5" class="first"><?php echo $rowAgency['code']." - ".$rowAgency['name']; ?></td>
                    <td style="text-align: right;">
                        <?php
                        echo number_format($endBalance, 2);
                        ?>
                    </td>
                </tr>
                <?php
                    }
                ?>
                <tr>
                    <td colspan="5" class="first">Current Balance</td>
                    <td style="text-align: right;">
                        <?php
                        echo number_format($totalBalance, 2);
                        ?>
                    </td>
                </tr>
                <?php
                } else {
                ?>
                <tr>
                    <td colspan="8" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td>
                </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<div style="clear: both;"></div>
<br />
<div class="buttons">
    <button type="button" id="<?php echo $btnPrint; ?>" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/>
        <?php echo ACTION_PRINT; ?>
    </button>
</div>
<div style="clear: both;"></div>