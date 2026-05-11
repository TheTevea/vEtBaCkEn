<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackAutoGenerateCode").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableAutoGenerateCode.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackAutoGenerateCode">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<table width="100%" cellpadding="10">
    <tr>
        <th style="width: 10%; font-size: 12px;"><?php echo MENU_TRANSPORTATION_TYPE; ?> :</th>
        <td style="font-size: 12px;"><?php echo $this->data['TTransportationType']['name']; ?></td>
    </tr>
    <tr>
        <th style="width: 10%; font-size: 12px;"><?php echo TABLE_NAME; ?> :</th>
        <td style="font-size: 12px;"><?php echo $this->data['AutoGenerateCode']['name']; ?></td>
    </tr>
    <tr>
        <th style="width: 10%; font-size: 12px;"><?php echo "Number of Seat"; ?> :</th>
        <td style="font-size: 12px;"><?php echo $this->data['AutoGenerateCode']['number_of_seat']; ?></td>
    </tr>
    <tr>
        <th style="width: 10%; font-size: 12px;"><?php echo "Apply Rent"; ?> :</th>
        <td style="font-size: 12px;">
            <?php 
            if($this->data['AutoGenerateCode']['apply_rent'] == 1){
                echo ACTION_YES;
            } else {
                echo ACTION_NO;
            }
            ?>
        </td>
    </tr>
    <tr>
        <th style="width: 10%; font-size: 12px;"><?php echo GENERAL_DESCRIPTION; ?> :</th>
        <td style="font-size: 12px;">
            <?php echo nl2br($this->data['AutoGenerateCode']['description']); ?>
        </td>
    </tr>
</table>