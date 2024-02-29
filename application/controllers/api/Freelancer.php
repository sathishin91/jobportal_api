<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Freelancer extends CI_Controller
{
    public $responseData = array('code' => 200, 'status' => 'success', 'message' => 'Your have registered successfully'); // set API response array
    public $customerRoleId    = null;
    public $DIR   = 'assets/api/images/';
    public $freelancerDoc   = 'assets/freelancerDoc/';
    public function __construct()
    {
        parent::__construct();
        $this->load->model(["CommonModel", "ApiCommonModel", "JobDetailsModel"]);
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
     *  verify freelancer
     */

    public function verifyFreelancer()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {

            $json_data   = json_decode(file_get_contents("php://input"));
            $api_key     = $json_data->api_key;
            $user_id     = $json_data->user_id;
            $role_id     = $json_data->role_id;

            if (isset($json_data)) {
                $api_key = $json_data->api_key;

                if ($this->ApiCommonModel->checkApiKey($api_key)) {
                    $reqData = $json_data;
                    $reqData = (array) $reqData;

                    if (!empty($reqData['role_id'])) {
                        $this->form_validation->set_data($reqData);
                        $this->form_validation->set_rules('user_id', 'User ID', 'required');
                        $this->form_validation->set_rules('role_id', 'Role ID', 'required');


                        if ($this->form_validation->run() == TRUE) {

                            $result = $this->CommonModel->select_rec('user', '*', array('id' => $user_id))->row_array();

                            // print_r($result['role_id']);
                            // die();

                            if ($result['role_id']) {
                                $update = $this->CommonModel->update('user', array('role_id' => $role_id), array('id' => $user_id));

                                // print_r($update);
                                // die();

                                if ($update) {
                                    $this->responseData['code']        = 200;
                                    $this->responseData['status']      = 'success';
                                    $this->responseData['message']     = "Updated successfully.";
                                    // $this->responseData['data']        = $result;
                                } else {
                                    $this->responseData['code']    = 401;
                                    $this->responseData['status']  = 'failed';
                                    $this->responseData['message'] = 'Not updated successfully.';
                                }
                            } else {
                                $this->responseData['code']    = 404;
                                $this->responseData['status']  = 'failed';
                                $this->responseData['message'] = 'Not Found';
                                // $this->responseData['completed']   = [];
                                // $this->responseData['pending']     = [];
                                unset($this->responseData['data']);
                            }
                        } else {
                            $msg = $this->ApiCommonModel->validationErrorMsg();
                            $this->responseData['code']    = 401;
                            $this->responseData['status']  = 'failed';
                            $this->responseData['message'] = $msg;
                        }
                    } else {
                        $this->responseData['code']    = 401;
                        $this->responseData['status']  = 'failed';
                        $this->responseData['message'] = 'Required param missing: Id';
                        unset($this->responseData['data']);
                    }
                } else {
                    $this->responseData['code']    = 401;
                    $this->responseData['status']  = 'failed';
                    $this->responseData['message'] = 'Invalid api key!';
                }
            } else {
                $this->responseData['code']    = 401;
                $this->responseData['status']  = 'failed';
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
     * Adding Freelancer Info
     */
    public function addFreelancerInfo()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {
            $json_data = json_decode(file_get_contents("php://input"));

            $api_key     = $json_data->api_key;
            $user_id     = $json_data->user_id;
            $first_name  = $json_data->first_name;
            $last_name   = $json_data->last_name;
            $email       = $json_data->email;
            $gender      = $json_data->gender;
            $dob         = $json_data->dob;
            $city        = $json_data->city;
            $id_proof    = $json_data->id_proof;
            $id_upload   = $json_data->id_upload;
            $other_lang  = $json_data->other_lang;
            $description = $json_data->description;
            $img         = $json_data->img;

            if ($json_data) {
                // $api_key = $this->input->post('api_key');
                $api_key = $json_data->api_key;

                if ($this->ApiCommonModel->checkApiKey($api_key)) {
                    $reqData = $json_data;
                    $reqData = (array) $reqData;

                    if (!empty($user_id)) {

                        $this->form_validation->set_data($reqData);
                        $this->form_validation->set_rules('user_id', 'User Id', 'required|trim');
                        $this->form_validation->set_rules('first_name', 'First Name', 'required|trim');
                        $this->form_validation->set_rules('last_name', 'Last Name', 'required|trim');
                        $this->form_validation->set_rules('email', 'Email', 'required|trim');
                        $this->form_validation->set_rules('gender', 'English Level', 'required|trim');
                        $this->form_validation->set_rules('dob', 'Date of Birth', 'required|trim');
                        $this->form_validation->set_rules('city', 'City', 'required|trim');
                        $this->form_validation->set_rules('other_lang', 'Other language', 'required|trim');

                        if ($this->form_validation->run() == TRUE) {
                            // $userData['id']          = $user_id;
                            $userData['first_name']  = $first_name;
                            $userData['last_name']   = $last_name;
                            $userData['email']       = $email;
                            $userData['gender']      = $gender;
                            $userData['dob']         = $dob;
                            $userData['city']        = $city;
                            $userData['id_proof']    = $id_proof;
                            $userData['other_lang']  = $other_lang;
                            $userData['description'] = $description;

                            $userData['is_active']          = 1;
                            $userData['is_verify']          = 1;
                            $userData['created_at']         = strtotime(date('d-m-Y'));
                            $userData['is_registered']      = 1;
                            $userData['is_completed']       = 1;

                            //image upload
                            $rand  = rand(10, 10000);
                            $image = preg_replace('#^data:image/[^;]+;base64,#', '', $img);
                            $name               = $this->DIR . $rand . 'image.png';
                            $new                = file_put_contents($name, base64_decode($image));
                            $userData['user_avatar'] = $rand . 'image.png';

                            //doc upload(need to work on)
                            // $id_upload               = $json_data->id_upload;
                            // $rand  = rand(10, 10000);
                            // $image = preg_replace('#^data:image/[^;]+;base64,#', '', $img);
                            // $name               = $this->DIR . $rand . 'image.png';
                            // $new                = file_put_contents($name, base64_decode($image));
                            // $userData['user_avatar'] = $rand . 'image.png';

                            if ($user_id) {
                                $getRecord = $this->CommonModel->getRecord('user', array('id' => $user_id, 'role_id' => 5))->row_array();

                                if (!empty($getRecord)) {
                                    if (empty($getRecord['user_avatar'])) {
                                        $this->CommonModel->update('user', array('user_avatar' => $userData['user_avatar']), array('id' => $user_id));
                                    } else {
                                        unlink($this->DIR . $getRecord['user_avatar']);
                                    }
                                    // print_r($userData);
                                    // die();
                                    $result = $this->CommonModel->update('user', $userData, array('id' => $user_id));



                                    if ($result) {
                                        $this->responseData['code']         = 200;
                                        $this->responseData['status']       = 'success';
                                        // $this->responseData['data']         = $result;
                                        $this->responseData['img_url']   = base_url('assets/api/images/');
                                        $this->responseData['message']      = "Added successfully.";
                                    } else {
                                        $this->responseData['code']    = 401;
                                        $this->responseData['status']  = 'failed';
                                        $this->responseData['message'] = 'Wrong Freelancer User';
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


    /*
     * Adding Feelancer Skills Info
     */
    public function addFreelancerSkill()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {
            $json_data = json_decode(file_get_contents("php://input"));

            $api_key           = $json_data->api_key;
            $user_id           = $json_data->user_id;
            $skill             = $json_data->skill;

            if ($json_data) {
                // $api_key = $this->input->post('api_key');
                $api_key = $json_data->api_key;

                if ($this->ApiCommonModel->checkApiKey($api_key)) {
                    $reqData = $json_data;
                    $reqData = (array) $reqData;

                    if (!empty($user_id)) {

                        $this->form_validation->set_data($reqData);
                        $this->form_validation->set_rules('user_id', 'User Id', 'required|trim');
                        $this->form_validation->set_rules('skill', 'Skill', 'required|trim');

                        if ($this->form_validation->run() == TRUE) {

                            $userData['user_id']         = $user_id;
                            $userData['skill']           = strtoupper($skill);

                            $userData['is_active']          = 1;
                            $userData['is_verify']          = 1;
                            $userData['created_at']         = strtotime(date('d-m-Y'));
                            $userData['is_completed']       = 1;
                            // print_r($userData);
                            // die();

                            if ($user_id) {
                                $getRecord = $this->CommonModel->getRecord('user', array('id' => $user_id, 'role_id' => 5))->row_array();
                                $getSkillsRecord = $this->CommonModel->getRecord('skill_info', array('user_id' => $user_id))->row_array();

                                if (!empty($getRecord) && !empty($getSkillsRecord)) {
                                    $result = $this->CommonModel->update('skill_info', $userData, array('user_id' => $user_id));

                                    if ($result) {
                                        $this->responseData['code']         = 200;
                                        $this->responseData['status']       = 'success';
                                        // $this->responseData['data']         = $result;
                                        $this->responseData['message']      = "Added successfully.";
                                    } else {
                                        $this->responseData['code']    = 401;
                                        $this->responseData['status']  = 'failed';
                                        $this->responseData['message'] = 'Wrong Freelancer User';
                                        unset($this->responseData['data']);
                                    }
                                } elseif (!empty($getRecord) && empty($getSkillsRecord)) {

                                    $result = $this->CommonModel->insert('skill_info', $userData);

                                    if ($result) {
                                        $this->responseData['code']         = 200;
                                        $this->responseData['status']       = 'success';
                                        // $this->responseData['data']         = $result;
                                        $this->responseData['message']      = "Added successfully.";
                                    } else {
                                        $this->responseData['code']    = 401;
                                        $this->responseData['status']  = 'failed';
                                        $this->responseData['message'] = 'Wrong Freelancer User';
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


    /*
     * Adding Freelancer Plans Info
     */
    public function addPlan()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {
            $json_data = json_decode(file_get_contents("php://input"));

            $api_key           = $json_data->api_key;
            $user_id           = $json_data->user_id;
            $basic_title       = $json_data->basic_title;
            $basic_amount      = $json_data->basic_amount;
            $basic_del_time    = $json_data->basic_del_time;
            $basic_rev         = $json_data->basic_rev;
            $basic_des         = $json_data->basic_des;
            $standard_title    = $json_data->standard_title;
            $standard_amount   = $json_data->standard_amount;
            $standard_del_time = $json_data->standard_del_time;
            $standard_rev      = $json_data->standard_rev;
            $standard_des      = $json_data->standard_des;
            $premium_title     = $json_data->premium_title;
            $premium_amount    = $json_data->premium_amount;
            $premium_del_time  = $json_data->premium_del_time;
            $premium_rev       = $json_data->premium_rev;
            $premium_des       = $json_data->premium_des;
            $upload            = $json_data->upload;
            $project_des       = $json_data->project_des;

            if ($json_data) {
                // $api_key = $this->input->post('api_key');
                $api_key = $json_data->api_key;

                if ($this->ApiCommonModel->checkApiKey($api_key)) {
                    $reqData = $json_data;
                    $reqData = (array) $reqData;

                    if (!empty($user_id)) {

                        $this->form_validation->set_data($reqData);
                        $this->form_validation->set_rules('user_id', 'User Id', 'required|trim');
                        $this->form_validation->set_rules('basic_title', 'Basic Title', 'required|trim');
                        $this->form_validation->set_rules('basic_amount', 'Basic Amount', 'required|trim');
                        $this->form_validation->set_rules('basic_del_time', 'Basic Delete Time', 'required|trim');
                        $this->form_validation->set_rules('basic_rev', 'Basic No. of Revision', 'required|trim');
                        $this->form_validation->set_rules('basic_des', 'Basic Description', 'required|trim');
                        $this->form_validation->set_rules('standard_title', 'Standard Title', 'required|trim');
                        $this->form_validation->set_rules('standard_amount', 'Standard Amount', 'required|trim');
                        $this->form_validation->set_rules('standard_del_time', 'Standard Delete Time', 'required|trim');
                        $this->form_validation->set_rules('standard_rev', 'Standard No. of Revision', 'required|trim');
                        $this->form_validation->set_rules('standard_des', 'Standard Description', 'required|trim');
                        $this->form_validation->set_rules('premium_title', 'Premium Title', 'required|trim');
                        $this->form_validation->set_rules('premium_amount', 'Premium Amount', 'required|trim');
                        $this->form_validation->set_rules('premium_del_time', 'Premium Delete Time', 'required|trim');
                        $this->form_validation->set_rules('premium_rev', 'Premium No. of Revision', 'required|trim');
                        $this->form_validation->set_rules('premium_des', 'Premium Description', 'required|trim');

                        if ($this->form_validation->run() == TRUE) {

                            $userData['user_id']           = $user_id;
                            $userData['basic_title']       = $basic_title;
                            $userData['basic_amount']      = $basic_amount;
                            $userData['basic_del_time']    = $basic_del_time;
                            $userData['basic_rev']         = $basic_rev;
                            $userData['basic_des']         = $basic_des;
                            $userData['standard_title']    = $standard_title;
                            $userData['standard_amount']   = $standard_amount;
                            $userData['standard_del_time'] = $standard_del_time;
                            $userData['standard_rev']      = $standard_rev;
                            $userData['standard_des']      = $standard_des;
                            $userData['premium_title']     = $premium_title;
                            $userData['premium_amount']    = $premium_amount;
                            $userData['premium_del_time']  = $premium_del_time;
                            $userData['premium_rev']       = $premium_rev;
                            $userData['premium_des']       = $premium_des;
                            $userData['upload']            = $upload;
                            $userData['project_des']       = $project_des;

                            $userData['is_active']          = 1;
                            $userData['is_verify']          = 1;
                            $userData['created_at']         = strtotime(date('d-m-Y'));
                            $userData['is_completed']       = 1;
                            // print_r($userData);
                            // die();

                            if ($user_id) {
                                $getRecord = $this->CommonModel->getRecord('user', array('id' => $user_id, 'role_id' => 5))->row_array();
                                $getPlanRecord = $this->CommonModel->getRecord('plan_info', array('user_id' => $user_id))->row_array();

                                if (!empty($getRecord) && !empty($getPlanRecord)) {
                                    $result = $this->CommonModel->update('plan_info', $userData, array('user_id' => $user_id));

                                    if ($result) {
                                        $this->responseData['code']         = 200;
                                        $this->responseData['status']       = 'success';
                                        // $this->responseData['data']         = $result;
                                        $this->responseData['message']      = "Added successfully.";
                                    } else {
                                        $this->responseData['code']    = 401;
                                        $this->responseData['status']  = 'failed';
                                        $this->responseData['message'] = 'Wrong Freelancer User';
                                        unset($this->responseData['data']);
                                    }
                                } elseif (!empty($getRecord) && empty($getPlanRecord)) {

                                    $result = $this->CommonModel->insert('plan_info', $userData);

                                    if ($result) {
                                        $this->responseData['code']         = 200;
                                        $this->responseData['status']       = 'success';
                                        // $this->responseData['data']         = $result;
                                        $this->responseData['message']      = "Added successfully.";
                                    } else {
                                        $this->responseData['code']    = 401;
                                        $this->responseData['status']  = 'failed';
                                        $this->responseData['message'] = 'Wrong Freelancer User';
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

    /*
     * Adding Freelancer Bank Info
     */
    public function addBank()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {
            $json_data = json_decode(file_get_contents("php://input"));

            $api_key           = $json_data->api_key;
            $user_id           = $json_data->user_id;
            $acc_info          = $json_data->acc_info;
            //acc_info = 1
            $account_no        = $json_data->account_no;
            $ifsc              = $json_data->ifsc;
            $account_holder    = $json_data->account_holder;
            //acc_info = 2
            $upi_id            = $json_data->upi_id;
            $upi_mobile        = $json_data->upi_mobile;

            if ($json_data) {
                // $api_key = $this->input->post('api_key');
                $api_key = $json_data->api_key;

                if ($this->ApiCommonModel->checkApiKey($api_key)) {
                    $reqData = $json_data;
                    $reqData = (array) $reqData;

                    if (!empty($user_id)) {

                        $this->form_validation->set_data($reqData);
                        $this->form_validation->set_rules('user_id', 'User Id', 'required|trim');
                        if ($acc_info == 1) {
                            $this->form_validation->set_rules('account_no', 'Account Number', 'required|trim');
                            $this->form_validation->set_rules('ifsc', 'IFSC', 'required|trim');
                            $this->form_validation->set_rules('account_holder', 'Account Holder Name', 'required|trim');
                        } elseif ($acc_info == 2) {
                            $this->form_validation->set_rules('upi_id', 'Upi Id', 'required|trim');
                            $this->form_validation->set_rules('upi_mobile', 'Upi Mobile No', 'required|trim');
                        }

                        if ($this->form_validation->run() == TRUE) {

                            $userData['user_id']         = $user_id;
                            $userData['acc_info']        = $acc_info;

                            if ($acc_info == 1) {
                                $userData['account_no']      = $account_no;
                                $userData['ifsc']            = $ifsc;
                                $userData['account_holder']  = $account_holder;
                            } elseif ($acc_info == 2) {
                                $userData['upi_id']          = $upi_id;
                                $userData['upi_mobile']      = $upi_mobile;
                            }

                            $userData['is_active']          = 1;
                            $userData['is_verify']          = 1;
                            $userData['is_completed']       = 1;
                            $userData['created_at']         = strtotime(date('d-m-Y'));

                            if ($user_id) {
                                $getRecord = $this->CommonModel->getRecord('user', array('id' => $user_id, 'role_id' => 5))->row_array();
                                $getPlanRecord = $this->CommonModel->getRecord('bank_info', array('user_id' => $user_id))->row_array();

                                if (!empty($getRecord) && !empty($getPlanRecord)) {
                                    $result = $this->CommonModel->update('bank_info', $userData, array('user_id' => $user_id));

                                    if ($result) {
                                        $this->responseData['code']         = 200;
                                        $this->responseData['status']       = 'success';
                                        // $this->responseData['data']         = $result;
                                        $this->responseData['message']      = "Added successfully.";
                                    } else {
                                        $this->responseData['code']    = 401;
                                        $this->responseData['status']  = 'failed';
                                        $this->responseData['message'] = 'Wrong Freelancer User';
                                        unset($this->responseData['data']);
                                    }
                                } elseif (!empty($getRecord) && empty($getPlanRecord)) {

                                    $result = $this->CommonModel->insert('bank_info', $userData);

                                    if ($result) {
                                        $this->responseData['code']         = 200;
                                        $this->responseData['status']       = 'success';
                                        // $this->responseData['data']         = $result;
                                        $this->responseData['message']      = "Added successfully.";
                                    } else {
                                        $this->responseData['code']    = 401;
                                        $this->responseData['status']  = 'failed';
                                        $this->responseData['message'] = 'Wrong Freelancer User';
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


    /*
    *  get job list by skills and user_id
    */

    public function getJobListBySkills()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {

            $json_data   = json_decode(file_get_contents("php://input"));
            $api_key     = $json_data->api_key;
            $user_id     = $json_data->user_id;

            if (isset($json_data)) {
                $api_key = $json_data->api_key;

                if ($this->ApiCommonModel->checkApiKey($api_key)) {
                    $reqData = $json_data;
                    $reqData = (array) $reqData;

                    if (!empty($reqData['user_id'] && $reqData['user_id'])) {
                        $this->form_validation->set_data($reqData);
                        $this->form_validation->set_rules('user_id', 'User ID', 'required');

                        if ($this->form_validation->run() == TRUE) {

                            $check_role = $this->CommonModel->select_rec('user', '*', array('id' => $user_id))->row_array();
                            if ($check_role['role_id']) {
                                $getSkills = $this->CommonModel->select_rec('skill_info', '*', array('user_id' => $user_id))->row_array();

                                $skills = explode(',', $getSkills['skill']);

                                $result = $this->CommonModel->filterBySkills($skills);

                                if ($result) {

                                    $this->responseData['code']        = 200;
                                    $this->responseData['status']      = 'success';
                                    $this->responseData['message']     = "Fetched successfully.";
                                    $this->responseData['data']        = $result;
                                    // $this->responseData['pending']     = $pendingResult;
                                } else {
                                    $this->responseData['code']    = 401;
                                    $this->responseData['status']  = 'failed';
                                    $this->responseData['message'] = 'Not fetched';
                                    $this->responseData['no_data'] = [];

                                    // $this->responseData['pending']     = [];
                                    unset($this->responseData['data']);
                                }
                            } else {
                                $this->responseData['code']    = 401;
                                $this->responseData['status']  = 'failed';
                                $this->responseData['message'] = 'Not freelancer';
                            }
                        } else {
                            $msg = $this->ApiCommonModel->validationErrorMsg();
                            $this->responseData['code']    = 401;
                            $this->responseData['status']  = 'failed';
                            $this->responseData['message'] = $msg;
                        }
                    } else {
                        $this->responseData['code']    = 401;
                        $this->responseData['status']  = 'failed';
                        $this->responseData['message'] = 'Required param missing: Id';
                        unset($this->responseData['data']);
                    }
                } else {
                    $this->responseData['code']    = 401;
                    $this->responseData['status']  = 'failed';
                    $this->responseData['message'] = 'Invalid api key!';
                }
            } else {
                $this->responseData['code']    = 401;
                $this->responseData['status']  = 'failed';
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
     * Adding job details
     */
    public function addFreelancerJob()
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
                //freelancer details
                $user_id            = $json_data->user_id;
                $job_title          = $json_data->job_title;
                $primary_skills     = $json_data->primary_skills;
                $secondary_skills   = $json_data->secondary_skills;
                $technology         = $json_data->technology;
                $project_des        = $json_data->project_des;
                $file               = $json_data->file;
                $min_payment        = $json_data->min_payment;
                $max_payment        = $json_data->max_payment;
                $is_budget_flexible = $json_data->is_budget_flexible;
                $timing             = $json_data->timing;
            }

            if (isset($json_data)) {
                $api_key = $json_data->api_key;

                if ($this->ApiCommonModel->checkApiKey($api_key)) {
                    $reqData = $json_data;
                    $reqData = (array) $reqData;

                    if (!empty($reqData)) {

                        //job details
                        $this->form_validation->set_data($reqData);
                        $this->form_validation->set_rules('user_id', 'User Id', 'required|trim');
                        $this->form_validation->set_rules('job_title', 'Job Title', 'required|trim');
                        $this->form_validation->set_rules('primary_skills', 'Primary Skills', 'required|trim');
                        $this->form_validation->set_rules('secondary_skills', 'Secondary Skills', 'required|trim');
                        $this->form_validation->set_rules('technology', 'Technology', 'required|trim');
                        $this->form_validation->set_rules('project_des', 'Project Description', 'required|trim');
                        $this->form_validation->set_rules('min_payment', 'Minimum Payment', 'required|trim');
                        $this->form_validation->set_rules('max_payment', 'Pay Type', 'required|trim');


                        if ($this->form_validation->run() == TRUE) {
                            // job details
                            $jobData['user_id']            = $user_id;
                            // $jobData['primary_skills'] = strtoupper(preg_replace('/[^A-Za-z0-9\-]/', '', $primary_skills));
                            $jobData['job_title']          = $job_title;
                            $jobData['primary_skills']     = strtoupper($primary_skills);
                            $jobData['secondary_skills']   = strtoupper($secondary_skills);
                            $jobData['technology']         = strtoupper($technology);
                            $jobData['project_des']        = $project_des;
                            $jobData['min_payment']        = $min_payment;
                            $jobData['max_payment']        = $max_payment;
                            $jobData['is_budget_flexible'] = $is_budget_flexible;
                            $jobData['timing']             = $timing;

                            $jobData['job_type']          = 4; //static jobtype '4' for freelanceronly
                            $jobData['is_active']         = 1;
                            $jobData['is_verify']         = 1;
                            $jobData['created_at']        = strtotime(date('d-m-Y'));
                            $jobData['create_date']       = date('d-m-Y');
                            $jobData['is_completed']      = 1;

                            $result = $this->CommonModel->insert('job_details', $jobData);
                            // job details end

                            if ($jobData['user_id']) {
                                if ($result) {
                                    $this->responseData['code']         = 200;
                                    $this->responseData['status']       = 'success';
                                    // $this->responseData['job_id']       = $result;
                                    // $this->responseData['job_details']  = $this->CommonModel->getRecord('job_details', array('id' => $result))->row_array();
                                    $this->responseData['message']      = "Added successfully.";
                                } else {
                                    $this->responseData['code']    = 401;
                                    $this->responseData['status']  = 'failed';
                                    $this->responseData['message'] = 'Not added successfully';
                                    unset($this->responseData['data']);
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


    /**
     *  get freelancer job list by user_id and job_id
     */

    public function getFreelancerJobListByUserId()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {

            $json_data   = json_decode(file_get_contents("php://input"));
            $api_key     = $json_data->api_key;
            $user_id     = $json_data->user_id;

            if (isset($json_data)) {
                $api_key = $json_data->api_key;

                if ($this->ApiCommonModel->checkApiKey($api_key)) {
                    $reqData = $json_data;
                    $reqData = (array) $reqData;

                    if (!empty($reqData['user_id'] && $reqData['user_id'])) {
                        $this->form_validation->set_data($reqData);
                        $this->form_validation->set_rules('user_id', 'User ID', 'required');

                        if ($this->form_validation->run() == TRUE) {


                            $result = $this->CommonModel->select_rec('job_details', '*', array('job_type' => 4, 'user_id' => $user_id))->result();

                            // $data['res'] = $fresult;
                            if ($result) {

                                $this->responseData['code']        = 200;
                                $this->responseData['status']      = 'success';
                                $this->responseData['message']     = "Fetched successfully.";
                                $this->responseData['data']        = $result;
                                // $this->responseData['pending']     = $pendingResult;
                            } else {
                                $this->responseData['code']    = 401;
                                $this->responseData['status']  = 'failed';
                                $this->responseData['message'] = 'Not fetched';
                                // $this->responseData['completed']   = [];
                                // $this->responseData['pending']     = [];
                                unset($this->responseData['data']);
                            }
                        } else {
                            $msg = $this->ApiCommonModel->validationErrorMsg();
                            $this->responseData['code']    = 401;
                            $this->responseData['status']  = 'failed';
                            $this->responseData['message'] = $msg;
                        }
                    } else {
                        $this->responseData['code']    = 401;
                        $this->responseData['status']  = 'failed';
                        $this->responseData['message'] = 'Required param missing: Id';
                        unset($this->responseData['data']);
                    }
                } else {
                    $this->responseData['code']    = 401;
                    $this->responseData['status']  = 'failed';
                    $this->responseData['message'] = 'Invalid api key!';
                }
            } else {
                $this->responseData['code']    = 401;
                $this->responseData['status']  = 'failed';
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


    /**
     *  get employee list by user_id and job_id
     */

    public function getFreelancerListByJobId()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {

            $json_data   = json_decode(file_get_contents("php://input"));
            $api_key     = $json_data->api_key;
            // $user_id     = $json_data->user_id;
            $job_id      = $json_data->job_id;

            if (isset($json_data)) {
                $api_key = $json_data->api_key;

                if ($this->ApiCommonModel->checkApiKey($api_key)) {
                    $reqData = $json_data;
                    $reqData = (array) $reqData;

                    if (!empty($reqData['job_id'])) {
                        $this->form_validation->set_data($reqData);
                        // $this->form_validation->set_rules('user_id', 'User ID', 'required');
                        $this->form_validation->set_rules('job_id', 'Job ID', 'required');


                        if ($this->form_validation->run() == TRUE) {
                            $result = $this->CommonModel->select_rec('job_details', '*', array('id' => $job_id))->row_array();


                            $result = explode(',', $result['primary_skills']);
                            // foreach ($result as $res) {
                            //     $skills = $res;
                            $fresult = $this->CommonModel->getMatchingDataBySkills($result[0]);
                            // }
                            // print_r($fresult);
                            // die();

                            // $data['res'] = $fresult;
                            if ($result) {

                                $this->responseData['code']        = 200;
                                $this->responseData['status']      = 'success';
                                $this->responseData['message']     = "Fetched successfully.";
                                $this->responseData['data']        = $fresult;
                                // $this->responseData['pending']     = $pendingResult;
                            } else {
                                $this->responseData['code']    = 401;
                                $this->responseData['status']  = 'failed';
                                $this->responseData['message'] = 'Not fetched';
                                // $this->responseData['completed']   = [];
                                // $this->responseData['pending']     = [];
                                unset($this->responseData['data']);
                            }
                        } else {
                            $msg = $this->ApiCommonModel->validationErrorMsg();
                            $this->responseData['code']    = 401;
                            $this->responseData['status']  = 'failed';
                            $this->responseData['message'] = $msg;
                        }
                    } else {
                        $this->responseData['code']    = 401;
                        $this->responseData['status']  = 'failed';
                        $this->responseData['message'] = 'Required param missing: Id';
                        unset($this->responseData['data']);
                    }
                } else {
                    $this->responseData['code']    = 401;
                    $this->responseData['status']  = 'failed';
                    $this->responseData['message'] = 'Invalid api key!';
                }
            } else {
                $this->responseData['code']    = 401;
                $this->responseData['status']  = 'failed';
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
}
