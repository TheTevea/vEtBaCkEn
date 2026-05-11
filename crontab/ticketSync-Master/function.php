<?php
include("config.php");

function putUrl($url, $post){
    // CURL
    $curl  = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, 1);
    if(!empty($post)){
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 600);
    curl_setopt($curl, CURLOPT_TIMEOUT, 600);
    $result     = curl_exec($curl);
    $curl_errno = curl_errno($curl);
    $curl_error = curl_error($curl);
    curl_close ($curl);
    if ($curl_errno > 0) {
        $return['status'] = 0;
        $return['info'] = "cURL Error ($curl_errno): $curl_error\n";
    } else {
        $return['status'] = 1;
        $return['info']   = json_decode($result, true);
    }
    return $return;
}

function sendTransaction($token, $synCode, $content, $total){
    $post   = "token=".$token."&synCode=".$synCode."&contents=".$content."&total=".$total;
    $url    = SERVER_API."setting/save";
    $return = putUrl($url, $post);
    return $return;
}

function receiveSetting($token, $request, $response){
    $post   = "";
    $url    = SERVER_API."setting/get/".$token."/".$request."/".$response;
    $return = putUrl($url, $post);
    return $return;
}

function extractDir($zipfile, $path) {
  if (file_exists($zipfile)) {
    $files = array();
    $zip = new ZipArchive;
    if ($zip->open($zipfile) === TRUE) {
      for($i = 0; $i < $zip->numFiles; $i++) {
          $entry = $zip->getNameIndex($i);
          $files[] = $entry;
      }
      if ($zip->extractTo($path, $files) === TRUE) {
        return TRUE;
      } else {
        return FALSE;
      }
      $zip->close();
    } else {
        
      return FALSE;
    }
  } else {
      
    return FALSE;
  }
}

function connectDb($host, $user, $password, $db){
    $con = mysql_connect($host,$user,$password);
    mysql_select_db($db);
    mysql_query("SET character_set_client=utf8", $con);
    mysql_query("SET character_set_connection=utf8", $con);
    mysql_query("SET NAMES 'utf8'", $con);
    return $con;
}

function genRandomString() {
    $character_set_array = array();
    $character_set_array[] = array('count' => 10, 'characters' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
    $character_set_array[] = array('count' => 1, 'characters' => '0123456789');
    $temp_array = array();
    foreach ($character_set_array as $character_set) {
        for ($i = 0; $i < $character_set['count']; $i++) {
            $temp_array[] = $character_set['characters'][rand(0, strlen($character_set['characters']) - 1)];
        }
    }
    shuffle($temp_array);
    return implode('', $temp_array);
}

function generateSqlInsertSync($tableName, $files){
    $i = 0;
    $j = 0;
    $sql = "INSERT INTO ".$tableName." (";
    foreach($files AS $key => $value){
        if($i > 0){
            $sql .= ",";
        }
        $sql .= "`".$key."`";
        $i++;
    }
    $sql .= ") VALUES (";
    foreach($files AS $key => $value){
        if($j > 0){
            $sql .= ",";
        }
        if (strpos($value,"SELECT") != false || $value == 'null') {
            $sql .= $value;
        }else{
            $sql .= "'".$value."'";
        }
        $j++;
    }
    $sql .= ");";
    return $sql;
}

function generateSqlUpdateSync($tableName, $files, $conditions, $order){
    $i = 0;
    $sql = "UPDATE ".$tableName." SET ";
    foreach($files AS $key => $value){
        if($i > 0){
            $sql .= ",";
        }
        if (strpos($value,"SELECT") != false || $value == 'null') {
            $sql .= $key."=".$value;
        }else{
            $sql .= $key."="."'".$value."'";
        }
        $i++;
    }
    $sql .= " WHERE ";
    if(!empty($conditions)){
        $sql .= $conditions;
    }else{
        $sql .= "1";
    }
    if(!empty($order)){
        $sql .= " ".$order;
    }
    $sql .= ";";
    return $sql;
}

function generateSqlDeleteSync($tableName, $conditions){
    $sql = "DELETE FROM ".$tableName." WHERE ";
    if(!empty($conditions)){
        $sql .= $conditions;
    }else{
        $sql .= "1";
    }
    $sql .= ";";
    return $sql;
}

function decryptString($string){
    $convert   = '';
    $array     = array();
    $array['66'] = '0';
    $array['77'] = '1';
    $array['55'] = '2';
    $array['11'] = '3';
    $array['88'] = '4';
    $array['99'] = '5';
    $array['00'] = '6';
    $array['98'] = '7';
    $array['97'] = '8';
    $array['96'] = '9';

    // Convert Lowercase
    $array['21'] = 'a';
    $array['22'] = 'b';
    $array['23'] = 'c';
    $array['31'] = 'd';
    $array['32'] = 'e';
    $array['33'] = 'f';
    $array['41'] = 'g';
    $array['42'] = 'h';
    $array['43'] = 'i';
    $array['51'] = 'j';
    $array['52'] = 'k';
    $array['53'] = 'l';
    $array['61'] = 'm';
    $array['62'] = 'n';
    $array['63'] = 'o';
    $array['71'] = 'p';
    $array['72'] = 'q';
    $array['73'] = 'r';
    $array['74'] = 's';
    $array['81'] = 't';
    $array['82'] = 'u';
    $array['83'] = 'v';
    $array['91'] = 'w';
    $array['92'] = 'x';
    $array['93'] = 'y';
    $array['94'] = 'z';

    $array['021'] = 'A';
    $array['022'] = 'B';
    $array['023'] = 'C';
    $array['031'] = 'D';
    $array['032'] = 'E';
    $array['033'] = 'F';
    $array['041'] = 'G';
    $array['042'] = 'H';
    $array['043'] = 'I';
    $array['051'] = 'J';
    $array['052'] = 'K';
    $array['053'] = 'L';
    $array['061'] = 'M';
    $array['062'] = 'N';
    $array['063'] = 'O';
    $array['071'] = 'P';
    $array['072'] = 'Q';
    $array['073'] = 'R';
    $array['074'] = 'S';
    $array['081'] = 'T';
    $array['082'] = 'U';
    $array['083'] = 'V';
    $array['091'] = 'W';
    $array['092'] = 'X';
    $array['093'] = 'Y';
    $array['094'] = 'Z';

    // Convert Under Score
    $array['100'] = '_';
    $contents = explode("-", $string);
    foreach($contents AS $content){
        if(!empty($array[$content]) && !empty($content)){
            $convert .= $array[$content];
        }
    }
    
    return $convert;
}

function Safeb64Encode($string) {
    $data = base64_encode($string);
    $data = str_replace(array('+','/','='),array('-','_',''),$data);
    return $data;
}

function Safeb64Decode($string) {
    $data = str_replace(array('-','_'),array('+','/'),$string);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
        $data .= substr('====', $mod4);
    }
    return base64_decode($data);
}

function encode($value, $skey){ 
    if(!$value){return false;}
    $text = $value;
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $skey, $text, MCRYPT_MODE_ECB, $iv);
    return trim(Safeb64Encode($crypttext)); 
}

function decode($value, $skey){
    if(!$value){return false;}
    $crypttext = Safeb64Decode($value); 
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $skey, $crypttext, MCRYPT_MODE_ECB, $iv);
    return trim($decrypttext);
}

function sendFile($sendId, $synCode, $synContent){
    $sqlRest = mysql_query("SELECT GROUP_CONCAT(s_t) AS rest_code FROM ".SYNC_DB."s_t_ps WHERE 1;");
    $accessSent = true;
    $result = '';
    $return = array();
    if(mysql_num_rows($sqlRest)){
        $sendTo = mysql_fetch_array($sqlRest);
        // GET Request Token
        $tokenFirst = getRequestToken();
        if(!empty($tokenFirst) && $tokenFirst['status'] == 1){
            $result .= "Get Token: Success\n";
            // Send Content
            $timeStart = microtime(true);
            $json = array();
            $json['rt']  = $sendTo['rest_code'];
            $json['sys'] = $synCode;
            $json['con'] = $synContent;
            $restResult  = sendContent($tokenFirst, $json);
            $timeEnd = microtime(true);
            if(!empty($restResult) && $restResult['status'] == 1){
                // Update Send Status
                mysql_query("UPDATE ".SYNC_DB."sends SET status = 2 WHERE id = ".$sendId);
                // Insert Send History
                $sendTime = $timeEnd - $timeStart;
                mysql_query("INSERT INTO ".SYNC_DB."send_status (`send_id`, `created`, `sent_time`) VALUE (".$sendId.", '".date("Y-m-d H:i:s")."', ".$sendTime.");");
                // Update Request Code
                if($restResult['rc'] != ''){
                    mysql_query("UPDATE ".SYNC_DB."`request_codes` SET status = 3 WHERE code = '".$restResult['rc']."';");
                }
                $result .= "Send File: Success\n";
            } else {
                $accessSent = false;
                $result .= "Send File: Failed\n";
            }
        } else {
            $result .= "Get Token: ".$tokenFirst['info']."\n";
        }
    }
    $return['result']  = $result;
    $return['process'] = $accessSent;
    return $return;
}

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

?>