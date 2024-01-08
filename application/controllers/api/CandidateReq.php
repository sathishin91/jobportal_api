<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CandidateReq extends CI_Controller
{
    public $responseData = array('code' => 200, 'status' => 'success', 'message' => 'Your have registered successfully'); // set API response array
    public $customerRoleId    = null;
    public $DIR   = 'assets/images/';
    public function __construct()
    {
        parent::__construct();
        $this->load->model(["CommonModel", "ApiCommonModel", "CandidateReqModel"]);
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
     * Adding Candidate requests
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
        $json_data  = json_decode(file_get_contents("php://input"));
        $api_key    = $json_data->api_key;
        $user_id    = $json_data->user_id;
        $job_id     = $json_data->job_id;
        $education  = $json_data->education;
        $experience = $json_data->experience;
        $eng_lvl    = $json_data->eng_lvl;
        $description = $json_data->description;
		    }

        if ($json_data) {
            // $api_key = $this->input->post('api_key');
            $api_key = $json_data->api_key;

            if ($this->ApiCommonModel->checkApiKey($api_key)) {
                $reqData = $json_data;
                $reqData = (array) $reqData;

                if (!empty($user_id)) {

                    $this->form_validation->set_data($reqData);
                    $this->form_validation->set_rules('user_id', 'User Id', 'required|trim');
                    $this->form_validation->set_rules('job_id', 'Job Id', 'required|trim');
                    $this->form_validation->set_rules('education', 'Education', 'required|trim');
                    $this->form_validation->set_rules('experience', 'Experience', 'required|trim');
                    $this->form_validation->set_rules('eng_lvl', 'English Level', 'required|trim');
                    $this->form_validation->set_rules('description', 'Description', 'required|trim');

                    if ($this->form_validation->run() == TRUE) {
                        $canData['user_id']     = $user_id;
                        $canData['job_id']      = $job_id;
                        $canData['education']   = $education;
                        $canData['experience']  = $experience;
                        $canData['eng_lvl']     = $eng_lvl;
                        $canData['description'] = $description;
                        $canData['is_active']          = 1;
                        $canData['is_verify']          = 1;
                        $canData['created_at']         = strtotime(date('d-m-Y'));
                        $canData['is_completed']       = 1;

                        if ($canData['user_id']) {
                              $getRecord = $this->CandidateReqModel->getRecord('candidate_req',array('job_id'=>$job_id))->row();
                              
                              if(!empty($getRecord)){
                                  
                            $result = $this->CandidateReqModel->update('candidate_req', $canData, array('job_id'=>$job_id));


                            if ($result) {
                                $getCompletedStatus = $this->CandidateReqModel->getRecord('job_details',array('id'=>$job_id))->row();
        
                                if($getCompletedStatus->is_completed != 3){
                                    $this->CandidateReqModel->update('job_details', array('is_completed'=>2), array('id'=>$job_id));
                                }     
                                
                                $this->responseData['code']           = 200;
                                $this->responseData['status']         = 'success';
                                // $this->responseData['data']         = $result;
                                $this->responseData['candidate_req']  = $this->CandidateReqModel->getRecord('candidate_req', array('job_id' => $job_id))->row_array();
                                $this->responseData['message']        = "Added successfully.";
                            } else {
                                $this->responseData['code']    = 401;
                                $this->responseData['status']  = 'failed';
                                $this->responseData['message'] = 'Wrong User';
                                unset($this->responseData['data']);
                            }
                              }else{
                                  $this->responseData['code'] = 404;
                                  $this->responseData['message'] = 'Not found  ';
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
     * Edit Candidate requests
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
        $json_data  = json_decode(file_get_contents("php://input"));
        $api_key    = $json_data->api_key;
        $user_id    = $json_data->user_id;
        $job_id     = $json_data->job_id;
        $education  = $json_data->education;
        $experience = $json_data->experience;
        $eng_lvl    = $json_data->eng_lvl;
        $description = $json_data->description;
		    }

        if ($json_data) {
            // $api_key = $this->input->post('api_key');
            $api_key = $json_data->api_key;

            if ($this->ApiCommonModel->checkApiKey($api_key)) {
                $reqData = $json_data;
                $reqData = (array) $reqData;

                if (!empty($user_id)) {

                    $this->form_validation->set_data($reqData);
                    $this->form_validation->set_rules('user_id', 'User Id', 'required|trim');
                    $this->form_validation->set_rules('job_id', 'Job Id', 'required|trim');
                    $this->form_validation->set_rules('education', 'Education', 'required|trim');
                    $this->form_validation->set_rules('experience', 'Experience', 'required|trim');
                    $this->form_validation->set_rules('eng_lvl', 'English Level', 'required|trim');
                    $this->form_validation->set_rules('description', 'Description', 'required|trim');

                    if ($this->form_validation->run() == TRUE) {
                        $canData['user_id']     = $user_id;
                        $canData['job_id']      = $job_id;
                        $canData['education']   = $education;
                        $canData['experience']  = $experience;
                        $canData['eng_lvl']     = $eng_lvl;
                        $canData['description'] = $description;
                        $canData['is_active']          = 1;
                        $canData['is_verify']          = 1;
                        $canData['updated_at']         = strtotime(date('d-m-Y'));
                        $canData['is_completed']       = 1;

                        if ($canData['user_id']) {
                              $getRecord = $this->CandidateReqModel->getRecord('candidate_req',array('job_id'=>$job_id))->row();
                               
                              if(!empty($getRecord)){
                                  
                            $result = $this->CandidateReqModel->update('candidate_req', $canData, array('job_id'=>$job_id));

                            if ($result) {
                                $getCompletedStatus = $this->CandidateReqModel->getRecord('job_details',array('id'=>$job_id))->row();
        
                                if($getCompletedStatus->is_completed != 3){
                                    $this->CandidateReqModel->update('job_details', array('is_completed'=>2), array('id'=>$job_id));
                                }
                                
                                $this->responseData['code']           = 200;
                                $this->responseData['status']         = 'success';
                                // $this->responseData['data']         = $result;
                                $this->responseData['candidate_req']  = $this->CandidateReqModel->getRecord('candidate_req', array('job_id' => $job_id))->row_array();
                                $this->responseData['message']        = "Updated successfully.";
                            } else {
                                $this->responseData['code']    = 401;
                                $this->responseData['status']  = 'failed';
                                $this->responseData['message'] = 'Wrong User';
                                unset($this->responseData['data']);
                            }
                              }else{
                                  $this->responseData['code'] = 404;
                                  $this->responseData['message'] = 'Not found  ';
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
    
     /**
     *  get eng level list
     */

    public function getEngLvlList()
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

                    $result = $this->CandidateReqModel->select_rec('eng_lvl', '*')->result_array();

                    if ($result) {
                        $this->responseData['code']     = 200;
                        $this->responseData['status']   = 'success';
                        $this->responseData['data']     = $result;
                        $this->responseData['message']  = "Fetched successfully.";
                    } else {
                        $this->responseData['code']    = 4001;
                        $this->responseData['status']  = 'failed';
                        $this->responseData['message'] = 'Not fetched';
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
     *  get education list
     */

    public function getExperienceList()
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

                    $result = $this->CandidateReqModel->select_rec('experience', '*')->result_array();

                    if ($result) {
                        $this->responseData['code']     = 200;
                        $this->responseData['status']   = 'success';
                        $this->responseData['data']     = $result;
                        $this->responseData['message']  = "Fetched successfully.";
                    } else {
                        $this->responseData['code']    = 4001;
                        $this->responseData['status']  = 'failed';
                        $this->responseData['message'] = 'Not fetched';
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
     *  get education list
     */

    public function getEducationList()
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

                    $result = $this->CandidateReqModel->select_rec('education', '*')->result_array();

                    if ($result) {
                        $this->responseData['code']     = 200;
                        $this->responseData['status']   = 'success';
                        $this->responseData['data']     = $result;
                        $this->responseData['message']  = "Fetched successfully.";
                    } else {
                        $this->responseData['code']    = 4001;
                        $this->responseData['status']  = 'failed';
                        $this->responseData['message'] = 'Not fetched';
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
