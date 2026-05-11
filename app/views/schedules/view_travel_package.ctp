
<?php
$sqlTravelPackage = mysql_query("SELECT * FROM travel_package_orders WHERE id = ".$id);
if(mysql_num_rows($sqlTravelPackage)){
    $rowTravelPackage = mysql_fetch_array($sqlTravelPackage);
?>
<table cellpadding="5" cellspacing="0" style="width: 300px;">
    <tr>
        <td colspan="2">
            <img src="<?php echo $rowTravelPackage['photo_path'].$rowTravelPackage['photo']; ?>" style="width: 200px;" />
        </td>
    </tr>
    <tr>
        <td style="width: 90px;"><?php echo "Code"; ?> :</td>
        <td style="font-size: 14px;">
            <?php echo $rowTravelPackage['code']; ?>
        </td>
    </tr>
    <tr>
        <td style="width: 90px;"><?php echo "Name"; ?> :</td>
        <td style="font-size: 14px;">
            <?php echo $rowTravelPackage['name']; ?>
        </td>
    </tr>
    <tr>
        <td style="width: 90px;"><?php echo "Sex"; ?> :</td>
        <td style="font-size: 14px;">
            <?php 
            if($rowTravelPackage['sex'] == 1){
                echo "Male";
            } else {
                echo "Female";
            }
            ?>
        </td>
    </tr>
    <tr>
        <td style="width: 90px;"><?php echo "Telephone"; ?> :</td>
        <td style="font-size: 14px;">
            <?php echo $rowTravelPackage['telephone']; ?>
        </td>
    </tr>
</table>
<?php
}
?>
