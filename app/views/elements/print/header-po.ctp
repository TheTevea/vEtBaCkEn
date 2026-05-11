<?php
$img = '';
if(!empty($logo)){
    $img = $logo;
}
?>
<table style="width: 100%;">
    <tr>
        <td style="vertical-align: top; text-align: left; width: 33%;">
            <table cellpadding="0" cellspacing="0">
                <tr>
                    <td style="width: 30%; vertical-align: top; padding-top: 8px;">
                        <img alt="" src="<?php echo $this->webroot; ?>public/company_photo/<?php echo $img; ?>" style="width: 140px;" />
                    </td>
                </tr>
            </table>
        </td>
        <td style="vertical-align: top; text-align: center; width: 40%;">
            <table cellpadding="0" cellspacing="0" style="width: 100%;">
                <tr>
                    <td style="vertical-align: top; text-align: center;">
                        <?php
                        if(!empty($title)){
                        ?>
                        <div style="font-size: 18px; font-weight: bold; text-align: center;">
                            <?php
                            echo $title;
                            ?>
                        </div>
                        <?php
                        }
                        if(!empty($address)){
                        ?>
                        <div style="font-size: 12px; text-align: center;">
                            <?php
                            echo nl2br($address);
                            ?>
                        </div>
                        <?php
                        }
                        if(!empty($telephone)){
                        ?>
                        <div style="font-size: 12px; text-align: center;">
                            Tel: <?php echo $telephone; ?>
                        </div>
                        <?php
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </td>
        <td style="vertical-align: top; text-align: right; white-space: nowrap; font-size: 30px; font-weight: bold;">
            <?php echo !empty($msg) ? $msg : ''; ?>
        </td>
    </tr>
</table>