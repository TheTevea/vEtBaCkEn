<?php
include('includes/function.php');
$rnd       = rand();
$printArea = "printArea" . $rnd;
$btnPrint  = "btnPrint" . $rnd;
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
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . REPORT_TRAVEL_PACKAGE_ORDER . '</b><br /><br />';
    if($_POST['date_from']!='') {
        $msg .= REPORT_FROM.': '.$_POST['date_from'];
    }
    if($_POST['date_to']!='') {
        $msg .= ' '.REPORT_TO.': '.$_POST['date_to'];
    }
    $condition = "travel_package_orders.status = 2 AND travel_package_orders.type = 1";
    if($_POST['date_from'] !='' ) {
        $condition .= " AND travel_package_orders.package_date >= '".dateConvert($_POST['date_from'])."'";
    }
    if($_POST['date_to'] !='' ) {
        $condition .= " AND travel_package_orders.package_date <= '".dateConvert($_POST['date_to'])."'";
    }
    echo $this->element('/print/header-report',array('msg'=>$msg));
    ?>
    <div id="dynamic">
        <table class="table" style="width: 100%;">
            <thead>
                <tr>
                    <th class="first" style="font-size: 11px; width: 35px;"><?php echo TABLE_NO; ?></th>
                    <th style="width: 130px !important; font-size: 11px;"><?php echo "Date"; ?></th>
                    <th style="width: 130px !important; font-size: 11px;"><?php echo "Package Code"; ?></th>
                    <th style="width: 130px !important; font-size: 11px;"><?php echo "Transaction Code"; ?></th>
                    <th style="width: 150px !important; font-size: 11px;"><?php echo "name"; ?></th>
                    <th style="width: 90px !important; font-size: 11px;"><?php echo "Sex"; ?></th>
                    <th style="width: 100px !important; font-size: 11px;"><?php echo "Telephone"; ?></th>
                    <th style="width: 130px !important; font-size: 11px;"><?php echo "Email"; ?></th>
                    <th style="width: 110px !important; font-size: 11px;"><?php echo "dob"; ?></th>
                    <th style="width: 110px !important; font-size: 11px;"><?php echo "Nationality"; ?></th>
                    <th style="width: 150px !important;  font-size: 11px;"><?php echo "Price"; ?> ($)</th>
                    <th style="width: 130px !important; font-size: 11px;"><?php echo "Expiry Date"; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalPrice = 0;
                $sqlPackageOrder = mysql_query("SELECT travel_package_orders.*, travel_packages.description AS package_name, nationalities.name AS nationality_name 
                                                FROM travel_package_orders 
                                                INNER JOIN travel_packages ON travel_packages.id = travel_package_orders.travel_package_id
                                                LEFT JOIN nationalities ON nationalities.id = travel_package_orders.nationality
                                                WHERE ".$condition." ORDER BY travel_package_orders.package_date ASC");
                if(mysql_num_rows($sqlPackageOrder)){
                    $index = 0;
                    while($rowPackageOrder = mysql_fetch_array($sqlPackageOrder)){
                ?>
                <tr>
                    <td class="first" style="font-size: 11px; width: 35px;"><?php echo ++$index; ?></td>
                    <td style="font-size: 11px;"><?php echo dateShort($rowPackageOrder['package_date']); ?></td>
                    <td style="font-size: 11px;"><?php echo $rowPackageOrder['package_code']; ?></td>
                    <td style="font-size: 11px;"><?php echo $rowPackageOrder['code']; ?></td>
                    <td style="font-size: 11px;"><?php echo $rowPackageOrder['name']; ?></td>
                    <td style="font-size: 11px;">
                        <?php 
                        if($rowPackageOrder['sex'] == 1){
                            echo "Male";
                        } else {
                            echo "Female";
                        }
                        ?>
                    </td>
                    <td style="font-size: 11px;"><?php echo $rowPackageOrder['telephone']; ?></td>
                    <td style="font-size: 11px;"><?php echo $rowPackageOrder['email']; ?></td>
                    <td style="font-size: 11px;">
                        <?php 
                        if(!empty($rowPackageOrder['dob'])){
                            echo dateShort($rowPackageOrder['dob']);
                        }
                        ?>
                    </td>
                    <td style="font-size: 11px;"><?php echo $rowPackageOrder['nationality_name']; ?></td>
                    <td style="font-size: 11px;"><?php echo number_format($rowPackageOrder['package_price'], 2); ?></td>
                    <td style="font-size: 11px;"><?php echo dateShort($rowPackageOrder['package_expired']); ?></td>
                </tr>
                <?php
                        $totalPrice += $rowPackageOrder['package_price'];
                    }
                ?>
                <tr>
                    <td class="first" colspan="10" style="font-size: 11px; text-align: right;">Total</td>
                    <td style="font-size: 11px;"><?php echo number_format($totalPrice, 2); ?></td>
                    <td></td>
                </tr>
                <?php
                } else {
                ?>
                <tr>
                    <td colspan="12" class="dataTables_empty first"><?php echo "No Records"; ?></td>
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