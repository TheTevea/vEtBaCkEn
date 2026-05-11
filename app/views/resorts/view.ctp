<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackResort").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableResort.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackResort">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_RESORT_INFO); ?></legend>
    <table width="100%" cellpadding="5">
        <tr>
            <td colspan="2">
                <?php
                $img = "";
                if(!empty($this->data['Resort']['photo'])){
                    $img = $this->data['Resort']['photo_path'].$this->data['Resort']['photo'];
                }
                ?>
                <img src="<?php echo $img; ?>" style="width: 150px;" />
            </td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __(TABLE_NAME); ?></th>
            <td style="font-size: 12px;"><?php echo $this->data['Resort']['name']; ?></td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __("Price"); ?></th>
            <td style="font-size: 12px;"><?php echo $this->data['Resort']['price']; ?></td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __("URL Link"); ?></th>
            <td style="font-size: 12px;"><?php echo $this->data['Resort']['link']; ?></td>
        </tr>
    </table>
</fieldset>
<br/>