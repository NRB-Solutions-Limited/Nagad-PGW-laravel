<?php

namespace Nrbsolution\nagad_payment_gateway\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NagadPaymentGatewayController extends Controller
{

    function baseUrl()
    {
        if (config("nagad_payment_gateway.sandbox") == true) {
            $Url = 'http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0/api/dfs/';
        } else {
            $Url = 'https://api.mynagad.com/api/dfs/';
        }
        return $Url;
    }
    function generateRandomString($length = 40)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function EncryptDataWithPublicKey($data)
    {
        $pgPublicKey = config("nagad_payment_gateway.public_key");
        $public_key = "-----BEGIN PUBLIC KEY-----\n" . $pgPublicKey . "\n-----END PUBLIC KEY-----";

        $key_resource = openssl_get_publickey($public_key);
        openssl_public_encrypt($data, $cryptText, $key_resource);
        return base64_encode($cryptText);
    }



    function SignatureGenerate($data)
    {
        $merchantPrivateKey = config("nagad_payment_gateway.private_key");
        $private_key = "-----BEGIN RSA PRIVATE KEY-----\n" . $merchantPrivateKey . "\n-----END RSA PRIVATE KEY-----";

        openssl_sign($data, $signature, $private_key, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }



    function HttpPostMethod($PostURL, $PostData)
    {
        $url = curl_init($PostURL);
        $postToken = json_encode($PostData);
        $header = array(
            'Content-Type:application/json',
            'X-KM-Api-Version:v-0.2.0',
            'X-KM-IP-V4:' . $this->get_client_ip(),
            'X-KM-Client-Type:PC_WEB'
        );

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $postToken);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($url, CURLOPT_HEADER, 1);

        $resultData = curl_exec($url);
        $ResultArray = json_decode($resultData, true);
        $header_size = curl_getinfo($url, CURLINFO_HEADER_SIZE);
        curl_close($url);
        // $headers = substr($resultData, 0, $header_size);
        // $body = substr($resultData, $header_size);
        // print_r($body);
        // print_r($headers);
        // $ResultArray = $this->callback($url);

        return $ResultArray;
    }

    function get_client_ip()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if (isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    function DecryptDataWithPrivateKey($cryptText)
    {
        $merchantPrivateKey =config("nagad_payment_gateway.private_key");
        $private_key = "-----BEGIN RSA PRIVATE KEY-----\n" . $merchantPrivateKey . "\n-----END RSA PRIVATE KEY-----";
        openssl_private_decrypt(base64_decode($cryptText), $plain_text, $private_key);
        return $plain_text;
    }



    public function NagadPay(Request $request, $reference_id, $amount)
    {
        //dd($carbook);

        date_default_timezone_set('Asia/Dhaka');

        $MerchantID = config("nagad_payment_gateway.merchant_id");
        $DateTime = Date('YmdHis');
        // $amount = 5;

        $OrderId = config("nagad_payment_gateway.prefix") . strtotime("now") . rand(1000, 10000);
        $random = $this->generateRandomString();
        $PostURL = $this->baseUrl() . "check-out/initialize/" . config("nagad_payment_gateway.merchant_id") . "/{$OrderId}";

        $_SESSION['orderId'] = $OrderId;
        // $id = Crypt::encrypt($carbook->id);
        $merchantCallbackURL = route('nagad.callback');

        $SensitiveData = array(
            'merchantId' => $MerchantID,
            'datetime' => $DateTime,
            'orderId' => $OrderId,
            'challenge' => $random,
        );

        $PostData = array(
            'accountNumber' => config("nagad_payment_gateway.merchant_number"), //Replace with Merchant Number (not mandatory)
            'dateTime' => $DateTime,
            'sensitiveData' => $this->EncryptDataWithPublicKey(json_encode($SensitiveData)),
            'signature' => $this->SignatureGenerate(json_encode($SensitiveData))
        );
        $Result_Data = $this->HttpPostMethod($PostURL, $PostData);


        if (isset($Result_Data['sensitiveData']) && isset($Result_Data['signature'])) {
            if ($Result_Data['sensitiveData'] != "" && $Result_Data['signature'] != "") {

                $PlainResponse = json_decode($this->DecryptDataWithPrivateKey($Result_Data['sensitiveData']), true);


                if (isset($PlainResponse['paymentReferenceId']) && isset($PlainResponse['challenge'])) {


                    $paymentReferenceId = $PlainResponse['paymentReferenceId'];


                    $randomServer = $PlainResponse['challenge'];

                    $SensitiveDataOrder = array(
                        'merchantId' => $MerchantID,
                        'orderId' => $OrderId,
                        'currencyCode' => '050',
                        'amount' => $amount,
                        'challenge' => $randomServer
                    );


                    $logo = config("nagad_payment_gateway.logo");

                    $merchantAdditionalInfo = '{"serviceName":"Brand Name", "serviceLogoURL": "' . $logo . '", "additionalFieldNameEN": "Type", "additionalFieldNameBN": "টাইপ","additionalFieldValue": "Payment", "reference": "' . $reference_id . '"}';

                    $PostDataOrder = array(
                        'sensitiveData' => $this->EncryptDataWithPublicKey(json_encode($SensitiveDataOrder)),
                        'signature' => $this->SignatureGenerate(json_encode($SensitiveDataOrder)),
                        'merchantCallbackURL' => $merchantCallbackURL,
                        'additionalMerchantInfo' => json_decode($merchantAdditionalInfo)
                    );

                    $OrderSubmitUrl = $this->baseUrl() . "check-out/complete/" . $paymentReferenceId;
                    $Result_Data_Order = $this->HttpPostMethod($OrderSubmitUrl, $PostDataOrder);

                    if ($Result_Data_Order['status'] == "Success") {
                        $url = json_encode($Result_Data_Order['callBackUrl']);
                        echo "<script>window.open($url, '_self')</script>";
                    } else {

                        echo json_encode($Result_Data_Order);
                    }
                } else {

                    echo json_encode($PlainResponse);
                }
            }
        }
    }
    public function NagadCallback(Request $request)
    {
        //dd($carbook);


        function HttpGet($url)
        {
            $ch = curl_init();
            $timeout = 10;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/0 (Windows; U; Windows NT 0; zh-CN; rv:3)");
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $file_contents = curl_exec($ch);
            echo curl_error($ch);
            curl_close($ch);
            return $file_contents;
        }

        $Query_String  = explode("&", explode("?", $_SERVER['REQUEST_URI'])[1]);
        $payment_ref_id = substr($Query_String[2], 15);
        $url = $this->baseUrl() . "verify/payment/" . $payment_ref_id;
        $json = HttpGet($url);
        $callback = json_decode($json, true);
        if (config("nagad_payment_gateway.response_type") == "json") {
            return response()->json($request->all());
        }
        // Success Log Store
        if ($callback['status'] == 'Success') {
            return redirect("/nagad-payment/{$callback['orderId']}/success");
        } else {

            return redirect("/nagad-payment/{$callback['orderId']}/fail");
        }
    }

    public function success($transId)
    {
        return view("nagad_payment_gateway::success", compact('transId'));
    }

    public function fail($transId)
    {
        return view("nagad_payment_gateway::failed", compact('transId'));
    }
}
