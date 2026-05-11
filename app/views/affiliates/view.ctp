<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackAffiliate").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableAffiliate.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackAffiliate">
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
                        <?php echo $this->data['Affiliate']['name']; ?>
                    </td>
                </tr>
                <tr>
                    <th style="width:20%;"><?php __(TABLE_TELEPHONE); ?></th>
                    <td style="width:1%;">:</td>
                    <td>
                        <?php echo $this->data['Affiliate']['telephone']; ?>
                    </td>
                </tr>
                <tr>
                    <th style="width:20%;"><?php __(TABLE_WEBSITE); ?></th>
                    <td style="width:1%;">:</td>
                    <td>
                        <?php echo $this->data['Affiliate']['website_url']; ?>
                    </td>
                </tr>
                <tr>
                    <th style="width:20%;"><?php __(TABLE_COMMISSION); ?></th>
                    <td style="width:1%;">:</td>
                    <td>
                        <?php echo $this->data['Affiliate']['commission']; ?>
                    </td>
                </tr>
                <tr>
                    <th style="width:20%;"><?php __("Affiliate ID"); ?></th>
                    <td style="width:1%;">:</td>
                    <td>
                        <?php echo $this->data['Affiliate']['affiliate_id']; ?>
                    </td>
                </tr>
                <tr>
                    <th style="width:20%;"><?php __(GENERAL_DESCRIPTION); ?></th>
                    <td style="width:1%;">:</td>
                    <td>
                        <?php echo nl2br($this->data['Affiliate']['description']); ?>
                    </td>
                </tr>
            </table>
        </td>
    </table>
 </fieldset>