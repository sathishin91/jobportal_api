<?php
defined('BASEPATH') or exit('No direct script access allowed');


class SignIn extends CI_Controller
{


	public $responseData        = array('code' => 200, 'status' => 'success', 'message' => 'success'); // set API response array
	public $responseDataFailed  = array('code' => 404, 'status' => 'failed', 'message' => 'failed'); // set API response array
	public $DIR   = 'assets/images/';

	public function __construct()
	{
		parent::__construct();
		$this->load->library('email');
		$this->load->model("UserModel");
		$this->load->model("ApiCommonModel");
		$this->load->model('');
		Header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
		Header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
		Header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed

	}



	public function index()
	{
		// add here accessDenied page url
		//$this->load->view('error_page');
	}



	/**
	 * common function for set output
	 * function will set output in json format
	 */

	public function setOutPut()

	{
		$replaceArray = array('\/', '\n');
		$replaceStr = array('/', '');
		$returnData = json_encode($this->responseData);
		$returnData = str_replace($replaceArray, $replaceStr, $returnData);
		$returnData = strip_tags($returnData);
		$returnData = trim($returnData);
		header('Content-Type: application/json; charset=utf-8');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
		echo $returnData;
		exit();
	}


	/* 
     * Check Login
     */
	public function login()
	{
		$isAuth = $this->ApiCommonModel->decodeToken();
		if ($isAuth == 1) {
			$json_data = json_decode(file_get_contents("php://input"));
			$api_key   = $json_data->api_key;
			$role_id   = $json_data->role_id;


			if (isset($json_data)) {
				$api_key = $json_data->api_key;

				if ($this->ApiCommonModel->checkApiKey($api_key)) {
					$reqData = $json_data;
					$reqData = (array) $reqData;

					if (isset($reqData['mobile']) && $reqData['mobile'])

						$reqData['mobile'] = $reqData['mobile'];

					if (!empty($reqData['mobile'])) {
						$role    = NULL;

						$this->form_validation->set_data($reqData);
						$this->form_validation->set_rules('mobile', 'mobile', 'required');

						if ($this->form_validation->run() == TRUE) {

							$result = $this->UserModel->getRecord($this->UserModel->table, array('mobile' => $reqData['mobile']))->row_array();

							if ($result) {

								$role   = $this->UserModel->getRecord($this->UserModel->table, array('mobile' => $reqData['mobile']))->row_array()['role_id'];

								if ($role == 3) {
									// for registered employer users 
									//generate otp

									$otp                          = $this->generateOtp();
									$updateUserData['otp_code']   = $otp;
									$updateUserData['updated_at'] = strtotime(date('d-m-Y'));
									$updateUserData['token']      = 'seekk' . now() . $this->UserModel->generateRandomString();

									//update otp in db
									$this->UserModel->update($this->UserModel->table, $updateUserData, array('mobile' => $reqData['mobile'], 'role_id' => 3));

									//send sms
									// $send = $this->CommonModel->send_sms('8871249919', $otp);
									// if ($send) {
									// 	echo 'sent';
									// 	die();
									// } else {
									// 	echo 'not sent';
									// 	die();
									// }

									if ($reqData['role_id'] == 3) {
										//account verify
										if ($result['is_verify'] == 2) {
											$this->responseData['code']    = 400;
											$this->responseData['status']  = 'error';
											$this->responseData['message'] = "Your account is not verify, please verify.";
										} elseif ($result['is_active'] == 0) {
											$this->responseData['code']    = 400;
											$this->responseData['status']  = 'error';
											$this->responseData['message'] = "Your account is deactive, please contact to admin.";
										} else {
											unset($result['mobile']);
											$this->responseData['code']   	 = 200;
											$this->responseData['status'] 	 = 'success';
											$this->responseData['message']   = "OTP sent successfully.";
											// $this->responseData['mobile']    = intval($this->CommonModel->getRecord('user', array('mobile' => $reqData['mobile']))->row_array()['mobile']);
											$this->responseData['temp_otp']  = intval($updateUserData['otp_code']);
											// $this->responseData['user_role'] = $this->CommonModel->getRecord('user_role', array('id' => $role))->row_array()['role_constant'];
											// $this->responseData['token']    = $updateUserData['token'];
											// $this->responseData['is_verify'] = intval($this->UserModel->getRecord($this->UserModel->table, array('mobile' => $reqData['mobile']))->row_array()['is_verify']);
											// $this->responseData['is_registered'] = intval($this->UserModel->getRecord($this->UserModel->table, array('mobile' => $reqData['mobile']))->row_array()['is_registered']);
										}
									} else {
										$this->responseData['code']    = 404;
										$this->responseData['status']  = 'error';
										$this->responseData['message'] = "Already taken, try with another mobile no.!";
									}
								} elseif ($role == 4) {
									// for registered employee users
									//generate otp

									$otp                          = $this->generateOtp();
									$updateUserData['otp_code']   = $otp;
									$updateUserData['updated_at'] = strtotime(date('d-m-Y'));
									$updateUserData['token']      = 'seekk' . now() . $this->UserModel->generateRandomString();

									//update otp in db
									$this->UserModel->update($this->UserModel->table, $updateUserData, array('mobile' => $reqData['mobile'], 'role_id' => 4));

									//send sms
									// $this->sendSms($otp, $reqData['mobile']);

									if ($reqData['role_id'] == 4) {

										//account verify
										if ($result['is_verify'] == 0) {
											$this->responseData['code']    = 400;
											$this->responseData['status']  = 'error';
											$this->responseData['message'] = "Your account is not verify, please verify.";
										} elseif ($result['is_active'] == 0) {
											$this->responseData['code']    = 400;
											$this->responseData['status']  = 'error';
											$this->responseData['message'] = "Your account is deactive, please contact to admin.";
										} else {
											unset($result['mobile']);
											$this->responseData['code']   	 = 200;
											$this->responseData['status'] 	 = 'success';
											$this->responseData['message']   = "OTP sent successfully.";
											$this->responseData['mobile']    = intval($this->CommonModel->getRecord('user', array('mobile' => $reqData['mobile']))->row_array()['mobile']);
											$this->responseData['temp_otp']  = intval($updateUserData['otp_code']);
											$this->responseData['user_id']   = intval($this->CommonModel->getRecord('user', array('mobile' => $reqData['mobile']))->row_array()['id']);
											// $this->responseData['token']     = $updateUserData['token'];
											// $this->responseData['is_verify'] = intval($this->UserModel->getRecord($this->UserModel->table, array('mobile' => $reqData['mobile']))->row_array()['is_verify']);
											// $this->responseData['is_registered'] = intval($this->UserModel->getRecord($this->UserModel->table, array('mobile' => $reqData['mobile']))->row_array()['is_registered']);
										}
									} else {
										$this->responseData['code']    = 404;
										$this->responseData['status']  = 'error';
										$this->responseData['message'] = "Already taken, try with another mobile no.!";
									}
								} elseif ($role == 5) {
									// for registered employee users
									//generate otp
									$otp                          = $this->generateOtp();
									$updateUserData['otp_code']   = $otp;
									$updateUserData['updated_at'] = strtotime(date('d-m-Y'));
									$updateUserData['token']      = 'seekk' . now() . $this->UserModel->generateRandomString();

									//update otp in db
									$this->UserModel->update($this->UserModel->table, $updateUserData, array('mobile' => $reqData['mobile'], 'role_id' => 5));

									//send sms
									// $this->sendSms($otp, $reqData['mobile']);

									if ($reqData['role_id'] == 5) {

										//account verify
										if ($result['is_verify'] == 0) {
											$this->responseData['code']    = 400;
											$this->responseData['status']  = 'error';
											$this->responseData['message'] = "Your account is not verify, please verify.";
										} elseif ($result['is_active'] == 0) {
											$this->responseData['code']    = 400;
											$this->responseData['status']  = 'error';
											$this->responseData['message'] = "Your account is deactive, please contact to admin.";
										} else {
											unset($result['mobile']);
											$this->responseData['code']   	 = 200;
											$this->responseData['status'] 	 = 'success';
											$this->responseData['message']   = "OTP sent successfully.";
											$this->responseData['mobile']    = intval($this->CommonModel->getRecord('user', array('mobile' => $reqData['mobile']))->row_array()['mobile']);
											$this->responseData['temp_otp']  = intval($updateUserData['otp_code']);
											$this->responseData['user_id']   = intval($this->CommonModel->getRecord('user', array('mobile' => $reqData['mobile']))->row_array()['id']);
											// $this->responseData['token']     = $updateUserData['token'];
											// $this->responseData['is_verify'] = intval($this->UserModel->getRecord($this->UserModel->table, array('mobile' => $reqData['mobile']))->row_array()['is_verify']);
											// $this->responseData['is_registered'] = intval($this->UserModel->getRecord($this->UserModel->table, array('mobile' => $reqData['mobile']))->row_array()['is_registered']);
										}
									} else {
										$this->responseData['code']    = 404;
										$this->responseData['status']  = 'error';
										$this->responseData['message'] = "Already taken, try with another mobile no.!";
									}
								}
							} else {
								// 			$this->responseData['code']    = 401;
								// 			$this->responseData['status']  = 'failed';
								// 			$this->responseData['message'] = 'Mobile number is wrong';
								// 			unset($this->responseData['data']);

								// for not registered users
								//generate otp
								$updateUserData['mobile']             = $reqData['mobile'];
								$updateUserData['role_id']            = $reqData['role_id'];
								$otp                                  = $this->generateOtp();
								$updateUserData['otp_code']           = $otp;
								$updateUserData['created_at']         = strtotime(date('d-m-Y'));
								$updateUserData['token']              = 'seekk' . now() . $this->UserModel->generateRandomString();
								$updateUserData['is_registered']      = 0;
								$updateUserData['is_active']          = 1;

								//update otp in db
								if ($role_id == 3) {
									$updateUserData['is_verify']          = 2;
								} else {
									$updateUserData['is_verify']          = 1;
								}
								$this->UserModel->insert($this->UserModel->table, $updateUserData);

								//send sms
								// $this->sendSms($otp, $reqData['mobile']);

								//account verify
								if ($updateUserData['is_verify'] == 0) {
									$this->responseData['code']    = 400;
									$this->responseData['status']  = 'error';
									$this->responseData['message'] = "Please verify your account first!";
								} elseif ($updateUserData['is_active'] == 0) {
									$this->responseData['code']    = 400;
									$this->responseData['status']  = 'error';
									$this->responseData['message'] = "Your account is deactive, please contact to admin.";
								} else {
									unset($result['mobile']);
									$this->responseData['code']   	 = 200;
									$this->responseData['status'] 	 = 'success';
									$this->responseData['message']   = "OTP sent successfully.";
									$this->responseData['mobile']    = intval($this->CommonModel->getRecord('user', array('mobile' => $reqData['mobile']))->row_array()['mobile']);
									$this->responseData['temp_otp']  = intval($updateUserData['otp_code']);
									// $this->responseData['user_role'] = $this->CommonModel->getRecord('user_role', array('id' => $reqData['role_id']))->row_array()['role_constant'];
									// // $this->responseData['token']  = $updateUserData['token'];
									// $this->responseData['is_verify'] = intval($this->UserModel->getRecord($this->UserModel->table, array('mobile' => $reqData['mobile']))->row_array()['is_verify']);
									// $this->responseData['is_registered'] = intval($this->UserModel->getRecord($this->UserModel->table, array('mobile' => $reqData['mobile']))->row_array()['is_registered']);
								}
							}
						} else {
							$msg = $this->ApiCommonModel->validationErrorMsg();
							$this->responseData['code']    = 400;
							$this->responseData['status']  = 'failed';
							$this->responseData['message'] = $msg;
						}
					} else {
						$this->responseData['code']    = 404;
						$this->responseData['status']  = 'failed';
						$this->responseData['message'] = 'Required param missing: mobile no. required';
					}
				} else {
					$this->responseData['code']    = 400;
					$this->responseData['status']  = 'failed';
					$this->responseData['message'] = 'Invalid api key!';
				}
			} else {
				$this->responseData['code']    = 400;
				$this->responseData['status']  = 'failed';
				$this->responseData['message'] = 'Invalid request';
				unset($this->responseData['data']);
			}
			unset($json_data);
		} elseif ($isAuth == 0) {
			$this->responseData['code']    = 400;
			$this->responseData['status']  = 'failed';
			$this->responseData['message'] = 'Token is invalid or expired!';
		} else {
			$this->responseData['code']    = 400;
			$this->responseData['status']  = 'failed';
			$this->responseData['message'] = 'Bearer Token required!';
		}
		SELF::setOutPut();
	}

	/* 
     * Check OTP verification
     */

	public function verify()
	{
		$isAuth = $this->ApiCommonModel->decodeToken();
		if ($isAuth == 1) {
			$json_data = json_decode(file_get_contents("php://input"));
			$api_key     = $json_data->api_key;

			if (isset($json_data)) {
				$api_key = $json_data->api_key;

				if ($this->ApiCommonModel->checkApiKey($api_key)) {
					$reqData = $json_data;
					$reqData = (array) $reqData;

					if (isset($reqData['otp_code']) && $reqData['otp_code'])

						$reqData['otp_code'] = $reqData['otp_code'];

					if (!empty($reqData['otp_code'])) {
						$this->form_validation->set_data($reqData);
						$this->form_validation->set_rules('mobile', 'mobile', 'required');
						$this->form_validation->set_rules('otp_code', 'otp_code', 'required');

						if ($this->form_validation->run() == TRUE) {
							$result = $this->UserModel->getRecord('user', array('mobile' => $reqData['mobile'], 'otp_code' => $reqData['otp_code']))->row_array();

							if ($result) {
								$updateUserData['last_login'] = date('Y-m-d H:i:s a');
								$updateUserData['login_info'] = $_SERVER['SERVER_ADDR'];

								$this->CommonModel->update($this->UserModel->table, $updateUserData, array('mobile' => $reqData['mobile']));

								//send sms
								// $res = $this->CommonModel->sendOtp();
								// if ($res) {
								// 	echo 'sent';
								// 	die();
								// } else {
								// 	echo 'not sent';
								// 	die();
								// }

								//account verify
								if ($result['is_verify'] == 0) {
									$this->responseData['code']    = 401;
									$this->responseData['status']  = 'error';
									$this->responseData['message'] = "Your account is not verify, please verify.";
								} elseif ($result['is_active'] == 0) {
									$this->responseData['code']    = 1001;
									$this->responseData['status']  = 'error';
									$this->responseData['message'] = "Your account is deactive, please contact to admin.";
								} else {
									unset($result['mobile']);
									$this->responseData['code']   	 = 200;
									$this->responseData['status'] 	 = 'success';
									$this->responseData['mobile']    = intval($this->CommonModel->getRecord('user', array('mobile' => $reqData['mobile']))->row_array()['mobile']);
									$this->responseData['user_role'] = $this->CommonModel->getRecord('user_role', array('id' => $result['role_id']))->row_array()['role_constant'];
									// $this->responseData['data2'] = $result;
									if ($result['is_compOrConstOrPers'] == 1 || $result['is_compOrConstOrPers'] == 2) {
										$this->responseData['data']      = [
											'id'                   =>  intval($result['id']),
											'role_id'              =>  intval($result['role_id']),
											'company_name'         => $result['company_name'],
											'email'                => $result['email'],
											'website'              => $result['website'],
											'otp_code'             => intval($result['otp_code']),
											'is_verify'            => intval($result['is_verify']),
											'is_registered'        =>  intval($result['is_registered']),
											'is_compOrConstOrPers' =>  intval($result['is_compOrConstOrPers']),
											'no_of_employees'      =>  intval($result['no_of_employees']),
											'gst'                  => $result['gst'],
											'is_terms_condition'   =>  intval($result['is_terms_condition']),
											'is_completed'         =>  intval($result['is_completed']),
										];
									} else {
										$this->responseData['data']      = [
											'id'                   =>  intval($result['id']),
											'role_id'              =>  intval($result['role_id']),
											'company_name'         => $result['company_name'],
											'email'                => $result['email'],
											'website'              => $result['website'],
											'otp_code'             => intval($result['otp_code']),
											'is_verify'            => intval($result['is_verify']),
											'is_registered'        =>  intval($result['is_registered']),
											'is_compOrConstOrPers' =>  intval($result['is_compOrConstOrPers']),
											'no_of_employees'      =>  intval($result['no_of_employees']),
											'is_terms_condition'   =>  intval($result['is_terms_condition']),
											'is_completed'         =>  intval($result['is_completed']),
										];
									}

									$this->responseData['message']   = "Loggedin successfully.";
								}
							} else {
								$this->responseData['code']    = 401;
								$this->responseData['status']  = 'failed';
								$this->responseData['message'] = 'Wrong credentials';
								unset($this->responseData['data']);
							}
						} else {
							$msg = $this->ApiCommonModel->validationErrorMsg();
							$this->responseData['code']    = 401;
							$this->responseData['status']  = 'failed';
							$this->responseData['message'] = $msg;
						}
					} else {
						$this->responseData['code']    = 404;
						$this->responseData['status']  = 'failed';
						$this->responseData['message'] = 'Required param missing: mobile no. required';
						unset($this->responseData['data']);
					}
				} else {
					$this->responseData['code']    = 401;
					$this->responseData['status']  = 'failed';
					$this->responseData['message'] = 'Invalid api key!';
				}
			} else {
				$this->responseData['code'] = 401;
				$this->responseData['status'] = 'failed';
				$this->responseData['message'] = 'Invalid Request';
				unset($this->responseData['data']);
			}
			unset($json_data);
		} elseif ($isAuth == 0) {
			$this->responseData['code']    = 400;
			$this->responseData['status']  = 'failed';
			$this->responseData['message'] = 'Token is invalid or expired!';
		} else {
			$this->responseData['code']    = 400;
			$this->responseData['status']  = 'failed';
			$this->responseData['message'] = 'Bearer Token required!';
		}
		SELF::setOutPut();
	}


	/* 
     * Generate OTP
     */
	public function generateOtp()
	{
		$OTP 	=	rand(1, 9);
		$OTP 	.=	rand(0, 9);
		$OTP 	.=	rand(0, 9);
		$OTP 	.=	rand(0, 9);
		// $OTP 	.=	rand(0, 9);
		// $OTP 	.=	rand(0, 9);
		return $OTP;
	}


	/* 
     * Send OTP sms by bulksmsgateway
     */
	public function sendSms($otp_code, $to)
	{

		$username = 'Jtsbusiness';
		$password = 'Edwin@123';
		$mobile_number = "8871249919";
		$sender = "seekkr";
		$message = $otp_code . " is your OTP. Do not share with anyone.";
		$template_id = 1207169537311791426;

		$url = "http://api.bulksmsgateway.in/sendmessage.php?user=" . urlencode($username) . "&password=" . urlencode($password) . "&mobile=" . urlencode($mobile_number) . "&message=" . urlencode($message) . "&sender=" . urlencode($sender) . "&type=" . urlencode('3') . "&template_id=" . urlencode($template_id);

		//Curl Start
		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$curl_scraped_page = curl_exec($ch);

		$response = curl_exec($ch);

		curl_close($ch);

		die($response);
	}

	/*
     * Status Job profile
     */
	public function status()
	{
		$isAuth = $this->ApiCommonModel->decodeToken();
		if ($isAuth == 1) {
			if (empty($json_data = json_decode(file_get_contents("php://input")))) {
				$this->responseData['code']    = 404;
				$this->responseData['status']  = 'failed';
				$this->responseData['message'] = 'Required fields are missing';
			} else {
				$json_data  = json_decode(file_get_contents("php://input"));
				$api_key    = $json_data->api_key;
				$mobile     = $json_data->mobile;
			}
			if ($json_data) {
				// $api_key = $this->input->post('api_key');
				$api_key = $json_data->api_key;

				if ($this->ApiCommonModel->checkApiKey($api_key)) {
					$reqData = $json_data;
					$reqData = (array) $reqData;

					if (!empty($mobile)) {

						$this->form_validation->set_data($reqData);
						$this->form_validation->set_rules('mobile', 'Mobile No', 'required|trim');

						if ($this->form_validation->run() == TRUE) {
							$jobData['mobile']       = $mobile;

							if ($mobile) {
								$result = $this->CommonModel->getRecord('user', array('mobile' => $mobile))->row_array();

								// if (!empty($getRecord)) {
								if ($result) {
									$this->responseData['code']           = 200;
									// $this->responseData['status']         = 'success';
									$this->responseData['message']        = "Status Fetched.";
									$this->responseData['status']   = intval($result['is_compOrConstOrPers']);
								} else {
									$this->responseData['code']    = 401;
									$this->responseData['status']  = 'failed';
									$this->responseData['message'] = 'Not found';
									unset($this->responseData['data']);
								}
								// } else {
								//     $this->responseData['code'] = 404;
								//     $this->responseData['message'] = 'Not found  ';
								//     $this->responseData['status']  = 'failed';
								// }
							} else {
								$this->responseData['code'] = 404;
								$this->responseData['message'] = 'Not found ';
								$this->responseData['status']  = 'failed';
							}
						} else {
							$msg = $this->ApiCommonModel->validationErrorMsg();
							$this->responseData['code']    = 400;
							$this->responseData['status']  = 'failed';
							$this->responseData['message'] = $msg;
						}
					} else {
						$this->responseData['code']    = 404;
						$this->responseData['status']  = 'failed';
						$this->responseData['message'] = 'Required param missing';
					}
				} else {
					$this->responseData['code']    = 400;
					$this->responseData['status']  = 'failed';
					$this->responseData['message'] = 'Invalid api key!';
				}
			} else {
				$this->responseData['code']    = 400;
				$this->responseData['status']  = 'failed';
				$this->responseData['message'] = 'Invalid request';
			}
		} elseif ($isAuth == 0) {
			$this->responseData['code']    = 400;
			$this->responseData['status']  = 'failed';
			$this->responseData['message'] = 'Token is invalid or expired!';
		} else {
			$this->responseData['code']    = 400;
			$this->responseData['status']  = 'failed';
			$this->responseData['message'] = 'Bearer Token required!';
		}

		self::setOutPut();
	}

	// public function sendSms()
	// {

	// 	// Multiple mobiles numbers separated by comma					
	// 	// Sender ID,While using route4 sender id should be 6 characters long.
	// 	$senderId 	= 'seekkr';

	// 	// Your message to send, Add URL encoding here.
	// 	$message 	= urlencode($body);

	// 	//Define route 
	// 	$route 		= 'trans';

	// 	//Prepare you post parameters
	// 	$postData 	= array(
	// 		'mobiles' 	=> $phone,
	// 		'message' 	=> $message,
	// 		'sender' 	=> $senderId,
	// 		'route' 	=> $route
	// 	);

	// 	//API URL
	// 	$url 		= 'http://api.bulksmsgateway.in/sendmessage.php';

	// 	$ch = curl_init();
	// 	curl_setopt_array($ch, array(
	// 		CURLOPT_URL 			=> $url,
	// 		CURLOPT_RETURNTRANSFER		=> true,
	// 		CURLOPT_POST 			=> true,
	// 		CURLOPT_POSTFIELDS 		=> $postData
	// 	));

	// 	//Ignore SSL certificate verification
	// 	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	// 	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

	// 	//get response
	// 	$output = curl_exec($ch);

	// 	curl_close($ch);
	// }
}
