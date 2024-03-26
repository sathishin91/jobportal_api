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
    public function update()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {
            if (empty($json_data = json_decode(file_get_contents("php://input")))) {
                $this->responseData['code']    = 404;
                $this->responseData['status']  = 'failed';
                $this->responseData['message'] = 'Required fields are missing';
            } else {
                $json_data         = json_decode(file_get_contents("php://input"));
                $api_key           = $json_data->api_key;
                $user_id           = $json_data->user_id;
                $job_id            = $json_data->job_id;
                $gender            = $json_data->gender;
                $min_age           = $json_data->min_age;
                $max_age           = $json_data->max_age;
                $is_preference     = $json_data->is_preference;
                $qualification     = $json_data->qualification;
                $experience_type   = $json_data->experience_type;
                $min_experience    = $json_data->min_experience;
                $any_experience    = $json_data->any_experience;
                $skills            = $json_data->skills;

                //application location
                $app_location_type = $json_data->app_location_type;
                $al_country        = $json_data->al_country;
                $al_state          = $json_data->al_state;
                $al_city           = $json_data->al_city;

                $notice_period     = $json_data->notice_period;
                $language          = $json_data->language;
            }

            if ($json_data) {
                // $api_key = $this->input->post('api_key');
                $api_key = $json_data->api_key;

                if ($this->ApiCommonModel->checkApiKey($api_key)) {
                    $reqData = $json_data;
                    $reqData = (array) $reqData;

                    if (!empty($user_id  && $job_id)) {

                        $check = $this->CommonModel->getRecord('candidate_req', array('job_id' => $job_id))->row_array()['created_at'];
                        if ($check == "" && $check == NULL) {
                            //adding candidate request
                            $this->form_validation->set_data($reqData);
                            $this->form_validation->set_rules('user_id', 'User Id', 'required|trim');
                            $this->form_validation->set_rules('job_id', 'Job Id', 'required|trim');
                            $this->form_validation->set_rules('gender', 'Gender', 'required|trim');
                            $this->form_validation->set_rules('min_age', 'Minimum age', 'required|trim');
                            $this->form_validation->set_rules('max_age', 'Maximum age', 'required|trim');

                            $this->form_validation->set_rules('qualification', 'Qualification', 'required|trim');
                            $this->form_validation->set_rules('experience_type', 'Experience Type', 'required|trim');

                            if ($experience_type == 2) {
                                $this->form_validation->set_rules('min_experience', 'Minimum Experience', 'required|trim');
                            }

                            if ($experience_type == 3) {
                                $this->form_validation->set_rules('any_experience', 'Any Experience', 'required|trim');
                            }

                            $this->form_validation->set_rules('app_location_type', 'Application location type', 'required|trim');
                            $this->form_validation->set_rules('skills', 'Skills', 'required|trim');
                            $this->form_validation->set_rules('notice_period', 'Notice period', 'required|trim');
                            $this->form_validation->set_rules('language', 'Language', 'required|trim');


                            if ($this->form_validation->run() == TRUE) {
                                $canData['user_id']         = $user_id;
                                $canData['job_id']          = $job_id;
                                $canData['address_no']      = $this->CommonModel->generate_unique_string(4);
                                $canData['gender']          = $gender;
                                $canData['min_age']         = $min_age;
                                $canData['max_age']         = $max_age;
                                $canData['is_preference']   = $is_preference;
                                $canData['qualification']   = $qualification;
                                $canData['experience_type'] = $experience_type;

                                if ($experience_type == 2) {
                                    $canData['min_experience'] = $min_experience;
                                    $canData['any_experience'] = NULL;
                                } elseif ($experience_type == 3) {
                                    $canData['min_experience'] = NULL;
                                    $canData['any_experience'] = $any_experience;
                                }

                                $canData['skills']             = strtoupper($skills);
                                $canData['notice_period']      = $notice_period;
                                $canData['language']           = $language;
                                $canData['is_active']          = 1;
                                $canData['created_at']         = strtotime(date('d-m-Y'));
                                $canData['is_completed']       = 1;
                                $canData['app_location_type']  = $app_location_type;

                                //candidate preference application location
                                if ($app_location_type != 1 && $app_location_type == 2) {
                                    $alData['user_id']           = $user_id;
                                    $alData['address_no']        = $canData['address_no'];
                                    $alData['app_location_type'] = $app_location_type;
                                    $alData['app_location_name'] = "SPECIFIC";
                                    $alData['al_country']        = $al_country;
                                    $alData['al_state']          = $al_state;
                                    $alData['al_city']           = $al_city;
                                    $alData['is_active']         = 1;
                                    $alData['created_at']        = strtotime(date('d-m-Y'));
                                } elseif ($app_location_type == 1 && $app_location_type != 2) {
                                    $alData['user_id']            = $user_id;
                                    $alData['address_no']         = $canData['address_no'];
                                    $alData['app_location_type']  = $app_location_type;
                                    $alData['app_location_name']  = "ANYWHERE";
                                    $alData['is_active']          = 1;
                                    $alData['created_at']         = strtotime(date('d-m-Y'));
                                }

                                if ($canData['user_id'] && $canData['job_id']) {
                                    $getRecord = $this->CandidateReqModel->getRecord('candidate_req', array('user_id' => $user_id, 'job_id' => $job_id))->row();


                                    if (!empty($getRecord)) {

                                        $result = $this->CandidateReqModel->update('candidate_req', $canData, array('user_id' => $user_id, 'job_id' => $job_id));

                                        $result_location = $this->CandidateReqModel->insert('cp_location', $alData);

                                        if ($result) {
                                            $getCompletedStatus = $this->CandidateReqModel->getRecord('job_details', array('id' => $job_id))->row();
                                            // print_r($getCompletedStatus);
                                            // die();
                                            if ($getCompletedStatus->is_completed != 3) {
                                                $this->CandidateReqModel->update('job_details', array('is_completed' => 2), array('id' => $job_id));
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
                                    } else {
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
                            //updating candidate request
                            $this->form_validation->set_data($reqData);
                            $this->form_validation->set_rules('user_id', 'User Id', 'required|trim');
                            $this->form_validation->set_rules('job_id', 'Job Id', 'required|trim');
                            $this->form_validation->set_rules('gender', 'Gender', 'required|trim');
                            $this->form_validation->set_rules('min_age', 'Minimum age', 'required|trim');
                            $this->form_validation->set_rules('max_age', 'Maximum age', 'required|trim');

                            $this->form_validation->set_rules('qualification', 'Qualification', 'required|trim');
                            $this->form_validation->set_rules('experience_type', 'Experience Type', 'required|trim');

                            if ($experience_type == 2) {
                                $this->form_validation->set_rules('min_experience', 'Minimum Experience', 'required|trim');
                            }

                            if ($experience_type == 3) {
                                $this->form_validation->set_rules('any_experience', 'Any Experience', 'required|trim');
                            }

                            $this->form_validation->set_rules('app_location_type', 'Application location type', 'required|trim');
                            $this->form_validation->set_rules('skills', 'Skills', 'required|trim');
                            $this->form_validation->set_rules('notice_period', 'Notice period', 'required|trim');
                            $this->form_validation->set_rules('language', 'Language', 'required|trim');


                            if ($this->form_validation->run() == TRUE) {
                                $canData['user_id']         = $user_id;
                                $canData['job_id']          = $job_id;
                                $canData['address_no']      = $this->CommonModel->generate_unique_string(4);
                                $canData['gender']          = $gender;
                                $canData['min_age']         = $min_age;
                                $canData['max_age']         = $max_age;
                                $canData['is_preference']   = $is_preference;
                                $canData['qualification']   = $qualification;
                                $canData['experience_type'] = $experience_type;

                                if ($experience_type == 2) {
                                    $canData['min_experience'] = $min_experience;
                                    $canData['any_experience'] = NULL;
                                } elseif ($experience_type == 3) {
                                    $canData['min_experience'] = NULL;
                                    $canData['any_experience'] = $any_experience;
                                }

                                $canData['skills']             = strtoupper($skills);
                                $canData['notice_period']      = $notice_period;
                                $canData['language']           = $language;
                                $canData['is_active']          = 1;
                                $canData['updated_at']         = strtotime(date('d-m-Y'));
                                $canData['is_completed']       = 1;
                                $canData['app_location_type']  = $app_location_type;

                                //candidate preference application location
                                if ($app_location_type != 1 && $app_location_type == 2) {
                                    $alData['user_id']           = $user_id;
                                    $alData['address_no']        = $canData['address_no'];
                                    $alData['app_location_type'] = $app_location_type;
                                    $alData['app_location_name'] = "SPECIFIC";
                                    $alData['al_country']        = $al_country;
                                    $alData['al_state']          = $al_state;
                                    $alData['al_city']           = $al_city;
                                    $alData['is_active']         = 1;
                                    $alData['created_at']        = strtotime(date('d-m-Y'));
                                } elseif ($app_location_type == 1 && $app_location_type != 2) {
                                    $alData['user_id']            = $user_id;
                                    $alData['address_no']         = $canData['address_no'];
                                    $alData['app_location_type']  = $app_location_type;
                                    $alData['app_location_name']  = "ANYWHERE";
                                    $alData['updated_at']         = strtotime(date('d-m-Y'));
                                }

                                if ($canData['user_id'] && $canData['job_id']) {
                                    $getRecord = $this->CandidateReqModel->getRecord('candidate_req', array('user_id' => $user_id, 'job_id' => $job_id))->row();

                                    if (!empty($getRecord)) {

                                        $result = $this->CandidateReqModel->update('candidate_req', $canData, array('user_id' => $user_id, 'job_id' => $job_id));

                                        $result_location = $this->CandidateReqModel->insert('cp_location', $alData);

                                        if ($result) {
                                            $getCompletedStatus = $this->CandidateReqModel->getRecord('job_details', array('id' => $job_id))->row();
                                            // print_r($getCompletedStatus);
                                            // die();
                                            if ($getCompletedStatus->is_completed != 3) {
                                                $this->CandidateReqModel->update('job_details', array('is_completed' => 2), array('id' => $job_id));
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
                                    } else {
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
                        }
                    } else {
                        $this->responseData['code']    = 404;
                        $this->responseData['status']  = 'failed';
                        $this->responseData['message'] = 'Required param missing: user_id or job_id';
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
