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
$filename = "public/report/survey_summary" . $user['User']['id'] . ".csv";
$fp = fopen($filename,"wb");
$excelContent = REPORT_SURVEY. " (Summary)\n\n";
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
            window.open("<?php echo $this->webroot; ?>public/report/survey_summary<?php echo $user['User']['id']; ?>.csv", "_blank");
        });

        $(".viewSurveyDetailList").click(function(event){
            event.preventDefault();
            var id = $(this).attr('data');
            var leftPanel  = $("#<?php echo $printArea; ?>").parent();
            var rightPanel = leftPanel.parent().find(".rightPanel");
            leftPanel.hide("slide", { direction: "left" }, 500, function() {
                rightPanel.show();
            });
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/userFeedbackView/" + id + "/<?php echo dateConvert($_POST['date_from']); ?>/<?php echo dateConvert($_POST['date_to']); ?>");
        });
    });
</script>
<div class="leftPanel">
    <div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . REPORT_SURVEY . ' (Summary)</b><br /><br />';
    $condition = "";
    if($_POST['date_from'] !='' ) {
        $msg .= REPORT_FROM.': '.$_POST['date_from'];
        $condition .= " DATE(user_feedbacks.created) >= '".dateConvert($_POST['date_from'])."'";
    }
    if($_POST['date_to'] !='' ) {
        $msg .= ' '.REPORT_TO.': '.$_POST['date_to'];
        $condition .= " AND DATE(user_feedbacks.created) <= '".dateConvert($_POST['date_to'])."'";
    }
    echo $this->element('/print/header-report',array('msg'=>$msg));
    ?>
        <div id="dynamic">
            <table id="<?php echo $tblName; ?>" class="table" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="font-size: 10px; width: 35px; text-align: center;" class="first"><?php echo TABLE_NO; ?></th>
                        <th style="width: 170px !important; font-size: 10px;"><?php echo "Vehicle ID"; ?></th>
                        <th style="width: 130px !important; font-size: 10px;"><?php echo "Driver"; ?></th>
                        <th style="width: 130px !important; font-size: 10px;"><?php echo "Office Staff"; ?></th>
                        <th style="width: 130px !important; font-size: 10px;"><?php echo "Reviewer"; ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $totalAmount = 0;
                $totalVat    = 0;
                $grandTotal  = 0;
                $index  = 0;
                $busCon = "";
                if($_POST['bus']!='') {
                    $busCon = " AND id = ".$_POST['bus'];
                } else {
                    if($_POST['bus_type']!='') {
                        $busCon = " AND id IN (SELECT id FROM buses WHERE bus_type_id = ".$_POST['bus_type']." AND is_active = 1)";
                    }
                }
                $j = 0;
                $dataRecords = array();
                $sqlBus = mysql_query("SELECT id, CONCAT_WS(' ', code,'(',name,')') AS bus_name FROM buses WHERE is_active = 1".$busCon." ORDER BY code, name;");
                while($rowBus = mysql_fetch_array($sqlBus)){
                    $driverRate  = 0;
                    $totalDrRate = 0;
                    $officeRate  = 0;
                    $totalOfRate = 0;
                    $reviewer    = 0;
                    $sqlDriverFeeback = mysql_query("SELECT COUNT(user_feedback_details.id) AS total, SUM(user_feedback_details.score) AS rate
                                                FROM user_feedbacks 
                                                INNER JOIN user_feedback_details ON user_feedback_details.user_feedback_id = user_feedbacks.id
                                                INNER JOIN question_feedbacks ON question_feedbacks.id = user_feedback_details.question_feedback_id AND question_feedbacks.type = 1
                                                WHERE ".$condition." AND user_feedbacks.bus_id = ".$rowBus['id']."
                                                GROUP BY user_feedbacks.id");
                    while($rowDriverFeedback = mysql_fetch_array($sqlDriverFeeback)){
                        $driverRate  += $rowDriverFeedback['rate'];
                        $totalDrRate += $rowDriverFeedback['total'] * 5;
                        $reviewer++;
                    }
                    $sqlOfficeFeeback = mysql_query("SELECT COUNT(user_feedback_details.id) AS total, SUM(user_feedback_details.score) AS rate
                                                    FROM user_feedbacks 
                                                    INNER JOIN user_feedback_details ON user_feedback_details.user_feedback_id = user_feedbacks.id
                                                    INNER JOIN question_feedbacks ON question_feedbacks.id = user_feedback_details.question_feedback_id AND question_feedbacks.type = 2
                                                    WHERE ".$condition." AND user_feedbacks.bus_id = ".$rowBus['id']."
                                                    GROUP BY user_feedbacks.id");
                    while($rowOfficeFeedback = mysql_fetch_array($sqlOfficeFeeback)){
                        $officeRate  += $rowOfficeFeedback['rate'];
                        $totalOfRate += $rowOfficeFeedback['total'] * 5;
                    }
                    $driverScore = 0;
                    $driverLbl   = "";
                    if($totalDrRate > 0){
                        $driverStar       = (int) (($driverRate / $totalDrRate) * 100);
                        $driverStarNum    = (int) ($driverStar / 20);
                        $driverStarRemain = $driverStar - ($driverStarNum * 20);
                        $driverStarHalf   = 0;
                        $driverScore     += $driverStar;
                        for($i = 0; $i < $driverStarNum; $i++){
                            $driverLbl .= '<i class="fa fa-star" style="color: orange;"></i>';
                        }
                        if($driverStarRemain >= 10){
                            $driverLbl .= '<i class="fa fa-star-half-o" style="color: orange;"></i>';
                            $driverStarHalf = 1;
                        }
                        for($i = 0; $i < (5 - ($driverStarNum + $driverStarHalf)); $i++){
                            $driverLbl .= '<i class="fa fa-star" style="color: #ddd;"></i>';
                        }
                    } else {
                        for($i = 0; $i < 5; $i++){
                            $driverLbl .= '<i class="fa fa-star" style="color: #ddd;"></i>';
                        }
                    }
                    $staffLbl = "";
                    if($totalOfRate > 0){
                        $officeStar       = (int) (($officeRate / $totalOfRate) * 100);
                        $officeStarNum    = (int) ($officeStar / 20);
                        $officeStarRemain = $officeStar - ($officeStarNum * 20);
                        $officeStarHalf   = 0;
                        for($i = 0; $i < $officeStarNum; $i++){
                            $staffLbl .= '<i class="fa fa-star" style="color: orange;"></i>';
                        }
                        if($officeStarRemain >= 10){
                            $staffLbl .= '<i class="fa fa-star-half-o" style="color: orange;"></i>';
                            $officeStarHalf = 1;
                        }
                        for($i = 0; $i < (5 - ($officeStarNum + $officeStarHalf)); $i++){
                            $staffLbl .= '<i class="fa fa-star" style="color: #ddd;"></i>';
                        }
                    } else {
                        for($i = 0; $i < 5; $i++){
                            $staffLbl .= '<i class="fa fa-star" style="color: #ddd;"></i>';
                        }
                    }
                    $dataRecords[$j]['bus_id']       = $rowBus['id']; 
                    $dataRecords[$j]['bus']          = $rowBus['bus_name']; 
                    $dataRecords[$j]['driver']       = $driverLbl;
                    $dataRecords[$j]['driver_score'] = $driverScore;
                    $dataRecords[$j]['staff']    = $staffLbl; 
                    $dataRecords[$j]['reviewer'] = $reviewer; 
                    $j++;
                }
                function DESC($a, $b) {
                    return strcmp($a["reviewer"], $b["reviewer"])*-1;
                }
                usort($dataRecords, "DESC");
                foreach($dataRecords AS $data){
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
                            echo '<a href="#" class="viewSurveyDetailList" data="'.$data['bus_id'].'">'.$data['bus'].'</a>'; 
                            $excelContent .= "\t" . $data['bus']; 
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px;">
                            <?php 
                            echo $data['driver'];
                            $excelContent .= "\t";
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px;">
                            <?php 
                            echo $data['staff'];
                            $excelContent .= "\t"; 
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px;">
                            <?php 
                            echo number_format($data['reviewer'], 0); 
                            $excelContent .= "\t" . number_format($data['reviewer'], 0); 
                            ?>
                        </td>
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
</div>
<div class="rightPanel"></div>
<?php
$excelContent = chr(255).chr(254).@mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
fwrite($fp,$excelContent);
fclose($fp);
?>