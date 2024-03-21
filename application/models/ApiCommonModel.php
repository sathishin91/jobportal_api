<?php
include_once './vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ApiCommonModel extends CommonModel
{
	public $apiKey = "seekk!@#$%2023";
	public function __construct()
	{
		parent::__construct();
	}

	public function checkApiKey($key)
	{
		$access = false;
		if (!empty($key)) {
			if ($key == $this->apiKey) {
				$access = true;
			}
		}

		return $access;
	}

	function validationErrorMsg()
	{

		$CI = &get_instance();
		$fetch =   $CI->form_validation->error_array();
		foreach ($fetch as $eval) {
			$msg = $eval;
		}

		return $msg;
	}


	// function curlReq($url)
	// {
	// 	$ch = curl_init($url);
	// 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// 	//curl_setopt($ch, CURLOPT_ENCODING , "");
	// 	$data = curl_exec($ch);
	// 	curl_close($ch);
	// 	return $data;
	// }

	/*
	decode jwt token
	*/
	public function decodeToken()
	{

		// Get the token from wherever it is stored (e.g., from the request headers)
		$token = $this->input->get_request_header('Authorization');
		if (!empty($token)) {
			$token = str_replace('Bearer ', '', $token);

			$secretKey = "seekk!@#$%2023";

			// Verify and decode the token
			try {
				$decodedToken = JWT::decode($token, new key($secretKey, 'HS256'));
			} catch (\Firebase\JWT\ExpiredException $e) {
				// Token has expired
				// echo 'Token has expired';
				return 0;
			} catch (\Exception $e) {
				// Token is invalid for some other reason
				// echo 'Invalid token';
				return 0;
			}

			// Check the expiration time in the decoded token
			// $expirationTime = $decodedToken->exp;

			// Compare the expiration time with the current time
			// $currentTime = time();

			// if ($expirationTime < $currentTime) {
			// 	// echo 'Token has expired';
			// 	return 0;
			// } else {
			return 1;
			// }
		} else {
			return 3;
		}
	}
}
