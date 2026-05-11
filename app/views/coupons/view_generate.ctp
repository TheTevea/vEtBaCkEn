<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackCoupon").unbind('click').click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            if (typeof oTableCoupon !== 'undefined') { oTableCoupon.fnDraw(false); }
            var rightPanel=$(this).closest('.rightPanel');
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        $(".btnViewCoupon").unbind('click').click(function(event){
            event.preventDefault();
            var id = $(this).attr('rel');
            var leftPanel = $(this).closest('.leftCouponPanel');
            var rightPanel = leftPanel.parent().find(".rightCouponPanel");
            leftPanel.hide("slide", { direction: "left" }, 500, function() {
                rightPanel.show();
            });
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/" + id);
        });
        $(".btnDeleteCoupon").unbind('click').click(function(event){
            event.preventDefault();
            var id = $(this).attr('rel');
            var name = $(this).attr('name');
            $("#dialog").dialog('option', 'title', '<?php echo DIALOG_CONFIRMATION; ?>');
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_DELETE; ?> <b>' + name + '</b>?</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_CONFIRMATION; ?>',
                resizable: false,
                modal: true,
                width: 'auto',
                height: 'auto',
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show();
                },
                buttons: {
                    '<?php echo ACTION_DELETE; ?>': function() {
                        $.ajax({
                            type: "GET",
                            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/delete/" + id,
                            data: "",
                            beforeSend: function(){
                                $("#dialog").dialog("close");
                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                            },
                            success: function(result){
                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                // alert message
                                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>'){
                                    createSysAct('Coupon', 'Delete', 2, result);
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                }else {
                                    createSysAct('Coupon', 'Delete', 1, '');
                                    // alert message
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                                }
                                $("#dialog").dialog({
                                    title: '<?php echo DIALOG_INFORMATION; ?>',
                                    resizable: false,
                                    modal: true,
                                    width: 'auto',
                                    height: 'auto',
                                    buttons: {
                                        '<?php echo ACTION_CLOSE; ?>': function() {
                                            $(this).dialog("close");
                                            // reload current batch view
                                            var rightPanel=$('.rightPanel');
                                            rightPanel.html("<?php echo ACTION_LOADING; ?>");
                                            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/viewGenerate/<?php echo intval($generate['CouponGenerate']['id']); ?>");
                                        }
                                    }
                                });
                            }
                        });
                    },
                    '<?php echo ACTION_CANCEL; ?>': function() {
                        $(this).dialog("close");
                    }
                }
            });
        });

        // Export to Excel for this generate batch
        $(".btnExportCoupon").unbind('click').click(function(){
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/exportExcel",
                data: {action: 'export', coupon_generate_id: <?php echo intval($generate['CouponGenerate']['id']); ?>},
                beforeSend: function(){
                    $(".btnExportCoupon").attr('disabled','disabled');
                    $(".btnExportCoupon").find('img').attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                },
                success: function(){
                    $(".btnExportCoupon").removeAttr('disabled');
                    $(".btnExportCoupon").find('img').attr("src", "<?php echo $this->webroot; ?>img/button/csv.png");
                    window.open("<?php echo $this->webroot; ?>public/report/coupon_export.csv", "_blank");
                }
            });
        });
    });
</script>
<div class="leftCouponPanel">
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <div class="buttons">
            <a href="" class="positive btnBackCoupon">
                <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
                <?php echo ACTION_BACK; ?>
            </a>
            <button type="button" class="positive btnExportCoupon" style="margin-left: 10px;">
                <img src="<?php echo $this->webroot; ?>img/button/csv.png" alt=""/>
                <?php echo ACTION_EXPORT_TO_EXCEL; ?>
            </button>
        </div>
        <div style="clear: both;"></div>
    </div>
    <br />
    <fieldset>
        <legend><?php echo __('Coupon Generate Info', true); ?></legend>
        <table width="100%" class="info">
            <tr>
                <th><?php echo __('Date', true); ?></th>
                <td><?php echo $generate['CouponGenerate']['date']; ?></td>
            </tr>
            <tr>
                <th><?php echo __('Coupon Type', true); ?></th>
                <td>
                    <?php 
                        $typeName = '';
                        if(!empty($generate['CouponGenerate']['coupon_type_id'])){
                            $sqlType = mysql_query("SELECT name FROM coupon_types WHERE id = ".intval($generate['CouponGenerate']['coupon_type_id'])." LIMIT 1");
                            if($sqlType && mysql_num_rows($sqlType)){
                                $rowType = mysql_fetch_array($sqlType);
                                $typeName = $rowType['name'];
                            }
                        }
                        echo $typeName;
                    ?>
                </td>
            </tr>
            <tr>
                <th><?php echo __('Total Coupon', true); ?></th>
                <td><?php echo $generate['CouponGenerate']['total_coupon']; ?></td>
            </tr>
            <tr>
                <th><?php echo __('Created', true); ?></th>
                <td><?php echo $generate['CouponGenerate']['created']; ?></td>
            </tr>
            <tr>
                <th><?php echo __('Created By', true); ?></th>
                <td>
                    <?php 
                        $createdBy = '';
                        if(!empty($generate['CouponGenerate']['created_by'])){
                            $sqlUser = mysql_query("SELECT username FROM users WHERE id = ".intval($generate['CouponGenerate']['created_by'])." LIMIT 1");
                            if($sqlUser && mysql_num_rows($sqlUser)){
                                $rowUser = mysql_fetch_array($sqlUser);
                                $createdBy = $rowUser['username'];
                            }
                        }
                        echo $createdBy;
                    ?>
                </td>
            </tr>
            <tr>
                <th><?php echo __('Status', true); ?></th>
                <td><?php echo $generate['CouponGenerate']['status']; ?></td>
            </tr>
        </table>
    </fieldset>
    <br />
    <fieldset>
        <legend><?php echo __('Coupons', true); ?></legend>
        <table width="100%" class="info">
            <thead>
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_CODE; ?></th>
                    <th><?php echo REPORT_FROM; ?></th>
                    <th><?php echo REPORT_TO; ?></th>
                    <th><?php echo GENERAL_AMOUNT; ?></th>
                    <th><?php echo TABLE_TIME_USE; ?></th>
                    <th><?php echo ACTION_ACTION; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($coupons)) { $i=1; foreach ($coupons as $c) { ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo $c['Coupon']['code']; ?></td>
                    <td><?php echo $c['Coupon']['start']; ?></td>
                    <td><?php echo $c['Coupon']['end']; ?></td>
                    <td><?php echo number_format($c['Coupon']['amount'], 2); ?></td>
                    <td><?php echo $c['Coupon']['total_time_use']; ?></td>
                    <td>
                        <a href="" class="btnViewCoupon" rel="<?php echo $c['Coupon']['id']; ?>" name="<?php echo $c['Coupon']['code']; ?>"><img alt="View" onmouseover="Tip('<?php echo ACTION_VIEW; ?>')" src="<?php echo $this->webroot; ?>img/button/view.png" /></a>
                        <a href="" class="btnDeleteCoupon" rel="<?php echo $c['Coupon']['id']; ?>" name="<?php echo $c['Coupon']['code']; ?>"><img alt="Delete" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot; ?>img/button/delete.png" /></a>
                    </td>
                </tr>
                <?php } } else { ?>
                <tr><td colspan="7" class="dataTables_empty"><?php echo GENERAL_NO_RECORD; ?></td></tr>
                <?php } ?>
            </tbody>
        </table>
    </fieldset>
</div>
<div class="rightCouponPanel"></div>
