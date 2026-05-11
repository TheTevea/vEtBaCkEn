<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackOfflineServer").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableOfflineServer.fnDraw(false);
            var rightPanel = $(this).parent().parent().parent();
            var leftPanel  = rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackOfflineServer">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_PROJECT_SERVER_INFO); ?></legend>
    <table width="100%" class="info">
        <td style="width: 50%; vertical-align: top;">
            <table style="width: 100%;">
                <tr>
                    <th style="width:20%;"><?php __(MENU_PROJECT); ?></th>
                    <td style="width:1%;">:</td>
                    <td>
                        <?php echo $this->data['OfflineProject']['code']." - ".$this->data['OfflineProject']['name']; ?>
                    </td>
                </tr>
                <tr>
                    <th style="width:20%;"><?php __(TABLE_CODE); ?></th>
                    <td style="width:1%;">:</td>
                    <td>
                        <?php echo $this->data['OfflineServer']['code']; ?>
                    </td>
                </tr>
                <tr>
                    <th style="width:20%;"><?php __(TABLE_NAME); ?></th>
                    <td style="width:1%;">:</td>
                    <td>
                        <?php echo $this->data['OfflineServer']['name']; ?>
                    </td>
                </tr>
                <tr>
                    <th style="width:20%;"><?php __("Rest Code"); ?></th>
                    <td style="width:1%;">:</td>
                    <td>
                        <?php echo $this->data['OfflineServer']['s_t']; ?>
                    </td>
                </tr>
                <tr>
                    <th style="width:20%;"><?php __("Security Code"); ?></th>
                    <td style="width:1%;">:</td>
                    <td>
                        <?php echo $this->data['OfflineServer']['sct_c']; ?>
                    </td>
                </tr>
            </table>
        </td>
    </table>
 </fieldset>