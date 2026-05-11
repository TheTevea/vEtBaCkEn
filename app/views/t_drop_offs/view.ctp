<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackTDropOff").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableTDropOff.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTDropOff">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_VIEW_DROP_OFF); ?></legend>
    <table width="100%" class="info">
        <td style="width: 100%; vertical-align: top;">
            <table style="width: 100%;">
            <tr>
                    <th style="width:10%;"><?php __(TABLE_NAME); ?> (Khmer)</th>
                    <td style="width:1%;">:</td>
                    <td>
                        <?php echo $this->data['TDropOff']['name_kh']; ?>
                    </td>
                </tr>
                <tr>
                    <th style="width:10%;"><?php __(TABLE_NAME); ?> (English)</th>
                    <td style="width:1%;">:</td>
                    <td>
                        <?php echo $this->data['TDropOff']['name']; ?>
                    </td>
                </tr>
                <tr>
                    <th style="width:10%;"><?php __(TABLE_NAME); ?> (Chinese)</th>
                    <td style="width:1%;">:</td>
                    <td>
                        <?php echo $this->data['TDropOff']['name_ch']; ?>
                    </td>
                </tr>
                <tr>
                    <th><?php __(TABLE_CONTACT_NAME); ?></th>
                    <td>:</td>
                    <td>
                        <?php echo $this->data['TDropOff']['contact']; ?>
                    </td>
                </tr>
                <tr>
                    <th><?php __(TABLE_TELEPHONE); ?></th>
                    <td>:</td>
                    <td>
                        <?php echo $this->data['TDropOff']['telephone']; ?>
                    </td>
                </tr>
                <tr>
                    <th><?php __(TABLE_ADDRESS); ?> (Khmer)</th>
                    <td>:</td>
                    <td>
                        <?php echo nl2br($this->data['TDropOff']['address_kh']); ?>
                    </td>
                </tr>
                <tr>
                    <th><?php __(TABLE_ADDRESS); ?> (English)</th>
                    <td>:</td>
                    <td>
                        <?php echo nl2br($this->data['TDropOff']['address']); ?>
                    </td>
                </tr>
                <tr>
                    <th><?php __(TABLE_ADDRESS); ?> (Chinese)</th>
                    <td>:</td>
                    <td>
                        <?php echo nl2br($this->data['TDropOff']['address_ch']); ?>
                    </td>
                </tr>
                <tr>
                    <th><?php __(TABLE_LONG); ?></th>
                    <td>:</td>
                    <td>
                        <?php echo $this->data['TDropOff']['longs']; ?>
                    </td>
                </tr>
                <tr>
                    <th><?php __(TABLE_LAT); ?></th>
                    <td>:</td>
                    <td>
                        <?php echo $this->data['TDropOff']['lats']; ?>
                    </td>
                </tr>
            </table>
        </td>
    </table>
 </fieldset>