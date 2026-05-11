<?php

class WingApiCheckout
{

    public static function requestSdkToken($url, $port, $method, $header, $requestBody)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_PORT => $port,
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_REFERER => base64_encode('http://' . $_SERVER['HTTP_HOST']),
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
            "bill_till_rbtn" => $dataArray['bill_till_rbtn'],
            "bill_till_number" => $dataArray['bill_till_number'],
            "order_reference_no" => $dataArray['order_reference_no'],
            "return_url" => $dataArray['return_url'],
            "cancel_url" => $dataArray['cancel_url'],
            "rand_str" => $dataArray['rand_str'],
            "timestamp" => $dataArray['timestamp'],
            "wing_account" => $dataArray['wing_account'],
            "merchant_name" => $dataArray['merchant_name'],
            "is_inquiry" => $dataArray['is_inquiry']
        );
        return self::encryptionPayload(json_encode($payloadRaw), $passworrd);
    }

    private function encryptionPayload($data, $passworrd)
    {
        $iv = substr($passworrd, 0, 16);
        return base64_encode(openssl_encrypt($data, "AES-256-CBC", $passworrd, OPENSSL_RAW_DATA, $iv));
    }

    public static function genHash($dataArray, $password)
    {
        $str = $dataArray['username'] . "#" . $dataArray['rest_api_key'] . "#" . $dataArray['bill_till_number'] . "#" . $dataArray['amount'] . "#" . $dataArray['order_reference_no'] . "#" . $dataArray['rand_str'] . "#" . $dataArray['timestamp'];
        return strtoupper(hash("sha256", base64_encode(openssl_encrypt(base64_encode($str), 'AES-256-CBC', $password, OPENSSL_RAW_DATA, $dataArray['rand_str']))));
    }

    public static function genTimeStamp()
    {
        return strtotime("now") . "";
    }

    public static function generateRandomString($length)
    {
        return substr(str_shuffle(str_repeat($x = '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef', ceil($length / strlen($x)))), 1, $length);
    }

}