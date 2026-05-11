<?php
sleep(60);
$con = mysql_connect('localhost','root','@BVS3pm412');
if(@$con){
    mysql_select_db('ticket');
    mysql_query("UPDATE date_settings SET is_set = 1 WHERE id = 1");
    setDate();
}

function setDate(){
    $break = false;
    while($break == false) {
        sleep(2);
        $sqlCheckDate = mysql_query("SELECT * FROM date_settings WHERE id = 1");
        if(mysql_num_rows($sqlCheckDate)){
            $rowDate = mysql_fetch_array($sqlCheckDate);
            if($rowDate['is_set'] == 0){
                $date = $rowDate['date'];
                shell_exec("sudo date -s '".$date."'");
                $break = true;
            }
        } else {
            $break = true;
        }
    } 
}