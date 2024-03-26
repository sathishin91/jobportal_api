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
    public function update()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {
            if (empty($json_data = json_decode(file_get_contents("php://input")))) {
                $this->responseData['code']    = 404;
                $this->responseData['status']  = 'failed';
                $this->responseData['message'] = 'Required fields are missing';
            } else {
                $json_data    = json_decode(file_get_contents("php://input"));
                $api_key      = $json_data->api_key;
                $user_id      = $json_data->user_id;
                $job_id       = $json_data->job_id;
                $interview_type     = $json_data->interview_type; //1=walkin, 2=telephonic, 3=we will shortlist

                /*walkin interview*/
                /*walkin interview location*/
                $interview_location_type  = $json_data->interview_location_type; //1= New address, 2= Same as registered address while signup(employer)
                $il_country               = $json_data->il_country;
                $il_state                 = $json_data->il_state;
                $il_pincode               = $json_data->il_pincode;
                $il_latitude              = $json_data->il_latitude;
                $il_longitude             = $json_data->il_longitude;

                $w_interview_date_from    = $json_data->w_interview_date_from;
                $w_interview_date_to      = $json_data->w_interview_date_to;
                $w_interview_time_from    = $json_data->w_interview_time_from;
                $w_interview_time_to      = $json_data->w_interview_time_to;

                /*telephonic interview*/
                $t_interview_date         = $json_data->t_interview_date;
                $t_interview_time_from    = $json_data->t_interview_time_from;
                $t_interview_time_to      = $json_data->t_interview_time_to;
                $t_contact_person         = $json_data->t_contact_person;
                $t_contact_number         = $json_data->t_contact_number;

                /*shorlist interview*/
                $s_send_resume            = $json_data->s_send_resume;
                $s_email                  = $json_data->s_email;
            }

            if ($json_data) {
                $api_key = $json_data->api_key;

                if ($this->ApiCommonModel->checkApiKey($api_key)) {
                    $reqData = $json_data;
                    $reqData = (array) $reqData;

                    if (!empty($user_id && $job_id)) {

                        $check = $this->CommonModel->getRecord('interviewer_info', array('job_id' => $job_id))->row_array()['created_at'];

                        if ($check == "" && $check == NULL) {

                            //adding interviewer_info
                            $this->form_validation->set_data($reqData);
                            $this->form_validation->set_rules('user_id', 'User Id', 'required|trim');
                            $this->form_validation->set_rules('job_id', 'Job Id', 'required|trim');
                            $this->form_validation->set_rules('interview_type', 'Interview type', 'required|trim');

                            if ($interview_type == 1 && $interview_type != 2 && $interview_type != 3) {
                                $this->form_validation->set_rules('interview_location_type', 'Interview location type', 'required|trim');
                                $this->form_validation->set_rules('w_interview_date_from', 'Walkin interview start date', 'required|trim');
                                $this->form_validation->set_rules('w_interview_date_to', 'Walkin interview end date', 'required|trim');
                                $this->form_validation->set_rules('w_interview_time_from', 'Walkin interview start time', 'required|trim');
                                $this->form_validation->set_rules('w_interview_time_to', 'Walkin interview end time', 'required|trim');
                            } elseif ($interview_type != 1 && $interview_type == 2 && $interview_type != 3) {
                                $this->form_validation->set_rules('t_interview_date', 'Telephonic interview date', 'required|trim');
                                $this->form_validation->set_rules('t_interview_time_from', 'Telephonic interview start time', 'required|trim');
                                $this->form_validation->set_rules('t_interview_time_to', 'Telephonic interview end timeTelephonic ', 'required|trim');
                                $this->form_validation->set_rules('t_contact_person', 'Telephonic interview contact person', 'required|trim');
                                $this->form_validation->set_rules('t_contact_number', 'Telephonic contact number', 'required|trim');
                            } elseif ($interview_type != 1 && $interview_type != 2 && $interview_type == 3) {
                                $this->form_validation->set_rules('s_send_resume', 'Send resume check', 'required|trim');
                                if ($s_send_resume == 1) {
                                    $this->form_validation->set_rules('s_email', 'Email for resume', 'required|trim');
                                }
                            }

                            if ($this->form_validation->run() == TRUE) {

                                if ($interview_type == 1 && $interview_type != 2 && $interview_type != 3) {
                                    $intData['user_id']             = $user_id;
                                    $intData['job_id']              = $job_id;
                                    $intData['address_no']          = $this->CommonModel->generate_unique_string(4);
                                    $intData['interview_type']      = $interview_type;
                                    $intData['interview_type_name'] = "WALKIN";
                                    $intData['interview_location_type']    = $interview_location_type;

                                    if ($intData['interview_location_type'] == 1) {
                                        $ilData['user_id']          = $user_id;
                                        $ilData['address_no']       = $intData['address_no'];
                                        $ilData['interview_location_type'] = $interview_location_type;
                                        $ilData['interview_location_name'] = "SPECIFIC";
                                        $ilData['il_country']       = $il_country;
                                        $ilData['il_state']         = $il_state;
                                        $ilData['il_pincode']       = $il_pincode;
                                        $ilData['il_latitude']      = $il_latitude;
                                        $ilData['il_longitude']     = $il_longitude;
                                        $ilData['is_active']        = 1;
                                        $ilData['created_at']       = strtotime(date('d-m-Y'));
                                    } elseif ($intData['interview_location_type'] == 2) {
                                        $ilData['user_id']          = $user_id;
                                        $ilData['address_no']       = $intData['address_no'];
                                        $ilData['interview_location_type'] = $interview_location_type;
                                        $ilData['interview_location_name'] = "SAME ADDRESS";
                                        $ilData['same_reg_address']        = $this->CommonModel->getRecord('user', array('id' => $user_id))->row_array()['reg_address'];
                                        $ilData['is_active']               = 1;
                                        $ilData['created_at']              = strtotime(date('d-m-Y'));
                                    }

                                    $intData['t_interview_date']       = NULL;
                                    $intData['t_interview_time_from']  = NULL;
                                    $intData['t_interview_time_to']    = NULL;
                                    $intData['t_contact_person']       = NULL;
                                    $intData['t_contact_number']       = NULL;
                                    $intData['s_send_resume']          = NULL;

                                    $intData['w_interview_date_from']      = $w_interview_date_from;
                                    $intData['w_interview_date_to']        = $w_interview_date_to;
                                    $intData['w_interview_time_from']      = $w_interview_time_from;
                                    $intData['w_interview_time_to']        = $w_interview_time_to;
                                    $intData['is_active']                  = 1;
                                    $intData['created_at']                 = strtotime(date('d-m-Y'));
                                    $intData['is_completed']               = 1;
                                } elseif ($interview_type != 1 && $interview_type == 2 && $interview_type != 3) {
                                    $intData['user_id']                = $user_id;
                                    $intData['job_id']                 = $job_id;
                                    $intData['interview_type']         = $interview_type;
                                    $intData['interview_type_name']    = "TELEPHONIC";

                                    $intData['w_interview_date_from']      = NULL;
                                    $intData['w_interview_date_to']        = NULL;
                                    $intData['w_interview_time_from']      = NULL;
                                    $intData['w_interview_time_to']        = NULL;
                                    $intData['s_send_resume']              = NULL;

                                    $intData['t_interview_date']       = $t_interview_date;
                                    $intData['t_interview_time_from']  = $t_interview_time_from;
                                    $intData['t_interview_time_to']    = $t_interview_time_to;
                                    $intData['t_contact_person']       = $t_contact_person;
                                    $intData['t_contact_number']       = $t_contact_number;
                                    $intData['is_active']              = 1;
                                    $intData['created_at']             = strtotime(date('d-m-Y'));
                                    $intData['is_completed']           = 1;
                                } elseif ($interview_type != 1 && $interview_type != 2 && $interview_type == 3) {
                                    $intData['user_id']             = $user_id;
                                    $intData['job_id']              = $job_id;
                                    $intData['interview_type']      = $interview_type;
                                    $intData['interview_type_name'] = "SEND EMAIL";

                                    $intData['w_interview_date_from']      = NULL;
                                    $intData['w_interview_date_to']        = NULL;
                                    $intData['w_interview_time_from']      = NULL;
                                    $intData['w_interview_time_to']        = NULL;
                                    $intData['t_interview_date']       = NULL;
                                    $intData['t_interview_time_from']  = NULL;
                                    $intData['t_interview_time_to']    = NULL;
                                    $intData['t_contact_person']       = NULL;
                                    $intData['t_contact_number']       = NULL;

                                    $intData['s_send_resume']          = $s_send_resume;
                                    if ($intData['s_send_resume'] == 1) {
                                        $intData['s_email']        = $s_email;
                                    } else {
                                        $intData['s_email']        = NULL;
                                    }
                                    $intData['is_active']          = 1;
                                    $intData['created_at']         = strtotime(date('d-m-Y'));
                                    $intData['is_completed']       = 1;
                                }

                                if ($intData['user_id'] && $intData['job_id']) {
                                    $getRecord = $this->InterviewerInfoModel->getRecord('interviewer_info', array('user_id' => $user_id, 'job_id' => $job_id))->row();

                                    if (!empty($getRecord)) {
                                        $result = $this->InterviewerInfoModel->update('interviewer_info', $intData, array('user_id' => $user_id, 'job_id' => $job_id));

                                        if ($interview_type == 1 && $interview_type != 2 && $interview_type != 3) {
                                            $result = $this->InterviewerInfoModel->insert('ip_location', $ilData);
                                        }

                                        if ($result) {
                                            $getCompletedStatus = $this->InterviewerInfoModel->getRecord('job_details', array('id' => $job_id))->row();

                                            if ($getCompletedStatus->is_completed != 3) {
                                                $this->InterviewerInfoModel->update('job_details', array('is_completed' => 3), array('id' => $job_id));
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

                            //updating interviewer info
                            $this->form_validation->set_data($reqData);
                            $this->form_validation->set_rules('user_id', 'User Id', 'required|trim');
                            $this->form_validation->set_rules('job_id', 'Job Id', 'required|trim');
                            $this->form_validation->set_rules('interview_type', 'Interview type', 'required|trim');

                            if ($interview_type == 1 && $interview_type != 2 && $interview_type != 3) {
                                $this->form_validation->set_rules('interview_location_type', 'Interview location type', 'required|trim');
                                $this->form_validation->set_rules('w_interview_date_from', 'Walkin interview start date', 'required|trim');
                                $this->form_validation->set_rules('w_interview_date_to', 'Walkin interview end date', 'required|trim');
                                $this->form_validation->set_rules('w_interview_time_from', 'Walkin interview start time', 'required|trim');
                                $this->form_validation->set_rules('w_interview_time_to', 'Walkin interview end time', 'required|trim');
                            } elseif ($interview_type != 1 && $interview_type == 2 && $interview_type != 3) {
                                $this->form_validation->set_rules('t_interview_date', 'Telephonic interview date', 'required|trim');
                                $this->form_validation->set_rules('t_interview_time_from', 'Telephonic interview start time', 'required|trim');
                                $this->form_validation->set_rules('t_interview_time_to', 'Telephonic interview end timeTelephonic ', 'required|trim');
                                $this->form_validation->set_rules('t_contact_person', 'Telephonic interview contact person', 'required|trim');
                                $this->form_validation->set_rules('t_contact_number', 'Telephonic contact number', 'required|trim');
                            } elseif ($interview_type != 1 && $interview_type != 2 && $interview_type == 3) {
                                $this->form_validation->set_rules('s_send_resume', 'Send resume check', 'required|trim');
                                if ($s_send_resume == 1) {
                                    $this->form_validation->set_rules('s_email', 'Email for resume', 'required|trim');
                                }
                            }

                            if ($this->form_validation->run() == TRUE) {

                                if ($interview_type == 1 && $interview_type != 2 && $interview_type != 3) {
                                    $intData['user_id']             = $user_id;
                                    $intData['job_id']              = $job_id;
                                    $intData['address_no']          = $this->CommonModel->generate_unique_string(4);
                                    $intData['interview_type']      = $interview_type;
                                    $intData['interview_type_name'] = "WALKIN";
                                    $intData['interview_location_type']    = $interview_location_type;

                                    if ($intData['interview_location_type'] == 1) {
                                        $ilData['user_id']          = $user_id;
                                        $ilData['address_no']       = $intData['address_no'];
                                        $ilData['interview_location_type'] = $interview_location_type;
                                        $ilData['interview_location_name'] = "SPECIFIC";
                                        $ilData['il_country']       = $il_country;
                                        $ilData['il_state']         = $il_state;
                                        $ilData['il_pincode']       = $il_pincode;
                                        $ilData['il_latitude']      = $il_latitude;
                                        $ilData['il_longitude']     = $il_longitude;
                                        $ilData['is_active']        = 1;
                                        $ilData['created_at']       = strtotime(date('d-m-Y'));
                                    } elseif ($intData['interview_location_type'] == 2) {
                                        $ilData['user_id']          = $user_id;
                                        $ilData['address_no']       = $intData['address_no'];
                                        $ilData['interview_location_type'] = $interview_location_type;
                                        $ilData['interview_location_name'] = "SAME ADDRESS";
                                        $ilData['same_reg_address']        = $this->CommonModel->getRecord('user', array('id' => $user_id))->row_array()['reg_address'];
                                        $ilData['is_active']               = 1;
                                        $ilData['updated_at']              = strtotime(date('d-m-Y'));
                                    }

                                    $intData['t_interview_date']       = NULL;
                                    $intData['t_interview_time_from']  = NULL;
                                    $intData['t_interview_time_to']    = NULL;
                                    $intData['t_contact_person']       = NULL;
                                    $intData['t_contact_number']       = NULL;
                                    $intData['s_send_resume']          = NULL;

                                    $intData['w_interview_date_from']      = $w_interview_date_from;
                                    $intData['w_interview_date_to']        = $w_interview_date_to;
                                    $intData['w_interview_time_from']      = $w_interview_time_from;
                                    $intData['w_interview_time_to']        = $w_interview_time_to;
                                    $intData['updated_at']                 = strtotime(date('d-m-Y'));
                                    $intData['is_completed']               = 1;
                                } elseif ($interview_type != 1 && $interview_type == 2 && $interview_type != 3) {
                                    $intData['user_id']                = $user_id;
                                    $intData['job_id']                 = $job_id;
                                    $intData['interview_type']         = $interview_type;
                                    $intData['interview_type_name']    = "TELEPHONIC";

                                    $intData['w_interview_date_from']      = NULL;
                                    $intData['w_interview_date_to']        = NULL;
                                    $intData['w_interview_time_from']      = NULL;
                                    $intData['w_interview_time_to']        = NULL;
                                    $intData['s_send_resume']              = NULL;

                                    $intData['t_interview_date']       = $t_interview_date;
                                    $intData['t_interview_time_from']  = $t_interview_time_from;
                                    $intData['t_interview_time_to']    = $t_interview_time_to;
                                    $intData['t_contact_person']       = $t_contact_person;
                                    $intData['t_contact_number']       = $t_contact_number;
                                    $intData['updated_at']             = strtotime(date('d-m-Y'));
                                    $intData['is_completed']           = 1;
                                } elseif ($interview_type != 1 && $interview_type != 2 && $interview_type == 3) {
                                    $intData['user_id']             = $user_id;
                                    $intData['job_id']              = $job_id;
                                    $intData['interview_type']      = $interview_type;
                                    $intData['interview_type_name'] = "SEND EMAIL";

                                    $intData['w_interview_date_from']      = NULL;
                                    $intData['w_interview_date_to']        = NULL;
                                    $intData['w_interview_time_from']      = NULL;
                                    $intData['w_interview_time_to']        = NULL;
                                    $intData['t_interview_date']       = NULL;
                                    $intData['t_interview_time_from']  = NULL;
                                    $intData['t_interview_time_to']    = NULL;
                                    $intData['t_contact_person']       = NULL;
                                    $intData['t_contact_number']       = NULL;

                                    $intData['s_send_resume']          = $s_send_resume;
                                    if ($intData['s_send_resume'] == 1) {
                                        $intData['s_email']        = $s_email;
                                    } else {
                                        $intData['s_email']        = NULL;
                                    }
                                    $intData['updated_at']         = strtotime(date('d-m-Y'));
                                    $intData['is_completed']       = 1;
                                }

                                if ($intData['user_id'] && $intData['job_id']) {
                                    $getRecord = $this->InterviewerInfoModel->getRecord('interviewer_info', array('user_id' => $user_id, 'job_id' => $job_id))->row();

                                    if (!empty($getRecord)) {
                                        $result = $this->InterviewerInfoModel->update('interviewer_info', $intData, array('user_id' => $user_id, 'job_id' => $job_id));

                                        if ($interview_type == 1 && $interview_type != 2 && $interview_type != 3) {
                                            $result = $this->InterviewerInfoModel->insert('ip_location', $ilData);
                                        }

                                        if ($result) {
                                            $getCompletedStatus = $this->InterviewerInfoModel->getRecord('job_details', array('id' => $job_id))->row();

                                            if ($getCompletedStatus->is_completed != 3) {
                                                $this->InterviewerInfoModel->update('job_details', array('is_completed' => 3), array('id' => $job_id));
                                            }

                                            $this->responseData['code']              = 200;
                                            $this->responseData['status']            = 'success';
                                            // $this->responseData['data']         = $result;
                                            $this->responseData['interviewer_info']  = $this->InterviewerInfoModel->getRecord('interviewer_info', array('job_id' => $job_id))->row_array();
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
