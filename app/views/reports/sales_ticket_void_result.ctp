<?php
include('includes/function.php');
$rnd = rand();
$oTable = "oTable" . $rnd;
$printArea = "printArea" . $rnd;
$btnPrint = "btnPrint" . $rnd;
?>
<?php $tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var <?php echo $oTable; ?>;
    $(document).ready(function(){
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        <?php echo $oTable; ?> = $("#<?php echo $tblName; ?>").dataTable({
            "aLengthMenu": [[50, 100, 500, 1000, 5000, 10000, 1000000*1000000], [50, 100, 500, 1000, 5000, 10000, "All"]],
            "iDisplayLength": 1000000*1000000,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/salesTicketVoidAjax/<?php echo str_replace("/", "|||", implode('-', $_POST)); ?>",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?>_length, #<?php echo $tblName; ?>_filter, #<?php echo $tblName; ?>_info, #<?php echo $tblName; ?>_paginate").hide();
                $("#<?php echo $tblName; ?> td").css("font-size", "11px");
                $("#<?php echo $tblName; ?> td:first-child").css("text-align", "center");
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $("#<?php echo $tblName; ?> td:nth-child(4)").css("text-align", "center");
                $("#<?php echo $tblName; ?> td:nth-child(5)").css("text-align", "center");
                $("#<?php echo $tblName; ?> td:nth-child(6)").css("text-align", "center");
                $("#<?php echo $tblName; ?> td:nth-child(7)").css("text-align", "center");
                $("#<?php echo $tblName; ?> td:nth-child(9)").css("text-align", "center");
                $("#<?php echo $tblName; ?> td:nth-child(11)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(12)").css("text-align", "center");
                $("#<?php echo $tblName; ?> td:nth-child(13)").css("text-align", "center");
                var totalPrice = 0;
                var totalPriceVoid = 0;
                $("#<?php echo $tblName; ?> tr:gt(0)").each(function(){
                    if($(this).find("td:nth-child(13)").text() != 'Void'){
                        totalPrice += replaceNum($(this).find("td:nth-child(11)").text());
                    } else {
                        totalPriceVoid += replaceNum($(this).find("td:nth-child(11)").text());
                    }
                });
                $('#<?php echo $tblName; ?> > tbody:last').append('<tr><td class="first" style="font-weight: bold; text-align: right; font-size: 11px;" colspan="10"><?php echo TABLE_TOTAL; ?> (Pending/Completed):</td><td class="formatCurrency" style="text-align: right;font-weight: bold; font-size: 11px;">' + (totalPrice) + '</td><td colspan="2"></td></tr>');
                $('#<?php echo $tblName; ?> > tbody:last').append('<tr><td class="first" style="font-weight: bold; text-align: right; font-size: 11px;" colspan="10"><?php echo TABLE_TOTAL; ?> (Void):</td><td class="formatCurrency" style="text-align: right;font-weight: bold; font-size: 11px;">' + (totalPriceVoid) + '</td><td colspan="2"></td></tr>');
                $('.formatCurrency').formatCurrency({colorize:true});
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0 ]
            }],
            "aaSorting": [[ 2, "asc" ]]
        });
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
    $msg = '<b style="font-size: 18px;">' . REPORT_SALES_TICKET_VOID . '</b><br /><br />';
    if($_POST['view_date'] == 1) {
        $msg .= 'View By Booking Date<br/>';
    } else {
        $msg .= 'View By Travel Date<br/>';
    }
    if($_POST['date_from']!='') {
        $msg .= REPORT_FROM.': '.$_POST['date_from'];
    }
    if($_POST['date_to']!='') {
        $msg .= ' '.REPORT_TO.': '.$_POST['date_to'];
    }
    echo $this->element('/print/header-report',array('msg'=>$msg));
    ?>
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table_print" style="width: 1600px;">
            <thead>
                <tr>
                    <th style="font-size: 11px; width: 35px;"><?php echo TABLE_NO; ?></th>
                    <th style="font-size: 11px;"><?php echo MENU_BRANCH; ?></th>
                    <th style="width: 130px !important; font-size: 11px;"><?php echo TABLE_TICKET_CODE; ?></th>
                    <th style="width: 80px !important; font-size: 11px;"><?php echo TABLE_BOOKING_DATE; ?></th>
                    <th style="width: 80px !important; font-size: 11px;"><?php echo TABLE_JOURNEY_DATE; ?></th>
                    <th style="width: 90px !important; font-size: 11px;"><?php echo TABLE_DEPARTURE_TIME; ?></th>
                    <th style="width: 100px !important; font-size: 11px;"><?php echo TABLE_SEAT_NUMBER; ?></th>
                    <th style="width: 220px !important; font-size: 11px;"><?php echo TABLE_DIRECTION; ?></th>
                    <th style="width: 110px !important; font-size: 11px;"><?php echo TABLE_CUSTOMER_PHONE; ?></th>
                    <th style="width: 110px !important; font-size: 11px;"><?php echo "Void By"; ?></th>
                    <th style="width: 75px !important; font-size: 11px;"><?php echo GENERAL_AMOUNT; ?></th>
                    <th style="width: 80px !important;  font-size: 11px;"><?php echo TABLE_TYPE; ?></th>
                    <th style="width: 75px !important; font-size: 11px;"><?php echo TABLE_STATUS; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="13" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
                </tr>
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