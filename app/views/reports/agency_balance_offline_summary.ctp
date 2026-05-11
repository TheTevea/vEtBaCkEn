<?php
include('includes/function.php');
$rnd       = rand();
$printArea = "printArea" . $rnd;
$btnPrint  = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;
$tblName   = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
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
                    <th style="width: 100px !important; text-align: left;"><?php echo TABLE_NAME; ?></th>
                    <th style="width: 100px !important; text-align: right;"><?php echo 'Balance ($)'; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $condition = "1";
                if ($_POST['date_to'] != '') {
                    $condition != '' ? $condition .= ' AND ' : '';
                    $condition .= '"' . dateConvert($_POST['date_to']) . '" >= DATE(gl.created)';
                }
                if ($_POST['agency'] != '') {
                    $condition != '' ? $condition .= ' AND ' : '';
                    $condition .= 'gl.t_agency_id=' . $_POST['agency'];
                }
                $i = 0;
                $sqlAgency = mysql_query("SELECT 
                                        CONCAT(t_agents.code,' - ',t_agents.name) AS agency_name,
                                        SUM(gl.net_price) AS net_price,
                                        SUM(gl.vat_price) AS vat_price,
                                        SUM(gl.bonus) AS bonus,
                                        SUM(gl.credit - gl.debit) AS amount
                                        FROM agency_balances gl 
                                        INNER JOIN t_agents ON t_agents.id = gl.t_agency_id AND t_agents.type = 2
                                        WHERE ".$condition." GROUP BY gl.t_agency_id");
                if(mysql_num_rows($sqlAgency)){
                    $totalNetPrice = 0;
                    $totalVat = 0;
                    $total = 0;
                    while($rowAgency = mysql_fetch_array($sqlAgency)){
                        $total += $rowAgency['amount'];
                ?>
                <tr>
                    <td class="first" style="font-size: 11px;"><?php echo ++$i; ?></td>
                    <td style="font-size: 12px;"><?php echo $rowAgency['agency_name']; ?></td>
                    <td style="font-size: 12px; text-align: right;"><?php echo number_format($rowAgency['amount'], 2)." $"; ?></td>
                </tr>
                <?php
                    }
                ?>
                <tr>
                    <td colspan="2" style="font-size: 11px; font-weight: bold; text-align: right;">Total</td>
                    <td style="font-size: 12px; font-weight: bold; text-align: right;"><?php echo number_format($total, 2)." $"; ?></td>
                </tr>
                <?php
                } else {
                ?>
                <tr>
                    <td colspan="3" class="first dataTables_empty"><?php echo GENERAL_NO_RECORD; ?></td>
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