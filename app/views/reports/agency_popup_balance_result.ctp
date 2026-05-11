<?php
include('includes/function.php');
$rnd = rand();
$printArea = "printArea" . $rnd;
$btnPrint  = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;
/**
 * export to excel
 */
$filename = "public/report/agency_popup_balance" . $user['User']['id'] . ".csv";
$fp = fopen($filename,"wb");
$excelContent  = REPORT_AGENCY_POP_UP_BALANCE. "\n\n";
$excelContent .= "\n".TABLE_NO."\t".TABLE_CODE."\t".TABLE_DATE."\t".MENU_AGENT."\t".GENERAL_AMOUNT."\t".TABLE_CREATED."\t".TABLE_CREATED_BY;
$msg = '<b style="font-size: 18px;">' . REPORT_AGENCY_POP_UP_BALANCE . '</b><br /><br />';
?>
<?php $tblName = "tbl" . rand(); ?>
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

        $("#<?php echo $btnExport; ?>").click(function(){
            window.open("<?php echo $this->webroot; ?>public/report/agency_popup_balance<?php echo $user['User']['id']; ?>.csv", "_blank");
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php 
    echo $this->element('/print/header-report',array('msg'=>$msg)); 
    $condition = "agency_topups.offline_project_id = 1 AND t_agents.status = 1 AND t_agents.type IN (1,2)";
    if($_POST['date_from'] != ''){
        $condition != '' ? $condition .= ' AND ' : '';
        $condition = '"' . dateConvert($_POST['date_from']) . '" <= agency_topups.date';
    }
    if ($_POST['date_to'] != '') {
        $condition != '' ? $condition .= ' AND ' : '';
        $condition .= '"' . dateConvert($_POST['date_to']) . '" >= agency_topups.date';
    }
    if ($_POST['agency'] != '') {
        $condition != '' ? $condition .= ' AND ' : '';
        $condition .= 'agency_topups.t_agency_id = ' . $_POST['agency'];
    }
    ?>
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table" style="width: 100%;">
            <thead>
                <tr>
                    <th style="font-size: 11px; width: 35px;" class="first"><?php echo TABLE_NO; ?></th>
                    <th style="width: 120px !important; font-size: 11px;"><?php echo TABLE_DATE; ?></th>
                    <th style="width: 110px !important; font-size: 11px;"><?php echo TABLE_CODE; ?></th>
                    <th style="font-size: 11px;"><?php echo MENU_AGENT; ?></th>
                    <th style="width: 110px !important; font-size: 11px; text-align: right;"><?php echo GENERAL_AMOUNT; ?></th>
                    <th style="width: 110px !important; font-size: 11px; text-align: left;"><?php echo TABLE_CREATED; ?></th>
                    <th style="width: 200px !important; font-size: 11px; text-align: left;"><?php echo TABLE_CREATED_BY; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 0;
                $totalPopup = 0;
                $sqlPopup = mysql_query("SELECT agency_topups.*, t_agents.name AS agency_name, users.username 
                                         FROM agency_topups 
                                         INNER JOIN t_agents ON t_agents.id = agency_topups.t_agency_id
                                         LEFT JOIN users ON users.id = agency_topups.created_by
                                         WHERE ".$condition." ORDER BY agency_topups.created, agency_topups.t_agency_id");
                if(mysql_num_rows($sqlPopup)){
                    while($rowPopup = mysql_fetch_array($sqlPopup)){
                        $credit = 1;
                        if($rowPopup['type'] == 1){
                            $credit = -1;
                        }
                        $totalPopup += ($rowPopup['amount'] * $credit);
                ?>
                <tr>
                    <td style="font-size: 11px;" class="first">
                        <?php 
                        echo ++$i; 
                        $excelContent .= "\n" . $i;
                        ?>
                    </td>
                    <td style="font-size: 11px;">
                        <?php 
                        echo dateShort($rowPopup['date']);
                        $excelContent .= "\t" . dateShort($rowPopup['date']);  
                        ?>
                    </td>
                    <td style="font-size: 11px;">
                        <?php 
                        echo $rowPopup['code']; 
                        $excelContent .= "\t" . $rowPopup['code']; 
                        ?>
                    </td>
                    <td style="font-size: 11px;">
                        <?php 
                        echo $rowPopup['agency_name']; 
                        $excelContent .= "\t" . $rowPopup['agency_name']; 
                        ?>
                    </td>
                    <td style="font-size: 11px; text-align: right;">
                        <?php 
                        echo number_format($rowPopup['amount'] * $credit, 2)." $"; 
                        $excelContent .= "\t" . number_format($rowPopup['amount'] * $credit, 2)." $"; 
                        ?>
                    </td>
                    <td style="font-size: 11px;">
                        <?php 
                        echo dateShort($rowPopup['created'], "d/m/Y H:i:s"); 
                        $excelContent .= "\t" . dateShort($rowPopup['created'], "d/m/Y H:i:s"); 
                        ?>
                    </td>
                    <td style="font-size: 11px;">
                        <?php 
                        echo $rowPopup['username']; 
                        $excelContent .= "\t" . $rowPopup['username']; 
                        ?>
                    </td>
                </tr>
                <?php
                    }
                    $excelContent .= "\n\t\t\t".TABLE_TOTAL;
                ?>
                <tr>
                    <td colspan="4" class="first" style="font-size: 12px; font-weight: bold;"><?php echo TABLE_TOTAL; ?></td>
                    <td style="font-size: 12px; font-weight: bold; text-align: right;">
                    <?php
                    echo number_format($totalPopup, 2)." $"; 
                    $excelContent .= "\t" . number_format($totalPopup, 2)." $"; 
                    ?>
                    </td>
                    <td colspan="2"></td>
                </tr>
                <?php
                } else {
                    $excelContent .= "\n".GENERAL_NO_RECORD;
                ?>
                <tr>
                    <td colspan="7" class="dataTables_empty first"><?php echo GENERAL_NO_RECORD; ?></td>
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
<div class="buttons">
    <button type="button" id="<?php echo $btnExport; ?>" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/csv.png" alt=""/>
        <?php echo ACTION_EXPORT_TO_EXCEL; ?>
    </button>
</div>
<div style="clear: both;"></div>
<?php
$excelContent = chr(255).chr(254).@mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
fwrite($fp,$excelContent);
fclose($fp);
?>