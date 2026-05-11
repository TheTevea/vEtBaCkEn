<?php
mysql_connect("localhost", "root", "");
mysql_select_db("");

if(!empty($_POST['act'])){
    if($_POST['act'] == '1'){
        $sqlTime = mysql_query("SELECT * FROM date_settings WHERE id = 1");
        $rowTime = mysql_fetch_array($sqlTime);
        echo $rowTime['is_set'];
        exit;
    } else if ($_POST['act'] == '2'){
        if(!empty($_POST['date']) && $_POST['date'] != '0000-00-00 00:00:00'){
            mysql_query("UPDATE date_settings SET date = '".$_POST['date']."', is_set = 0 WHERE id = 1;");
            echo 1;
            exit;
        } else {
            echo 0;
            exit;
        }
    } else {
        echo 0;
        exit;
    }
} else {
    echo 0;
    exit;
}
