<?php
defined('BASEPATH') or exit('No direct script access allowed');

class TestController extends CI_Controller
{
    public $responseData = array('code' => 200, 'status' => 'success', 'message' => 'Your have registered successfully'); // set API response array
    public $customerRoleId    = null;
    public $DIR       = 'assets/api/images/';
    public $DIR_doc   = 'assets/api/doc/';
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

    /*
     * Adding Employee Info
     */
    public function getUser()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {
            $json_data = json_decode(file_get_contents("php://input"));

            if ($json_data) {
                // $api_key = $this->input->post('api_key');
                $api_key = $json_data->api_key;
                $user_id = $json_data->user_id;

                if ($this->ApiCommonModel->checkApiKey($api_key)) {
                    $reqData = $json_data;
                    $reqData = (array) $reqData;

                    if (!empty($user_id)) {

                        $this->form_validation->set_data($reqData);
                        $this->form_validation->set_rules('user_id', 'User Id', 'required|trim');

                        if ($this->form_validation->run() == TRUE) {
                            // $userData['id']          = $user_id;
                            $where = json_encode(array('id' => 1));
                            // $decodedJson = json_decode(stripslashes($where), true);

                            // print_r($where);
                            // die();

                            if ($user_id) {
                                $getRecord = $this->CommonModel->GetRecords('user', $where);

                                // foreach ($getRecord  as $getRecord) {
                                //     print_r($getRecord);
                                //     die();
                                // }
                                print_r($getRecord);
                                die();


                                if (!empty($getRecord)) {
                                    $result = $this->CommonModel->select_rec('user', '*', array('id' => $user_id, 'role_id' => 4))->result();

                                    if ($result) {
                                        $this->responseData['code']         = 200;
                                        $this->responseData['status']       = 'success';
                                        $this->responseData['message']      = "Successfull.";
                                        $this->responseData['data']         = $result;
                                    } else {
                                        $this->responseData['code']    = 401;
                                        $this->responseData['status']  = 'failed';
                                        $this->responseData['message'] = 'Wrong User';
                                        unset($this->responseData['data']);
                                    }
                                } else {
                                    $this->responseData['code'] = 404;
                                    $this->responseData['message'] = 'Not found ';
                                    $this->responseData['status']  = 'failed';
                                }
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
                        $this->responseData['message'] = 'Required param missing: user_id';
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
}
