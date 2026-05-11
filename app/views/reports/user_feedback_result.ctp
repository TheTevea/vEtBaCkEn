<?php
include('includes/function.php');
$rnd = rand();
$oTable    = "oTable" . $rnd;
$printArea = "printArea" . $rnd;
$btnPrint  = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;
$tblName   = "tbl" . rand(); 

/**
 * export to excel
 */
$filename = "public/report/survey" . $user['User']['id'] . ".csv";
$fp = fopen($filename,"wb");
$excelContent = REPORT_SALES_TICKET_BRANCH. " (VAT)\n\n";
$excelContent .= "\n".TABLE_NO."\tCustomer\tTelephone";
?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $btnPrint; ?>").click(function(){
            w=window.open();
            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
            w.document.write($("#<?php echo $printArea; ?>").html());
            w.document.close();
            w.print();
            w.close();
        });

        $("#<?php echo $btnExport; ?>").click(function(){
            window.open("<?php echo $this->webroot; ?>public/report/survey<?php echo $user['User']['id']; ?>.csv", "_blank");
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . REPORT_SURVEY . '</b><br /><br />';
    $condition = "";
    if($_POST['date_from'] !='' ) {
        $msg .= REPORT_FROM.': '.$_POST['date_from'];
        $condition .= " DATE(created) >= '".dateConvert($_POST['date_from'])."'";
    }
    if($_POST['date_to'] !='' ) {
        $msg .= ' '.REPORT_TO.': '.$_POST['date_to'];
        $condition .= " AND DATE(created) <= '".dateConvert($_POST['date_to'])."'";
    }
    if($_POST['bus']!='') {
        $sqlBus = mysql_query("SELECT code, name FROM buses WHERE id = ".$_POST['bus']);
        $rowBus = mysql_fetch_array($sqlBus);
        $msg .= '<br/>'.MENU_BUS.': '.$rowBus['code']." (".$rowBus['name'].")";
        $condition .= " AND bus_id = ".$_POST['bus'];
    } else {
        if($_POST['bus_type']!='') {
            $sqlBusType = mysql_query("SELECT name FROM bus_types WHERE id = ".$_POST['bus_type']);
            $rowBusType = mysql_fetch_array($sqlBusType);
            $msg .= '<br/>'.MENU_BUS_TYPE.': '.$rowBusType[0];
            $condition .= " AND bus_id IN (SELECT id FROM buses WHERE bus_type_id = ".$_POST['bus_type']." AND is_active = 1)";
        }
    }
    echo $this->element('/print/header-report',array('msg'=>$msg));
    ?>
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table" style="width: 100%;">
            <thead>
                <tr>
                    <th style="font-size: 10px; width: 35px;" class="first"><?php echo TABLE_NO; ?></th>
                    <th style="width: 170px !important; font-size: 10px;"><?php echo "Customer"; ?></th>
                    <th style="width: 130px !important; font-size: 10px;"><?php echo "Telephone"; ?></th>
                    <?php
                    $totalCol = 0;
                    $sqlQuestion = mysql_query("SELECT * FROM question_feedbacks WHERE is_active = 1");
                    while($rowQuestion = mysql_fetch_array($sqlQuestion)){
                        $totalCol++;
                        $excelContent .= "\t".$rowQuestion['name'];
                    ?>
                    <th style="width: 75px !important; font-size: 10px;"><?php echo $rowQuestion['name']; ?></th>
                    <?php
                    }
                    $excelContent .= "\tRemark";
                    ?>
                    <th style="width: 130px !important; font-size: 10px;"><?php echo "Remark"; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $index = 0;
                $sqlFeedback = mysql_query("SELECT *
                                          FROM user_feedbacks 
                                          WHERE ".$condition." ORDER BY created ASC");
                if(mysql_num_rows($sqlFeedback)){
                    while($rowFeedback = mysql_fetch_array($sqlFeedback)){
                        
                ?>
                <tr>
                    <td style="padding: 5px; font-size: 12px; text-align: center;" class="first">
                        <?php 
                        echo ++$index; 
                        $excelContent .= "\n" . $index;
                        ?>
                    </td>
                    <td style="padding: 5px; font-size: 12px;">
                        <?php 
                        echo $rowFeedback['name']; 
                        $excelContent .= "\t" . $rowFeedback['name']; 
                        ?>
                    </td>
                    <td style="padding: 5px; font-size: 12px;">
                        <?php 
                        echo $rowFeedback['telephone']; 
                        $excelContent .= "\t" . $rowFeedback['telephone']; 
                        ?>
                    </td>
                    <?php
                    $sqlQuestion = mysql_query("SELECT * FROM question_feedbacks WHERE is_active = 1");
                    while($rowQuestion = mysql_fetch_array($sqlQuestion)){
                        $sqlRate = mysql_query("SELECT * FROM user_feedback_details WHERE user_feedback_id = ".$rowFeedback['id']." AND question_feedback_id = ".$rowQuestion['id']." LIMIT 1");
                        $rowRate = mysql_fetch_array($sqlRate);
                    ?>
                    <td style="padding: 5px; font-size: 12px;">
                        <?php 
                        echo $rowRate['score']; 
                        $excelContent .= "\t" . $rowRate['score']; 
                        ?>
                    </td>
                    <?php
                    }
                    ?>
                    <td style="padding: 5px; font-size: 12px;">
                        <?php 
                        echo $rowFeedback['note']; 
                        $excelContent .= "\t" . $rowFeedback['note']; 
                        ?>
                    </td>
                </tr>
                <?php
                    }
                } else {
                    $excelContent .= "\n".TABLE_NO_RECORD;
                ?>
                <tr>
                    <td colspan="<?php echo ($totalCol + 4); ?>" class="dataTables_empty first"><?php echo TABLE_NO_RECORD; ?></td>
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