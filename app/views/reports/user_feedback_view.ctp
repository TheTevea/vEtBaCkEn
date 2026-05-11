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
$excelContent = REPORT_SURVEY. " (Detail)\n\n";
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

        $(".btnBackSurveyViewDetail").click(function(event){
            event.preventDefault();
            var rightPanel=$(this).parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<br/>
<div class="buttons">
    <a href="" class="positive btnBackSurveyViewDetail">
        <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
        <?php echo ACTION_BACK; ?>
    </a>
</div>
<div style="clear: both;"></div>
<br />
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . REPORT_SURVEY . ' (Detail)</b><br /><br />';
    $condition = "";
    if($dateFrom !='' ) {
        $msg .= REPORT_FROM.': '.dateShort($dateFrom);
        $condition .= " DATE(created) >= '".$dateFrom."'";
    }
    if($dateTo !='' ) {
        $msg .= ' '.REPORT_TO.': '.dateShort($dateTo);
        $condition .= " AND DATE(created) <= '".$dateTo."'";
    }
    if($busId != '') {
        $sqlBus = mysql_query("SELECT code, name FROM buses WHERE id = ".$busId);
        $rowBus = mysql_fetch_array($sqlBus);
        $msg .= '<br/>'.MENU_BUS.': '.$rowBus['code']." (".$rowBus['name'].")";
        $condition .= " AND bus_id = ".$busId;
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
                    $excelContent .= "\n".TABLE_LOADING;
                ?>
                <tr>
                    <td colspan="<?php echo ($totalCol + 4); ?>" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td>
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