<?php $tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var oTableTTicket;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        oTableTTicket = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/ApiAjax/",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $(".btnViewTTicket").click(function(event){
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
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ -1 ]
            }],
            "aaSorting": [[ 0, "desc" ]]
        });
    });
</script>
<div class="leftPanel">
    <br />
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table" cellspacing="0">
            <thead>
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th style="width: 190px !important;"><?php echo TABLE_DATE; ?></th>
                    <th style="width: 190px !important;"><?php echo TABLE_CODE; ?></th>
                    <th style="width: 190px !important;"><?php echo TABLE_REFERENCE; ?></th>
                    <th style="width: 170px !important;"><?php echo TABLE_DEPARTURE_TIME; ?></th>
                    <th><?php echo GENERAL_DESCRIPTION; ?></th>
                    <th style="width: 100px !important;"><?php echo TABLE_TELEPHONE; ?></th>
                    <th style="width: 100px !important;"><?php echo GENERAL_AMOUNT; ?></th>
                    <th style="width: 100px !important;"><?php echo TABLE_MARKUP; ?></th>
                    <th style="width: 100px !important;"><?php echo GENERAL_BALANCE; ?></th>
                    <th style="width: 110px !important;"><?php echo TABLE_TYPE; ?></th>
                    <th style="width: 100px !important;"><?php echo TABLE_STATUS; ?></th>
                    <th style="width: 140px !important;"><?php echo ACTION_ACTION; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="11" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <br />
    <br />
</div>
<div class="rightPanel"></div>