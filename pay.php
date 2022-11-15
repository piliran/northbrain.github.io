<?php
session_start();
$url = "http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'];
$host = parse_url($url, PHP_URL_HOST);
$ref = rand(10001, 99999).rand(10001, 99999);

$pay = MobiPay::InitiatePayment();
var_dump($pay);
class MobiPay{

	

	function __construct()
	{
		
	}


	//Initiate payment request from Malipo API
	public static function InitiatePayment(){
	    global $ref;
		try{
			if(isset($_REQUEST['amount']) && !empty($_REQUEST['amount'])){

				//Set Amount and Merchant Unique Reference ID
				$amount = $_REQUEST['amount'];
				$_SESSION['amount'] = $amount;
				$merchantRef = $ref;
				$_SESSION['merchantRef'] = $merchantRef;
 
				//Get Full Transaction Payload Pre-Build
				$payload = self::getTransactionPayload($amount, $merchantRef);

				//Make XHR HTTP Request to MobiPay server
				$httpPost = self::makeHttpRequest($payload);
								//Evaluate Response
				if($httpPost['statusText'] == 'OK'){
					if($httpPost['data']['responseCode'] == '00'){
						//Payment Initiated Successfully. Proceed to redirect to Payment Page
						header('LOCATION:'.$httpPost['data']['url']);
						//return $httpPost;
					}else{
						/* return json_encode(array(
							'code'=>500,
							'status'=>'error',
							'message'=>"Payment could not be initiated!",
							'data'=>$httpPost
						)); */
						return $httpPost;
					}
				}else{
					var_dump($httpPost['data']);
				}
			}else{
				return json_encode(array(
					'code'=>401,
					'status'=>'error',
					'message'=>"Denied. Invalid/Inadequest Request Parameters!"
				));
			}
			
		}catch(Exception $error){
			return json_encode(array(
				'code'=>500,
				'status'=>'error',
				'message'=>$error->getMessage()
			));
		}
	}

	private static function makeHttpRequest($data){
		global $host;
		try{	
			$paymentUrl = "https://mobipay.cash/prod/merchantController/requestPayment";
			//$paymentUrl = "https://adimo-shopping.com/mobipay/index.php";


			// Create a new cURL resource
			$ch = curl_init($paymentUrl);
			
			$payload = json_encode($data);

			// Attach encoded JSON string to the POST fields
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

			// Set the content type to application/json
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

			// Return response instead of outputting
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			// Execute the POST request
			$result = curl_exec($ch);
			file_put_contents('link.txt', $result);
			// Close cURL resource
			curl_close($ch);

			return [
				'statusText'=>'OK',
				'data'=>json_decode($result, true)
			];
		}catch(Exception $error){
			return [
				'statusText'=>"ERROR",
				'error'=>$error->getMessage()
			];
		}
	}

	private static function getTransactionPayload($amount,$reference){
		global $host;
		return array(
			"merchantId" => 1760,
			"description" => "Market Finder.",
			"language" => "EN",
			"merchantRef" => $reference,
			"currency" => "MWK",
			"amount" => $amount,

			"successUrl" => "http://$host/ipn.php?status=success&merchantRef={$_SESSION['merchantRef']}&amount={$_SESSION['amount']}",
			"failedUrl" =>  "http://$host/ipn.php?status=failure&merchantRef=",
			"cancelledUrl" => "http://$host/ipn.php?status=cancelled&merchantRef=",
			"redirectUrl" => "http://$host/done.php?status=success&amount={$_SESSION['amount']}",
		);
	}
}



?>
USERNAME: pjaz
PASSWORD: zpiliran97@gmail

https://pjaz.000webhostapp.com/zimba.php