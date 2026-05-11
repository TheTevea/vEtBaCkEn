<?php
// Authentication
$this->element('check_access');
$allowAdd=checkAccess($user['User']['id'], $this->params['controller'], 'add');
$allowExport=checkAccess($user['User']['id'], $this->params['controller'], 'exportExcel');
?>
<?php $tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var oTableCoupon;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        oTableCoupon = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/ajax/",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $(".btnViewCouponGenerate").unbind('click').click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/viewGenerate/" + id);
                });
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0,-1 ]
            }]
        });
        // Filter by Coupon Type
        $("#couponFilterType").change(function(){
            var typeId = $(this).val();
            var Tablesetting = oTableCoupon.fnSettings();
            Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajaxGenerate/" + typeId;
            oCache.iCacheLower = -1;
            oTableCoupon.fnDraw(false);
        });
        $(".btnAddCoupon").click(function(event){
            event.preventDefault();
            var leftPanel=$(this).parent().parent().parent();
            var rightPanel=leftPanel.parent().find(".rightPanel");
            leftPanel.hide("slide", { direction: "left" }, 500, function() {
                rightPanel.show();
            });
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/add/");
        });
    });
</script>
<div class="leftPanel">
    <?php if($allowAdd || $allowExport){ ?>
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <?php if($allowAdd){ ?>
        <div class="buttons" style="float: left;">
            <a href="" class="positive btnAddCoupon">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_COUPON_ADD; ?>
            </a>
        </div>
        <?php } ?>
        
        <div style="float: right; width: 350px;">
            <label for="couponFilterType"><?php echo __('Coupon Type', true); ?></label> :
            <select id="couponFilterType" style="width: 250px; height: 30px; font-size: 12px;">
                <option value="all"><?php echo TABLE_ALL; ?></option>
                <?php if(!empty($couponTypes)){ foreach($couponTypes as $id => $name){ ?>
                <option value="<?php echo $id; ?>"><?php echo $name; ?></option>
                <?php } } ?>
            </select>
        </div>
        <div style="clear: both;"></div>
    </div>
    <?php } ?>
    <br />
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table" cellspacing="0">
            <thead>
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo __('Date', true); ?></th>
                    <th><?php echo __('Coupon Type', true); ?></th>
                    <th><?php echo __('Total Coupon', true); ?></th>
                    <th><?php echo __('Created', true); ?></th>
                    <th><?php echo __('Created By', true); ?></th>
                    <th><?php echo ACTION_ACTION; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="7" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <br />
    <br />
</div>
<div class="rightPanel"></div>