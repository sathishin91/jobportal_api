<?php
defined('BASEPATH') or exit('No direct script access allowed');

class InterviewerInfo extends CI_Controller
{
    public $responseData = array('code' => 200, 'status' => 'success', 'message' => 'Your have registered successfully'); // set API response array
    public $customerRoleId    = null;
    public $DIR   = 'assets/images/';
    public function __construct()
    {
        parent::__construct();
        $this->load->model(["CommonModel", "ApiCommonModel", "InterviewerInfoModel"]);
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
     * Adding Interviewer info
     */
    public function add()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {
            if(empty($json_data = json_decode(file_get_contents("php://input")))){
		    $this->responseData['code']    = 404;
			$this->responseData['status']  = 'failed';
			$this->responseData['message'] = 'Required fields are missing';
		        
		    }else{   
        $json_data    = json_decode(file_get_contents("php://input"));
        $api_key      = $json_data->api_key;
        $user_id      = $json_data->user_id;
        $job_id       = $json_data->job_id;
        $com_pref     = $json_data->com_pref;
        $com_pref_fn  = $json_data->com_pref_fn;
        $com_pref_mob = $json_data->com_pref_mob;
        $noti_pref        = $json_data->noti_pref;
        $noti_pref_fn     = $json_data->noti_pref_fn;
        $noti_pref_mob    = $json_data->noti_pref_mob;
        $interview_method = $json_data->interview_method;
		    }

        if ($json_data) {
            $api_key = $json_data->api_key;

            if ($this->ApiCommonModel->checkApiKey($api_key)) {
                $reqData = $json_data;
                $reqData = (array) $reqData;

                if (!empty($user_id)) {

                    $this->form_validation->set_data($reqData);
                    $this->form_validation->set_rules('user_id', 'User Id', 'required|trim');
                    $this->form_validation->set_rules('job_id', 'Job Id', 'required|trim');
                    $this->form_validation->set_rules('com_pref', 'Communication preference', 'required|trim');
                    $this->form_validation->set_rules('noti_pref', 'Notification preference', 'required|trim');
                    $this->form_validation->set_rules('interview_method', 'Interview method', 'required|trim');


                    if ($this->form_validation->run() == TRUE) {

                        if ($com_pref == 2 && $noti_pref != 2) {
                            $intData['user_id']             = $user_id;
                            $intData['job_id']              = $job_id;
                            $intData['com_pref']            = $com_pref;
                            $intData['com_pref_fn']         = $com_pref_fn;
                            $intData['com_pref_mob']        = $com_pref_mob;
                            $intData['noti_pref']           = $noti_pref;
                            $intData['interview_method']    = $interview_method;
                            $intData['is_active']          = 1;
                            $intData['is_verify']          = 1;
                            $intData['created_at']         = strtotime(date('d-m-Y'));
                            $intData['is_completed']       = 1;
                        } elseif ($noti_pref == 2 && $com_pref != 2) {
                            $intData['user_id']             = $user_id;
                            $intData['job_id']              = $job_id;
                            $intData['com_pref']            = $com_pref;
                            $intData['noti_pref']           = $noti_pref;
                            $intData['noti_pref_fn']        = $noti_pref_fn;
                            $intData['noti_pref_mob']       = $noti_pref_mob;
                            $intData['interview_method']    = $interview_method;
                            $intData['is_active']           = 1;
                            $intData['is_verify']           = 1;
                            $intData['created_at']          = strtotime(date('d-m-Y'));
                            $intData['is_completed']       = 1;
                        } elseif ($com_pref == 2 && $noti_pref == 2) {
                            $intData['user_id']             = $user_id;
                            $intData['job_id']              = $job_id;
                            $intData['com_pref']            = $com_pref;
                            $intData['com_pref_fn']         = $com_pref_fn;
                            $intData['com_pref_mob']        = $com_pref_mob;
                            $intData['noti_pref']           = $noti_pref;
                            $intData['noti_pref_fn']        = $noti_pref_fn;
                            $intData['noti_pref_mob']       = $noti_pref_mob;
                            $intData['interview_method']    = $interview_method;
                            $intData['is_active']          = 1;
                            $intData['is_verify']          = 1;
                            $intData['created_at']         = strtotime(date('d-m-Y'));
                            $intData['is_completed']       = 1;
                        } else {
                            $intData['user_id']             = $user_id;
                            $intData['job_id']              = $job_id;
                            $intData['com_pref']            = $com_pref;
                            $intData['noti_pref']           = $noti_pref;
                            $intData['interview_method']    = $interview_method;
                            $intData['is_active']          = 1;
                            $intData['is_verify']          = 1;
                            $intData['created_at']         = strtotime(date('d-m-Y'));
                            $intData['is_completed']       = 1;
                        }

                        if ($intData['user_id']) {
                             $getRecord = $this->InterviewerInfoModel->getRecord('interviewer_info',array('job_id'=>$job_id))->row();
                              
                              if(!empty($getRecord)){
                            $result = $this->InterviewerInfoModel->update('interviewer_info', $intData, array('job_id'=>$job_id));
                            // $result = $this->InterviewerInfoModel->insert('interviewer_info', $intData);

                            if ($result) {
                                 $getCompletedStatus = $this->InterviewerInfoModel->getRecord('job_details',array('id'=>$job_id))->row();
        
                                if($getCompletedStatus->is_completed != 3){
                                    $this->InterviewerInfoModel->update('job_details', array('is_completed'=>3), array('id'=>$job_id));
                                }

                                $this->responseData['code']              = 200;
                                $this->responseData['status']            = 'success';
                                // $this->responseData['data']         = $result;
                                $this->responseData['interviewer_info']  = $this->InterviewerInfoModel->getRecord('interviewer_info', array('job_id' => $job_id))->row_array();
                                $this->responseData['message']           = "Added successfully.";
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
    
    
    /*
     * Edir Interviewer info
     */
    public function edit()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {
            if(empty($json_data = json_decode(file_get_contents("php://input")))){
		    $this->responseData['code']    = 404;
			$this->responseData['status']  = 'failed';
			$this->responseData['message'] = 'Required fields are missing';
		        
		    }else{   
        $json_data    = json_decode(file_get_contents("php://input"));
        $api_key      = $json_data->api_key;
        $user_id      = $json_data->user_id;
        $job_id       = $json_data->job_id;
        $com_pref     = $json_data->com_pref;
        $com_pref_fn  = $json_data->com_pref_fn;
        $com_pref_mob = $json_data->com_pref_mob;
        $noti_pref        = $json_data->noti_pref;
        $noti_pref_fn     = $json_data->noti_pref_fn;
        $noti_pref_mob    = $json_data->noti_pref_mob;
        $interview_method = $json_data->interview_method;
		    }

        if ($json_data) {
            $api_key = $json_data->api_key;

            if ($this->ApiCommonModel->checkApiKey($api_key)) {
                $reqData = $json_data;
                $reqData = (array) $reqData;

                if (!empty($user_id)) {

                    $this->form_validation->set_data($reqData);
                    $this->form_validation->set_rules('user_id', 'User Id', 'required|trim');
                    $this->form_validation->set_rules('job_id', 'Job Id', 'required|trim');
                    $this->form_validation->set_rules('com_pref', 'Communication preference', 'required|trim');
                    $this->form_validation->set_rules('noti_pref', 'Notification preference', 'required|trim');
                    $this->form_validation->set_rules('interview_method', 'Interview method', 'required|trim');


                    if ($this->form_validation->run() == TRUE) {

                        if ($com_pref == 2 && $noti_pref != 2) {
                            $intData['user_id']             = $user_id;
                            $intData['job_id']              = $job_id;
                            $intData['com_pref']            = $com_pref;
                            $intData['com_pref_fn']         = $com_pref_fn;
                            $intData['com_pref_mob']        = $com_pref_mob;
                            $intData['noti_pref']           = $noti_pref;
                            $intData['interview_method']    = $interview_method;
                            $intData['is_active']          = 1;
                            $intData['is_verify']          = 1;
                            $intData['updated_at']         = strtotime(date('d-m-Y'));
                            $intData['is_completed']       = 1;
                        } elseif ($noti_pref == 2 && $com_pref != 2) {
                            $intData['user_id']             = $user_id;
                            $intData['job_id']              = $job_id;
                            $intData['com_pref']            = $com_pref;
                            $intData['noti_pref']           = $noti_pref;
                            $intData['noti_pref_fn']        = $noti_pref_fn;
                            $intData['noti_pref_mob']       = $noti_pref_mob;
                            $intData['interview_method']    = $interview_method;
                            $intData['is_active']           = 1;
                            $intData['is_verify']           = 1;
                            $intData['updated_at']          = strtotime(date('d-m-Y'));
                            $intData['is_completed']       = 1;
                        } elseif ($com_pref == 2 && $noti_pref == 2) {
                            $intData['user_id']             = $user_id;
                            $intData['job_id']              = $job_id;
                            $intData['com_pref']            = $com_pref;
                            $intData['com_pref_fn']         = $com_pref_fn;
                            $intData['com_pref_mob']        = $com_pref_mob;
                            $intData['noti_pref']           = $noti_pref;
                            $intData['noti_pref_fn']        = $noti_pref_fn;
                            $intData['noti_pref_mob']       = $noti_pref_mob;
                            $intData['interview_method']    = $interview_method;
                            $intData['is_active']          = 1;
                            $intData['is_verify']          = 1;
                            $intData['updated_at']         = strtotime(date('d-m-Y'));
                            $intData['is_completed']       = 1;
                        } else {
                            $intData['user_id']             = $user_id;
                            $intData['job_id']              = $job_id;
                            $intData['com_pref']            = $com_pref;
                            $intData['noti_pref']           = $noti_pref;
                            $intData['interview_method']    = $interview_method;
                            $intData['is_active']          = 1;
                            $intData['is_verify']          = 1;
                            $intData['updated_at']         = strtotime(date('d-m-Y'));
                            $intData['is_completed']       = 1;
                        }

                        if ($intData['user_id']) {
                             $getRecord = $this->InterviewerInfoModel->getRecord('interviewer_info',array('job_id'=>$job_id))->row();
                              
                              if(!empty($getRecord)){
                            $result = $this->InterviewerInfoModel->update('interviewer_info', $intData, array('job_id'=>$job_id));
                            // $result = $this->InterviewerInfoModel->insert('interviewer_info', $intData);

                            if ($result) {
                                 $getCompletedStatus = $this->InterviewerInfoModel->getRecord('job_details',array('id'=>$job_id))->row();
        
                                if($getCompletedStatus->is_completed != 3){
                                    $this->InterviewerInfoModel->update('job_details', array('is_completed'=>3), array('id'=>$job_id));
                                }
                                $this->responseData['code']              = 200;
                                $this->responseData['status']            = 'success';
                                // $this->responseData['data']         = $result;
                                // $this->responseData['interviewer_info']  = $this->InterviewerInfoModel->getRecord('interviewer_info', array('job_id' => $job_id))->row_array();
                                $this->responseData['message']           = "Updated successfully.";
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
