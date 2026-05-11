<?php

class WingApiCheckout
{

    public static function requestSdkToken($url, $port, $method, $header, $requestBody, $packagId)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_PORT => $port,
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_REFERER => base64_encode($packagId),
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $requestBody,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => $header,
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            $error = array(
                'errorCode' => 'ERROR',
                'errorText' => "cURL Error #:" . $err
            );
            return json_encode($error);
        } else {
            return $response;
        }
    }

    public static function requestSdkConfirm($url, $token)
    {
        return $url . "?token=" . base64_encode($token);
        //return header('Location: ' . $url . "?token=" . base64_encode($token));
    }

    public static function genPayload($dataArray, $passworrd)
    {
        $payloadRaw = array(
            "amount" => $dataArray['amount'],
            "bill_till_rbtn" => "0",
            "bill_till_number" => $dataArray['bill_till_number'],
            "order_reference_no" => $dataArray['order_reference_no'],
            "return_url" => $dataArray['return_url'],
            "cancel_url" => $dataArray['cancel_url'],
            "rand_str" => $dataArray['rand_str'],
            "timestamp" => $dataArray['timestamp'],
            "wing_account" => "",
            "integration_type" => $dataArray['integration_type'],
            "is_wingapp_installed" => "1",
            "merchant_name" => ""
        );
        return self::encryptionPayload(json_encode($payloadRaw), $passworrd);
    }

    private function encryptionPayload($data, $passworrd)
    {
        $iv = substr($passworrd, 0, 16);
        return base64_encode(openssl_encrypt($data, "AES-256-CBC", $passworrd, OPENSSL_RAW_DATA, $iv));
    }

    public static function genHash($dataArray, $username, $apiKey, $password)
    {
        // $str = $dataArray['username'] . "#" . $dataArray['rest_api_key'] . "#" . $dataArray['bill_till_number'] . "#" . $dataArray['amount'] . "#" . $dataArray['order_reference_no'] . "#" . $dataArray['rand_str'] . "#" . $dataArray['timestamp'];
        // return strtoupper(hash("sha256", base64_encode(openssl_encrypt(base64_encode($str), 'AES-256-CBC', $password, OPENSSL_RAW_DATA, $dataArray['rand_str']))));

        // $dataHash   = $dataArray['username'] . "#" . $dataArray['rest_api_key'] . "#" . $dataArray['bill_till_number'] . "#" . $dataArray['amount'] . "#" . $dataArray['order_reference_no'] . "#" . $dataArray['rand_str'] . "#" . $dataArray['timestamp'];
        $billNum  = $dataArray['bill_till_number'];
        $amount   = $dataArray['amount'];
        $order_reference = $dataArray['order_reference_no'];
        $rand_str = $dataArray['rand_str'];
        $currenttimestamp = $dataArray['timestamp'];
        $dataHash   = "$username#$apiKey#$billNum#$amount#$order_reference#$rand_str#$currenttimestamp";
        $base64Hash = base64_encode($dataHash);
        $ase256Hash = openssl_encrypt($base64Hash, 'AES-256-CBC', $password, OPENSSL_RAW_DATA, $rand_str);
        $base64Hash = base64_encode($ase256Hash);
        $hash = strtoupper(hash('sha256', $base64Hash));
        return $hash;
    }

    public static function genTimeStamp()
    {
        date_default_timezone_set("UTC");
        $currenttimestamp = strval(time());
        return $currenttimestamp;
    }

    public static function generateRandomString($length)
    {
        return substr(str_shuffle(str_repeat($x = '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef', ceil($length / strlen($x)))), 1, $length);
    }

}