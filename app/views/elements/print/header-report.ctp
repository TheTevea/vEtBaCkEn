<table style="width: 100%;">
    <tr>
        <td style="text-align: center;">
            <img alt="" src="<?php echo $this->webroot; ?>img/<?php echo !empty($logo) ? $logo : 'logo.png'; ?>" style="height: 90px;" />
        </td>
    </tr>
    <tr>
        <td style="vertical-align: top; text-align: center; width: 34%;">
            <div style="font-size: 12px; font-weight: bold; text-transform: uppercase;"><?php echo !empty($msg) ? $msg : ''; ?></div>
        </td>
    </tr>
</table>