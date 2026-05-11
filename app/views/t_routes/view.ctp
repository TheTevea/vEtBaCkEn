<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackTRoute").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableTRoute.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTRoute">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_ROUTE_INFO); ?></legend>
    <table width="100%" class="info">
        <td style="width: 100%; vertical-align: top;">
            <table style="width: 100%;">
                <tr>
                    <th style="width:20%;"><?php __(TABLE_NAME); ?></th>
                    <td style="width:1%;">:</td>
                    <td>
                        <?php echo $this->data['TRoute']['name']; ?>
                    </td>
                </tr>
                <tr>
                    <th style="width:20%;"><?php __(GENERAL_DESCRIPTION); ?></th>
                    <td style="width:1%;">:</td>
                    <td>
                        <?php echo nl2br($this->data['TRoute']['description']); ?>
                    </td>
                </tr>
            </table>
        </td>
    </table>
 </fieldset>