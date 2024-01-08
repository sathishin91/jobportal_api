<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SignUp extends CI_Controller
{
    public $responseData = array('code' => 200, 'status' => 'success', 'message' => 'Your have registered successfully'); // set API response array
    public $customerRoleId    = null;
    public $DIR   = 'assets/images/';
    public function __construct()
    {
        parent::__construct();
        $this->load->model(["CommonModel", "ApiCommonModel", "UserModel"]);
        Header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
        Header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
        Header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed

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


    /**
     * Signup 
     */
    public function signup()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
		if ($isAuth == 1) {
		      
		    if(empty($json_data = json_decode(file_get_contents("php://input")))){
		    $this->responseData['code']    = 404;
			$this->responseData['status']  = 'failed';
			$this->responseData['message'] = 'Required fields are missing';
		        
		    }else{        

        $json_data     = json_decode(file_get_contents("php://input"));
        $api_key       = $json_data->api_key;
        $role_id       = $json_data->role_id;
        $mobile        = $json_data->mobile;
        $company_name     = $json_data->company_name;
        $is_company       = $json_data->is_company;
        $email            = $json_data->email;
        $website          = $json_data->website;
        $no_of_employees  = $json_data->no_of_employees;
        $is_accept        = $json_data->is_accept;
		    }
    
        if (isset($json_data)) {
            $api_key = $json_data->api_key;

            if ($this->ApiCommonModel->checkApiKey($api_key)) {
                $reqData = $json_data;
                $reqData = (array) $reqData;

                if (!empty($reqData)) {

                    $this->form_validation->set_data($reqData);
                    $this->form_validation->set_rules('role_id', 'Role Id', 'required|trim');
                    $this->form_validation->set_rules('company_name', 'First Name Id', 'required|trim');
                    $this->form_validation->set_rules('is_company', 'Company or Consultancy', 'required|trim');
                    $this->form_validation->set_rules('email', 'Email', 'required|trim');
                    $this->form_validation->set_rules('mobile', 'Mobile no', 'required|trim');
                    // $this->form_validation->set_rules('website', 'Website', 'required|trim');
                    $this->form_validation->set_rules('no_of_employees', 'No of Employees', 'required|trim');
                    $this->form_validation->set_rules('is_accept', 'Terms and Conditions', 'required|trim');


                    if ($this->form_validation->run() == TRUE) {
                        $userData['role_id']            = $role_id;
                        // $userData['mobile']          = $mobile;
                        $userData['company_name']       = $company_name;
                        $userData['is_company']         = $is_company;
                        $userData['email']              = $email;
                        $userData['website']            = $website;
                        $userData['is_active']          = 1;
                        $userData['is_verify']          = 2;
                        $userData['is_registered']      = 1;
                        $userData['created_at']         = strtotime(date("d-m-Y"));
                        $userData['no_of_employees']    = $no_of_employees;
                        $userData['is_accept']          = $is_accept;

                $checkMob =  $this->UserModel->getRecord('user', array('mobile' => $mobile))->row_array();
                   if($checkMob){
                       
                        // if ($userData['email'] && $userData['mobile']) {
                            if ($email) {
                            $checkEmail =  $this->UserModel->getRecord('user', array('email' => $email))->row_array();
                            // $checkUser =  $this->UserModel->getRecord('user', array('mobile' => $userData['mobile']))->row_array();
                        
                            // if (empty($checkEmail || $checkUser)) {
                                if (empty($checkEmail)) {
                                $result = $this->UserModel->update($this->UserModel->table, $userData,array('mobile'=>$mobile));
                                    
                                if ($result) {
                                    $this->responseData['code']         = 200;
                                    $this->responseData['status']       = 'success';
                                    // $this->responseData['data']      = $result;
                                    $this->responseData['message']      = "Signup successfully.";
                                    $this->responseData['id']           = intval($this->CommonModel->getRecord('user',array('mobile'=>$mobile))->row_array()['id']);
								    $this->responseData['user_role']    = $this->CommonModel->getRecord('user_role',array('id'=>$role_id))->row_array()['role_constant'];
								    $this->responseData['mobile']       = intval($this->CommonModel->getRecord('user',array('mobile'=>$mobile))->row_array()['mobile']);
								    $this->responseData['company_name'] = $this->CommonModel->getRecord('user',array('mobile'=>$mobile))->row_array()['company_name'];
								    $this->responseData['email']        = $this->CommonModel->getRecord('user',array('mobile'=>$mobile))->row_array()['email'];
								    $this->responseData['website']      = $this->CommonModel->getRecord('user',array('mobile'=>$mobile))->row_array()['website'];
								    $this->responseData['token']        = $this->CommonModel->getRecord('user',array('mobile'=>$mobile))->row_array()['token'];
								    $this->responseData['is_active']    = intval($this->CommonModel->getRecord('user',array('mobile'=>$mobile))->row_array()['is_active']);
								    $this->responseData['is_verify']    = intval($this->CommonModel->getRecord('user',array('mobile'=>$mobile))->row_array()['is_verify']);
								    $this->responseData['is_registered']= intval($this->CommonModel->getRecord('user',array('mobile'=>$mobile))->row_array()['is_registered']);
								    $this->responseData['is_company']      = intval($this->CommonModel->getRecord('user',array('mobile'=>$mobile))->row_array()['is_company']);
								    $this->responseData['no_of_employees'] = intval($this->CommonModel->getRecord('user',array('mobile'=>$mobile))->row_array()['no_of_employees']);
								    $this->responseData['is_accept']       = intval($this->CommonModel->getRecord('user',array('mobile'=>$mobile))->row_array()['is_accept']);
                                } else {
                                    $this->responseData['code']    = 401;
                                    $this->responseData['status']  = 'failed';
                                    $this->responseData['message'] = 'Email or Mobile is wrong';
                                    unset($this->responseData['data']);
                                }
                            } else {
                                $this->responseData['code'] = 400;
                                $this->responseData['message'] = 'Email already registered, please try with different email address';
                                $this->responseData['status']  = 'failed';
                            }
                        } else {
                            $this->responseData['code'] = 404;
                            $this->responseData['message'] = 'Not found ';
                            $this->responseData['status']  = 'failed';
                        }
                      }else{
                            $this->responseData['code'] = 404;
                            $this->responseData['message'] = 'Mobile no. not found ';
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
                    $this->responseData['message'] = 'Required param missing:userSignup';
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
        self::setOutPut();
    }
}
