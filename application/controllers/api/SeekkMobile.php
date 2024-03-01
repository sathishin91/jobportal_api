<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SeekkMobile extends CI_Controller
{
    public $responseData = array('code' => 200, 'status' => 'success', 'message' => 'Your have registered successfully'); // set API response array
    public $customerRoleId    = null;
    public $DIR         = 'assets/api/images/';
    public $DIR_doc     = 'assets/api/doc/';
    public $DIR_idproof = 'assets/api/idproof/';
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
    public function addUserInfo()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {
            $json_data = json_decode(file_get_contents("php://input"));

            $api_key    = $json_data->api_key;
            $user_id    = $json_data->user_id;
            $first_name = $json_data->first_name;
            $last_name  = $json_data->last_name;
            $email      = $json_data->email;
            $gender     = $json_data->gender;
            $dob        = $json_data->dob;
            $city       = $json_data->city;
            $img        = $json_data->img;

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
                        $this->form_validation->set_rules('gender', 'Gender', 'required|trim');
                        $this->form_validation->set_rules('dob', 'Date of Birth', 'required|trim');
                        $this->form_validation->set_rules('city', 'City', 'required|trim');

                        if ($this->form_validation->run() == TRUE) {
                            // $userData['id']          = $user_id;

                            $userData['first_name']  = $first_name;
                            $userData['last_name']   = $last_name;
                            $userData['email']       = $email;
                            $userData['gender']      = $gender;
                            $userData['dob']         = $dob;
                            $userData['city']        = $city;
                            $userData['is_active']          = 1;
                            $userData['is_verify']          = 1;
                            $userData['created_at']         = strtotime(date('d-m-Y'));
                            $userData['is_registered']      = 1;
                            $userData['is_completed']       = 1;

                            $rand  = rand(10, 10000);
                            $image = preg_replace('#^data:image/[^;]+;base64,#', '', $img);

                            $name               = $this->DIR . $rand . 'image.png';
                            $new                = file_put_contents($name, base64_decode($image));
                            $userData['user_avatar'] = $rand . 'image.png';


                            if ($user_id) {
                                $getRecord = $this->UserModel->getRecord('user', array('id' => $user_id, 'role_id' => 4))->row_array();

                                if (!empty($getRecord)) {
                                    if (empty($getRecord['user_avatar'])) {
                                        $this->UserModel->update('user', array('user_avatar' => $userData['user_avatar']), array('id' => $user_id));
                                    } else {
                                        unlink($this->DIR . $getRecord['user_avatar']);
                                    }
                                    $result = $this->UserModel->update('user', $userData, array('id' => $user_id));

                                    if ($result) {
                                        $this->responseData['code']         = 200;
                                        $this->responseData['status']       = 'success';
                                        // $this->responseData['data']         = $result;
                                        $this->responseData['img_url']   = base_url('assets/api/images/');
                                        $this->responseData['message']      = "Added successfully.";
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
     * Adding Employee Education Info
     */
    public function addEducationInfo()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {
            $json_data = json_decode(file_get_contents("php://input"));

            $api_key           = $json_data->api_key;
            $user_id           = $json_data->user_id;
            $highest_education = $json_data->highest_education;
            $college_name      = $json_data->college_name;
            $degree            = $json_data->degree;
            $specialization    = $json_data->specialization;
            $education_type    = $json_data->education_type;
            $comp_year         = $json_data->comp_year;


            if ($json_data) {
                // $api_key = $this->input->post('api_key');
                $api_key = $json_data->api_key;

                if ($this->ApiCommonModel->checkApiKey($api_key)) {
                    $reqData = $json_data;
                    $reqData = (array) $reqData;

                    if (!empty($user_id)) {

                        $this->form_validation->set_data($reqData);
                        $this->form_validation->set_rules('user_id', 'User Id', 'required|trim');
                        $this->form_validation->set_rules('highest_education', 'Highest Education', 'required|trim');

                        if ($highest_education == 3 || $highest_education == 4 || $highest_education == 5) {
                            $this->form_validation->set_rules('college_name', 'College Name', 'required|trim');
                            $this->form_validation->set_rules('degree', 'Degree', 'required|trim');
                            $this->form_validation->set_rules('specialization', 'Specialization', 'required|trim');
                            $this->form_validation->set_rules('education_type', 'Education Type', 'required|trim');
                            $this->form_validation->set_rules('comp_year', 'Completion Year', 'required|trim');
                        }



                        if ($this->form_validation->run() == TRUE) {

                            $userData['user_id']              = $user_id;
                            $userData['highest_education']    = $highest_education;
                            if ($highest_education > 2) {

                                $userData['college_name']     = $college_name;
                                $userData['degree']           = $degree;
                                $userData['specialization']   = $specialization;
                                $userData['education_type']   = $education_type;
                                $userData['comp_year']        = $comp_year;
                            }

                            $userData['is_active']          = 1;
                            $userData['is_verify']          = 1;
                            $userData['created_at']         = strtotime(date('d-m-Y'));
                            $userData['is_completed']       = 1;
                            // print_r($userData);
                            // die();

                            if ($user_id) {
                                $getRecord = $this->UserModel->getRecord('user', array('id' => $user_id, 'role_id' => 4))->row_array();
                                $getEducationRecord = $this->UserModel->getRecord('education_info', array('user_id' => $user_id))->row_array();

                                if (!empty($getRecord) && !empty($getEducationRecord)) {
                                    $result = $this->UserModel->update('education_info', $userData, array('user_id' => $user_id));

                                    if ($result) {
                                        $this->responseData['code']         = 200;
                                        $this->responseData['status']       = 'success';
                                        $this->responseData['data']         = $result;
                                        $this->responseData['message']      = "Added successfully.";
                                    } else {
                                        $this->responseData['code']    = 401;
                                        $this->responseData['status']  = 'failed';
                                        $this->responseData['message'] = 'Wrong User';
                                        unset($this->responseData['data']);
                                    }
                                } elseif (!empty($getRecord) && empty($getEducationRecord)) {

                                    $result = $this->UserModel->insert('education_info', $userData);

                                    if ($result) {
                                        $this->responseData['code']         = 200;
                                        $this->responseData['status']       = 'success';
                                        $this->responseData['data']         = $result;
                                        $this->responseData['message']      = "Added successfully.";
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
     * Adding Employee Experience Info
     */
    public function addExperienceInfo()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {
            $json_data = json_decode(file_get_contents("php://input"));

            $api_key           = $json_data->api_key;
            $user_id           = $json_data->user_id;
            $experience        = $json_data->experience;
            //if experience is 1
            $total_experience_month = $json_data->total_experience_month;
            $total_experience_year  = $json_data->total_experience_year;
            $job_title              = $json_data->job_title;
            $department             = $json_data->department;
            $category               = $json_data->category;
            $company_name           = $json_data->company_name;
            $industry               = $json_data->industry;
            $current_work           = $json_data->current_work;
            //if current_work is 1
            $current_salary         = $json_data->current_salary; //common
            $employment_type        = $json_data->employment_type; //common
            $notice_period          = $json_data->notice_period;
            $start_date             = $json_data->start_date; //common
            //if current_work is 0
            $end_date               = $json_data->end_date;

            if ($json_data) {
                // $api_key = $this->input->post('api_key');
                $api_key = $json_data->api_key;

                if ($this->ApiCommonModel->checkApiKey($api_key)) {
                    $reqData = $json_data;
                    $reqData = (array) $reqData;

                    if (!empty($user_id)) {

                        $this->form_validation->set_data($reqData);
                        $this->form_validation->set_rules('user_id', 'User Id', 'required|trim');
                        $this->form_validation->set_rules('experience', 'Experience', 'required|trim');

                        if ($experience == 1) {
                            $this->form_validation->set_rules('total_experience_month', 'Total Experience', 'required|trim');
                            $this->form_validation->set_rules('job_title', 'Job Title', 'required|trim');
                            $this->form_validation->set_rules('department', 'Department', 'required|trim');
                            $this->form_validation->set_rules('category', 'Category', 'required|trim');
                            $this->form_validation->set_rules('company_name', 'Company Name', 'required|trim');
                            $this->form_validation->set_rules('industry', 'Industry', 'required|trim');
                            $this->form_validation->set_rules('current_work', 'Current Work', 'required|trim');

                            if ($current_work == 1) {
                                $this->form_validation->set_rules('current_salary', 'Current Salary', 'required|trim');
                                $this->form_validation->set_rules('employment_type', 'Employment Type', 'required|trim');
                                $this->form_validation->set_rules('notice_period', 'Notice Period', 'required|trim');
                                $this->form_validation->set_rules('start_date', 'Start Date', 'required|trim');
                            } else {
                                $this->form_validation->set_rules('current_salary', 'Current Salary', 'required|trim');
                                $this->form_validation->set_rules('employment_type', 'Employment Type', 'required|trim');
                                $this->form_validation->set_rules('start_date', 'Start Date', 'required|trim');
                                $this->form_validation->set_rules('end_date', 'End Date', 'required|trim');
                            }
                        }

                        if ($this->form_validation->run() == TRUE) {

                            $userData['user_id']       = $user_id;
                            $userData['experience']    = $experience;

                            if ($experience == 1) {
                                $userData['total_experience_month']    = $total_experience_month;
                                $userData['total_experience_year']     = $total_experience_year;
                                $userData['job_title']           = $job_title;
                                $userData['department']          = $department;
                                $userData['category']            = $category;
                                $userData['company_name']        = $company_name;
                                $userData['industry']            = $industry;
                                $userData['current_work']        = $current_work;

                                if ($userData['current_work'] == 1) {
                                    $userData['current_salary']   = $current_salary;
                                    $userData['employment_type']  = $employment_type;
                                    $userData['notice_period']    = $notice_period;
                                    $userData['start_date']       = $start_date;
                                } else {
                                    $userData['current_salary']   = $current_salary;
                                    $userData['employment_type']  = $employment_type;
                                    $userData['start_date']       = $start_date;
                                    $userData['end_date']         = $end_date;
                                }
                            }

                            $userData['is_active']          = 1;
                            $userData['is_verify']          = 1;
                            $userData['created_at']         = strtotime(date('d-m-Y'));
                            $userData['is_completed']       = 1;
                            // print_r($userData);
                            // die();

                            if ($user_id) {
                                $getRecord = $this->UserModel->getRecord('user', array('id' => $user_id, 'role_id' => 4))->row_array();
                                $getEducationRecord = $this->UserModel->getRecord('experience_info', array('user_id' => $user_id))->row_array();
                                $getEducationRecordId = $this->UserModel->getRecord('experience_info', array('user_id' => $user_id))->row_array()['id'];

                                if (!empty($getRecord) && !empty($getEducationRecord)) {
                                    // $result = $this->UserModel->update('experience_info', $userData, array('user_id' => $user_id));
                                    $result = $this->UserModel->delete('experience_info', array('user_id' => $user_id));
                                    $userData['id'] = $getEducationRecordId;
                                    $result = $this->UserModel->insert('experience_info', $userData);

                                    if ($result) {
                                        $this->responseData['code']         = 200;
                                        $this->responseData['status']       = 'success';
                                        $this->responseData['data']         = $result;
                                        $this->responseData['message']      = "Added successfully.";
                                    } else {
                                        $this->responseData['code']    = 401;
                                        $this->responseData['status']  = 'failed';
                                        $this->responseData['message'] = 'Wrong User';
                                        unset($this->responseData['data']);
                                    }
                                } elseif (!empty($getRecord) && empty($getEducationRecord)) {

                                    $result = $this->UserModel->insert('experience_info', $userData);

                                    if ($result) {
                                        $this->responseData['code']         = 200;
                                        $this->responseData['status']       = 'success';
                                        $this->responseData['data']         = $result;
                                        $this->responseData['message']      = "Added successfully.";
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
     * Adding Employee Skills Info
     */
    public function addSkillsInfo()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {
            $json_data = json_decode(file_get_contents("php://input"));

            $api_key           = $json_data->api_key;
            $user_id           = $json_data->user_id;
            $skill             = $json_data->skill;
            $language          = $json_data->language;
            $other_lang        = $json_data->other_lang;
            $pref_work_type    = $json_data->pref_work_type;
            $pref_job_city     = $json_data->pref_job_city;

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
                        $this->form_validation->set_rules('language', 'Language', 'required|trim');
                        $this->form_validation->set_rules('pref_work_type', 'Preferred Work Type', 'required|trim');
                        $this->form_validation->set_rules('pref_job_city', 'Preferred Job City', 'required|trim');

                        if ($this->form_validation->run() == TRUE) {

                            $userData['user_id']         = $user_id;
                            $userData['skill']           = strtoupper($skill);
                            $userData['language']        = $language;
                            $userData['other_lang']      = $other_lang;
                            $userData['pref_work_type']  = $pref_work_type;
                            $userData['pref_job_city']   = $pref_job_city;

                            $userData['is_active']          = 1;
                            $userData['is_verify']          = 1;
                            $userData['created_at']         = strtotime(date('d-m-Y'));
                            $userData['is_completed']       = 1;

                            if ($user_id) {
                                $getRecord = $this->UserModel->getRecord('user', array('id' => $user_id, 'role_id' => 4))->row_array();
                                $getEducationRecord = $this->UserModel->getRecord('skill_info', array('user_id' => $user_id))->row_array();

                                if (!empty($getRecord) && !empty($getEducationRecord)) {
                                    $result = $this->UserModel->update('skill_info', $userData, array('user_id' => $user_id));

                                    if ($result) {
                                        $this->responseData['code']         = 200;
                                        $this->responseData['status']       = 'success';
                                        $this->responseData['data']         = $result;
                                        $this->responseData['message']      = "Added successfully.";
                                    } else {
                                        $this->responseData['code']    = 401;
                                        $this->responseData['status']  = 'failed';
                                        $this->responseData['message'] = 'Wrong User';
                                        unset($this->responseData['data']);
                                    }
                                } elseif (!empty($getRecord) && empty($getEducationRecord)) {

                                    $result = $this->UserModel->insert('skill_info', $userData);

                                    if ($result) {
                                        $this->responseData['code']         = 200;
                                        $this->responseData['status']       = 'success';
                                        $this->responseData['data']         = $result;
                                        $this->responseData['message']      = "Added successfully.";
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
     * Adding Employee Skills Info
     */
    public function addResume()
    {
        $json_data = json_decode(file_get_contents("php://input"));
        $api_key           = $json_data->api_key;
        $user_id           = $json_data->user_id;
        $resume            = $json_data->resume;

        if ($json_data) {
            // $api_key = $this->input->post('api_key');
            $api_key = $json_data->api_key;

            if ($this->ApiCommonModel->checkApiKey($api_key)) {
                $reqData = $json_data;
                $reqData = (array) $reqData;

                if (!empty($user_id)) {

                    $this->form_validation->set_data($reqData);
                    $this->form_validation->set_rules('user_id', 'User Id', 'required|trim');

                    if ($this->form_validation->run() == TRUE) {

                        $userData['user_id']        = $user_id;
                        $userData['resume']         = $resume;
                        $userData['uploaded_at']    = strtotime(date('d-m-Y'));
                        $userData['is_completed']   = 1;

                        $rand  = rand(10, 10000);
                        $doc = preg_replace('#^data:image/[^;]+;base64,#', '', $userData['resume']);

                        $name               = $this->DIR_doc . $rand . 'doc.pdf';
                        $new                = file_put_contents($name, base64_decode($doc));
                        $userData['resume'] = $rand . 'doc.pdf';

                        if ($user_id) {
                            $getRecord          = $this->UserModel->getRecord('user', array('id' => $user_id, 'role_id' => 4))->row_array();
                            $getEducationRecord = $this->UserModel->getRecord('doc_resume', array('user_id' => $user_id))->row_array();

                            if (!empty($getRecord) && !empty($getEducationRecord)) {
                                unlink($this->DIR_doc . $getEducationRecord['resume']);
                                $result = $this->UserModel->update('doc_resume', $userData, array('user_id' => $user_id));

                                if ($result) {
                                    $this->responseData['code']         = 200;
                                    $this->responseData['status']       = 'success';
                                    $this->responseData['resume_url']   = base_url('assets/api/doc/');
                                    // $this->responseData['data']         = $result;
                                    $this->responseData['message']      = "Updated successfully.";
                                } else {
                                    $this->responseData['code']    = 401;
                                    $this->responseData['status']  = 'failed';
                                    $this->responseData['message'] = 'Wrong User';
                                    unset($this->responseData['data']);
                                }
                            } elseif (!empty($getRecord) && empty($getEducationRecord)) {
                                $result = $this->UserModel->insert('doc_resume', $userData);
                                if ($result) {
                                    $this->responseData['code']         = 200;
                                    $this->responseData['status']       = 'success';
                                    $this->responseData['data']         = $result;
                                    $this->responseData['message']      = "Added successfully.";
                                } else {
                                    $this->responseData['code']    = 401;
                                    $this->responseData['status']  = 'failed';
                                    $this->responseData['message'] = 'Wrong User';
                                    unset($this->responseData['data']);
                                }
                            } else {
                                $this->responseData['code']    = 404;
                                $this->responseData['message'] = 'Not found!';
                                $this->responseData['status']  = 'failed';
                            }
                        } else {
                            $this->responseData['code']    = 404;
                            $this->responseData['message'] = 'Not found!';
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

        self::setOutPut();
    }

    /*
     * Check employee profile status
     */
    public function checkStatus()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {
            $json_data = json_decode(file_get_contents("php://input"));

            $api_key           = $json_data->api_key;
            $user_id           = $json_data->user_id;

            if ($json_data) {
                // $api_key = $this->input->post('api_key');
                $api_key = $json_data->api_key;

                if ($this->ApiCommonModel->checkApiKey($api_key)) {
                    $reqData = $json_data;
                    $reqData = (array) $reqData;

                    if (!empty($user_id)) {

                        $this->form_validation->set_data($reqData);
                        $this->form_validation->set_rules('user_id', 'User Id', 'required|trim');

                        if ($this->form_validation->run() == TRUE) {

                            $userData['user_id']         = $user_id;

                            if ($user_id) {

                                $getUserInfoStatus     = $this->UserModel->getRecord('user', array('id' => $user_id, 'role_id' => 4))->row_array()['is_completed'];
                                if ($getUserInfoStatus == NULL) {
                                    $getUserInfoStatus = 0;
                                }

                                $getEduInfoStatus      = $this->UserModel->getRecord('education_info', array('user_id' => $user_id))->row_array()['is_completed'];
                                if ($getEduInfoStatus == NULL) {
                                    $getEduInfoStatus = 0;
                                }

                                $getExpInfoStatus      = $this->UserModel->getRecord('experience_info', array('user_id' => $user_id))->row_array()['is_completed'];
                                if ($getExpInfoStatus == NULL) {
                                    $getExpInfoStatus = 0;
                                }
                                $getSkillsInfoStatus   = $this->UserModel->getRecord('skill_info', array('user_id' => $user_id))->row_array()['is_completed'];
                                if ($getSkillsInfoStatus == NULL) {
                                    $getSkillsInfoStatus = 0;
                                }

                                $final = $getUserInfoStatus + $getEduInfoStatus + $getExpInfoStatus + $getSkillsInfoStatus;

                                if ($final) {
                                    // $result = $this->UserModel->update('skill_info', $userData, array('user_id' => $user_id));

                                    if (!empty($final)) {
                                        $this->responseData['code']         = 200;
                                        $this->responseData['status']       = 'success';
                                        $this->responseData['completion_status']         = $final;
                                        if ($final == 1) {
                                            $this->responseData['message']      = "Basic info.";
                                        } elseif ($final == 2) {
                                            $this->responseData['message']      = "Education info.";
                                        } elseif ($final == 3) {
                                            $this->responseData['message']      = "Experience info.";
                                        } elseif ($final == 4) {
                                            $this->responseData['message']      = "Skill info.";
                                        }
                                    } else {
                                        $this->responseData['code']    = 401;
                                        $this->responseData['status']  = 'failed';
                                        $this->responseData['message'] = 'Wrong User';
                                        unset($this->responseData['data']);
                                    }
                                } else {
                                    $this->responseData['code']    = 404;
                                    $this->responseData['message'] = 'Not data found ';
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
     * get job list
     */
    public function getJobList()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {
            $json_data = json_decode(file_get_contents("php://input"));

            $api_key           = $json_data->api_key;

            if ($json_data) {
                // $api_key = $this->input->post('api_key');
                $api_key = $json_data->api_key;

                if ($this->ApiCommonModel->checkApiKey($api_key)) {
                    $reqData = $json_data;
                    $reqData = (array) $reqData;

                    if (!empty($api_key)) {

                        $this->form_validation->set_data($reqData);
                        $this->form_validation->set_rules('api_key', 'Api Key', 'required|trim');


                        if ($this->form_validation->run() == TRUE) {

                            $result = $this->CommonModel->select_rec('job_details', '*', array('is_verify' => 1))->result_array();


                            if ($result) {
                                // $result = $this->UserModel->update('skill_info', $userData, array('user_id' => $user_id));

                                $this->responseData['code']         = 200;
                                $this->responseData['status']       = 'success';
                                $this->responseData['message']      = 'Job list fetched successfully.';
                                $this->responseData['data']         = $result;
                            } else {
                                $this->responseData['code']    = 404;
                                $this->responseData['message'] = 'Not fetched successfully!';
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
                        $this->responseData['message'] = 'Required param missing: api_key!';
                    }
                } else {
                    $this->responseData['code']    = 400;
                    $this->responseData['status']  = 'failed';
                    $this->responseData['message'] = 'Invalid api key!';
                }
            } else {
                $this->responseData['code']    = 400;
                $this->responseData['status']  = 'failed';
                $this->responseData['message'] = 'Invalid request!';
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
     * get job details by job id
     */
    public function getJobDetailsByJobId()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {
            $json_data = json_decode(file_get_contents("php://input"));

            $api_key           = $json_data->api_key;
            $job_id            = $json_data->job_id;

            if ($json_data) {
                // $api_key = $this->input->post('api_key');
                $api_key = $json_data->api_key;

                if ($this->ApiCommonModel->checkApiKey($api_key)) {
                    $reqData = $json_data;
                    $reqData = (array) $reqData;

                    if (!empty($api_key)) {

                        $this->form_validation->set_data($reqData);
                        $this->form_validation->set_rules('job_id', 'Job Id', 'required|trim');

                        if ($this->form_validation->run() == TRUE) {

                            $result = $this->CommonModel->select_rec('job_details', '*', array('id' => $job_id, 'is_verify' => 1))->result_array();


                            if ($result) {
                                // $result = $this->UserModel->update('skill_info', $userData, array('user_id' => $user_id));

                                $this->responseData['code']         = 200;
                                $this->responseData['status']       = 'success';
                                $this->responseData['message']      = 'Job fetched successfully.';
                                $this->responseData['data']         = $result;
                            } else {
                                $this->responseData['code']    = 404;
                                $this->responseData['message'] = 'Not fetched successfully!';
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
                        $this->responseData['message'] = 'Required param missing: api_key!';
                    }
                } else {
                    $this->responseData['code']    = 400;
                    $this->responseData['status']  = 'failed';
                    $this->responseData['message'] = 'Invalid api key!';
                }
            } else {
                $this->responseData['code']    = 400;
                $this->responseData['status']  = 'failed';
                $this->responseData['message'] = 'Invalid request!';
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
     * get job details by job id
     */
    public function applyJob()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {
            $json_data = json_decode(file_get_contents("php://input"));

            $api_key           = $json_data->api_key;
            $job_id            = $json_data->job_id;
            $user_id           = $json_data->user_id;

            if ($json_data) {
                // $api_key = $this->input->post('api_key');
                $api_key = $json_data->api_key;

                if ($this->ApiCommonModel->checkApiKey($api_key)) {
                    $reqData = $json_data;
                    $reqData = (array) $reqData;

                    if (!empty($api_key)) {

                        $this->form_validation->set_data($reqData);
                        $this->form_validation->set_rules('job_id', 'Job Id', 'required|trim');

                        if ($this->form_validation->run() == TRUE) {
                            $checkUser = $this->CommonModel->getRecord('user', array('id' => $user_id, 'role_id' => 4))->row();
                            $checkJob = $this->CommonModel->getRecord('job_details', array('id' => $job_id))->row();

                            if ($checkUser && $checkJob) {
                                $checkResume = $this->CommonModel->getRecord('doc_resume', array('user_id' => $user_id))->row();

                                if ($checkResume) {
                                    $checkJobData = $this->CommonModel->getRecord('applied_jobs', array('job_id' => $job_id, 'user_id' => $user_id))->row();
                                    if (empty($checkJobData)) {

                                        $jobData['job_id']     = $job_id;
                                        $jobData['user_id']    = $user_id;
                                        $jobData['is_active']  = 1;
                                        $jobData['created_at'] = strtotime(date('d-m-Y'));

                                        $result = $this->CommonModel->insert('applied_jobs', $jobData);

                                        if ($result) {
                                            // $result = $this->UserModel->update('skill_info', $userData, array('user_id' => $user_id));

                                            $this->responseData['code']         = 200;
                                            $this->responseData['status']       = 'success';
                                            $this->responseData['message']      = 'Job applied successfully.';
                                            $this->responseData['data']         = $result;
                                        } else {
                                            $this->responseData['code']    = 401;
                                            $this->responseData['status']  = 'failed';
                                            $this->responseData['message'] = 'Not applied successfully!';
                                        }
                                    } else {
                                        $this->responseData['code']    = 401;
                                        $this->responseData['status']  = 'failed';
                                        $this->responseData['message'] = 'Already applied for this job!';
                                    }
                                } else {
                                    $this->responseData['code']    = 404;
                                    $this->responseData['status']  = 'failed';
                                    $this->responseData['message'] = 'Resume not found, please upload resume before applying!';
                                }
                            } else {
                                $this->responseData['code']    = 404;
                                $this->responseData['status']  = 'failed';
                                $this->responseData['message'] = 'User or Job not found!';
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
                        $this->responseData['message'] = 'Required param missing: api_key!';
                    }
                } else {
                    $this->responseData['code']    = 400;
                    $this->responseData['status']  = 'failed';
                    $this->responseData['message'] = 'Invalid api key!';
                }
            } else {
                $this->responseData['code']    = 400;
                $this->responseData['status']  = 'failed';
                $this->responseData['message'] = 'Invalid request!';
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
     * Updating Employee Basic Details
     */
    public function editBasicDetails()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {
            $json_data = json_decode(file_get_contents("php://input"));

            $api_key    = $json_data->api_key;
            $user_id    = $json_data->user_id;
            $gender     = $json_data->gender;
            $dob        = $json_data->dob;
            $email      = $json_data->email;
            $mobile     = $json_data->mobile;
            $id_proof   = $json_data->id_proof;
            $id_upload  = $json_data->id_upload;


            if ($json_data) {
                // $api_key = $this->input->post('api_key');
                $api_key = $json_data->api_key;

                if ($this->ApiCommonModel->checkApiKey($api_key)) {
                    $reqData = $json_data;
                    $reqData = (array) $reqData;

                    if (!empty($user_id)) {

                        $this->form_validation->set_data($reqData);
                        $this->form_validation->set_rules('user_id', 'User Id', 'required|trim');

                        if ($this->form_validation->run() == TRUE) {
                            // $userData['id']          = $user_id;
                            $userData['gender']      = $gender;
                            $userData['dob']         = $dob;
                            $userData['email']       = $email;
                            $userData['mobile']      = $mobile;
                            $userData['id_proof']    = $id_proof;
                            $userData['id_upload']   = $id_upload;
                            $userData['updated_at']         = strtotime(date('d-m-Y'));

                            $rand  = rand(10, 10000);
                            $doc   = preg_replace('#^data:image/[^;]+;base64,#', '', $userData['id_upload']);

                            $name                  = $this->DIR_idproof . $rand . 'doc.pdf';
                            $new                   = file_put_contents($name, base64_decode($doc));
                            $userData['id_upload'] = $rand . 'doc.pdf';

                            if ($user_id) {
                                $getRecord = $this->UserModel->getRecord('user', array('id' => $user_id, 'role_id' => 4))->row_array();
                                // print_r($userData);
                                // die();

                                if (!empty($getRecord)) {
                                    if (empty($getRecord['id_upload'])) {
                                        $result = $this->UserModel->update('user', $userData, array('id' => $user_id));
                                    } else {
                                        unlink($this->DIR_idproof . $getRecord['id_upload']);
                                        $result = $this->UserModel->update('user', $userData, array('id' => $user_id));
                                    }

                                    if ($result) {
                                        $this->responseData['code']         = 200;
                                        $this->responseData['status']       = 'success';
                                        // $this->responseData['data']         = $result;
                                        $this->responseData['idproof_url']  = base_url('assets/api/idproof/');
                                        $this->responseData['message']      = "Updated successfully.";
                                    } else {
                                        $this->responseData['code']    = 401;
                                        $this->responseData['status']  = 'failed';
                                        $this->responseData['message'] = 'Not updated successfully.';
                                        unset($this->responseData['data']);
                                    }
                                } else {
                                    $this->responseData['code']    = 404;
                                    $this->responseData['message'] = 'User(Employee) not found!';
                                    $this->responseData['status']  = 'failed';
                                }
                            } else {
                                $this->responseData['code']    = 404;
                                $this->responseData['message'] = 'Not found!';
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
     * show employee resume 
     */
    public function showResume()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {
            $json_data = json_decode(file_get_contents("php://input"));

            $api_key    = $json_data->api_key;
            $user_id    = $json_data->user_id;

            if ($json_data) {
                // $api_key = $this->input->post('api_key');
                $api_key = $json_data->api_key;

                if ($this->ApiCommonModel->checkApiKey($api_key)) {
                    $reqData = $json_data;
                    $reqData = (array) $reqData;

                    if (!empty($user_id)) {

                        $this->form_validation->set_data($reqData);
                        $this->form_validation->set_rules('user_id', 'User Id', 'required|trim');

                        if ($this->form_validation->run() == TRUE) {

                            $result = $this->CommonModel->getRecord('doc_resume', array('user_id' => $user_id))->row();

                            if ($result->resume) {
                                $this->responseData['code']         = 200;
                                $this->responseData['status']       = 'success';
                                // $this->responseData['data']         = $result;
                                $this->responseData['message']      = "Resume available.";
                                $this->responseData['doc_url']      = base_url('assets/api/doc/' . $result->resume);
                            } else {
                                $this->responseData['code']    = 404;
                                $this->responseData['status']  = 'failed';
                                $this->responseData['message'] = 'Resume not available.';
                                unset($this->responseData['data']);
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
     * Updating mployee resume
     */
    public function updateResume()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {
            $json_data = json_decode(file_get_contents("php://input"));

            $api_key    = $json_data->api_key;
            $user_id    = $json_data->user_id;
            $resume     = $json_data->resume;


            if ($json_data) {
                // $api_key = $this->input->post('api_key');
                $api_key = $json_data->api_key;

                if ($this->ApiCommonModel->checkApiKey($api_key)) {
                    $reqData = $json_data;
                    $reqData = (array) $reqData;

                    if (!empty($user_id)) {

                        $this->form_validation->set_data($reqData);
                        $this->form_validation->set_rules('user_id', 'User Id', 'required|trim');
                        $this->form_validation->set_rules('resume', 'Resume', 'required|trim');


                        if ($this->form_validation->run() == TRUE) {
                            // $userData['id']          = $user_id;
                            $userData['resume']         = $resume;
                            $userData['uploaded_at']    = strtotime(date('d-m-Y'));

                            $rand  = rand(10, 10000);
                            $doc   = preg_replace('#^data:image/[^;]+;base64,#', '', $userData['resume']);

                            $name                  = $this->DIR_doc . $rand . 'doc.pdf';
                            $new                   = file_put_contents($name, base64_decode($doc));
                            $userData['resume']    = $rand . 'doc.pdf';

                            if ($user_id) {
                                $getRecord = $this->CommonModel->getRecord('doc_resume', array('user_id' => $user_id))->row();
                                // print_r($getRecord->resume);
                                // die();

                                if (!empty($getRecord)) {
                                    if (empty($getRecord->resume)) {
                                        $result = $this->CommonModel->update('doc_resume', $userData, array('user_id' => $user_id));
                                    } else {
                                        unlink($this->DIR_doc . $getRecord->resume);
                                        $result = $this->CommonModel->update('doc_resume', $userData, array('user_id' => $user_id));
                                    }

                                    if ($result) {
                                        $this->responseData['code']         = 200;
                                        $this->responseData['status']       = 'success';
                                        // $this->responseData['data']         = $result;
                                        $this->responseData['doc_url']      = base_url('assets/api/doc/');
                                        $this->responseData['message']      = "Updated successfully.";
                                    } else {
                                        $this->responseData['code']    = 401;
                                        $this->responseData['status']  = 'failed';
                                        $this->responseData['message'] = 'Not updated successfully!';
                                        unset($this->responseData['data']);
                                    }
                                } else {
                                    $this->responseData['code']    = 404;
                                    $this->responseData['message'] = 'User(Employee) not found!';
                                    $this->responseData['status']  = 'failed';
                                }
                            } else {
                                $this->responseData['code']    = 404;
                                $this->responseData['message'] = 'Not found!';
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
     * Updating Employee Skills Info
     */
    public function editSkillsInfo()
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

                        if ($this->form_validation->run() == TRUE) {

                            $userData['user_id']         = $user_id;
                            $userData['skill']           = strtoupper($skill);
                            $userData['updated_at']      = strtotime(date('d-m-Y'));

                            if ($user_id) {
                                $getRecord = $this->UserModel->getRecord('user', array('id' => $user_id, 'role_id' => 4))->row_array();
                                $getEducationRecord = $this->UserModel->getRecord('skill_info', array('user_id' => $user_id))->row_array();

                                if (!empty($getRecord) && !empty($getEducationRecord)) {
                                    $result = $this->UserModel->update('skill_info', $userData, array('user_id' => $user_id));

                                    if ($result) {
                                        $this->responseData['code']         = 200;
                                        $this->responseData['status']       = 'success';
                                        // $this->responseData['data']         = $result;
                                        $this->responseData['message']      = "Updated successfully.";
                                    } else {
                                        $this->responseData['code']    = 401;
                                        $this->responseData['status']  = 'failed';
                                        $this->responseData['message'] = 'Not updated successfully!';
                                        unset($this->responseData['data']);
                                    }
                                } elseif (!empty($getRecord) && empty($getEducationRecord)) {

                                    $result = $this->UserModel->insert('skill_info', $userData);

                                    if ($result) {
                                        $this->responseData['code']         = 200;
                                        $this->responseData['status']       = 'success';
                                        // $this->responseData['data']         = $result;
                                        $this->responseData['message']      = "Updated successfully.";
                                    } else {
                                        $this->responseData['code']    = 401;
                                        $this->responseData['status']  = 'failed';
                                        $this->responseData['message'] = 'Not updated successfully!';
                                        unset($this->responseData['data']);
                                    }
                                } else {
                                    $this->responseData['code'] = 404;
                                    $this->responseData['status']  = 'failed';
                                    $this->responseData['message'] = 'User or skills info not found!';
                                }
                            } else {
                                $this->responseData['code'] = 404;
                                $this->responseData['status']  = 'failed';
                                $this->responseData['message'] = 'Not found user id';
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
     * Updating Employee Education Info
     */
    public function editEducationInfo()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {
            $json_data = json_decode(file_get_contents("php://input"));

            $api_key           = $json_data->api_key;
            $user_id           = $json_data->user_id;
            $highest_education = $json_data->highest_education;
            $college_name      = $json_data->college_name;
            $degree            = $json_data->degree;
            $specialization    = $json_data->specialization;
            $education_type    = $json_data->education_type;
            $comp_year         = $json_data->comp_year;


            if ($json_data) {
                // $api_key = $this->input->post('api_key');
                $api_key = $json_data->api_key;

                if ($this->ApiCommonModel->checkApiKey($api_key)) {
                    $reqData = $json_data;
                    $reqData = (array) $reqData;

                    if (!empty($user_id)) {

                        $this->form_validation->set_data($reqData);
                        $this->form_validation->set_rules('user_id', 'User Id', 'required|trim');


                        if ($this->form_validation->run() == TRUE) {

                            $userData['user_id']              = $user_id;
                            $userData['highest_education']    = $highest_education;

                            if ($highest_education >= 3) {

                                $userData['college_name']     = $college_name;
                                $userData['degree']           = $degree;
                                $userData['specialization']   = $specialization;
                                $userData['education_type']   = $education_type;
                                $userData['comp_year']        = $comp_year;
                            } else {
                                $userData['college_name']     = NULL;
                                $userData['degree']           = NULL;
                                $userData['specialization']   = NULL;
                                $userData['education_type']   = NULL;
                                $userData['comp_year']        = NULL;
                            }

                            $userData['updated_at']         = strtotime(date('d-m-Y'));

                            if ($user_id) {
                                $getRecord = $this->UserModel->getRecord('user', array('id' => $user_id, 'role_id' => 4))->row_array();
                                $getEducationRecord = $this->UserModel->getRecord('education_info', array('user_id' => $user_id))->row_array();

                                if (!empty($getRecord) && !empty($getEducationRecord)) {
                                    $result = $this->UserModel->update('education_info', $userData, array('user_id' => $user_id));

                                    if ($result) {
                                        $this->responseData['code']         = 200;
                                        $this->responseData['status']       = 'success';
                                        // $this->responseData['data']         = $result;
                                        $this->responseData['message']      = "Updated successfully.";
                                    } else {
                                        $this->responseData['code']    = 401;
                                        $this->responseData['status']  = 'failed';
                                        $this->responseData['message'] = 'Not updated successfully!';
                                        unset($this->responseData['data']);
                                    }
                                } elseif (!empty($getRecord) && empty($getEducationRecord)) {

                                    $result = $this->UserModel->insert('education_info', $userData);

                                    if ($result) {
                                        $this->responseData['code']         = 200;
                                        $this->responseData['status']       = 'success';
                                        // $this->responseData['data']         = $result;
                                        $this->responseData['message']      = "Updated successfully.";
                                    } else {
                                        $this->responseData['code']    = 401;
                                        $this->responseData['status']  = 'failed';
                                        $this->responseData['message'] = 'Npt updated successfully.';
                                        unset($this->responseData['data']);
                                    }
                                } else {
                                    $this->responseData['code'] = 404;
                                    $this->responseData['message'] = 'User or education info not found!';
                                    $this->responseData['status']  = 'failed';
                                }
                            } else {
                                $this->responseData['code']   = 404;
                                $this->responseData['message'] = 'Not found user id!';
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
                $this->responseData['message'] = 'Invalid request!';
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
     * Updating Employee job preference
     */
    public function editJobPreference()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {
            $json_data = json_decode(file_get_contents("php://input"));

            $api_key           = $json_data->api_key;
            $user_id           = $json_data->user_id;
            $pref_work_type    = $json_data->pref_work_type;
            $pref_job_city     = $json_data->pref_job_city;

            if ($json_data) {
                // $api_key = $this->input->post('api_key');
                $api_key = $json_data->api_key;

                if ($this->ApiCommonModel->checkApiKey($api_key)) {
                    $reqData = $json_data;
                    $reqData = (array) $reqData;

                    if (!empty($user_id)) {

                        $this->form_validation->set_data($reqData);
                        $this->form_validation->set_rules('user_id', 'User Id', 'required|trim');

                        if ($this->form_validation->run() == TRUE) {

                            $userData['user_id']         = $user_id;
                            $userData['pref_work_type']  = $pref_work_type;
                            $userData['pref_job_city']   = strtoupper($pref_job_city);
                            $userData['updated_at']      = strtotime(date('d-m-Y'));

                            if ($user_id) {
                                $getRecord = $this->UserModel->getRecord('user', array('id' => $user_id, 'role_id' => 4))->row_array();
                                $getEducationRecord = $this->UserModel->getRecord('skill_info', array('user_id' => $user_id))->row_array();

                                if (!empty($getRecord) && !empty($getEducationRecord)) {
                                    $result = $this->UserModel->update('skill_info', $userData, array('user_id' => $user_id));

                                    if ($result) {
                                        $this->responseData['code']         = 200;
                                        $this->responseData['status']       = 'success';
                                        // $this->responseData['data']         = $result;
                                        $this->responseData['message']      = "Updated successfully.";
                                    } else {
                                        $this->responseData['code']    = 401;
                                        $this->responseData['status']  = 'failed';
                                        $this->responseData['message'] = 'Not updated successfully!';
                                        unset($this->responseData['data']);
                                    }
                                } elseif (!empty($getRecord) && empty($getEducationRecord)) {

                                    $result = $this->UserModel->insert('skill_info', $userData);

                                    if ($result) {
                                        $this->responseData['code']         = 200;
                                        $this->responseData['status']       = 'success';
                                        // $this->responseData['data']         = $result;
                                        $this->responseData['message']      = "Updated successfully.";
                                    } else {
                                        $this->responseData['code']    = 401;
                                        $this->responseData['status']  = 'failed';
                                        $this->responseData['message'] = 'Not updated successfully!';
                                        unset($this->responseData['data']);
                                    }
                                } else {
                                    $this->responseData['code'] = 404;
                                    $this->responseData['status']  = 'failed';
                                    $this->responseData['message'] = 'User or job preference not found!';
                                }
                            } else {
                                $this->responseData['code'] = 404;
                                $this->responseData['status']  = 'failed';
                                $this->responseData['message'] = 'Not found user id';
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
     * get user profie details by user id
     */
    public function getUserProfileByUserId()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {
            $json_data = json_decode(file_get_contents("php://input"));

            $api_key           = $json_data->api_key;
            $user_id           = $json_data->user_id;

            if ($json_data) {
                // $api_key = $this->input->post('api_key');
                $api_key = $json_data->api_key;

                if ($this->ApiCommonModel->checkApiKey($api_key)) {
                    $reqData = $json_data;
                    $reqData = (array) $reqData;

                    if (!empty($api_key)) {

                        $this->form_validation->set_data($reqData);
                        $this->form_validation->set_rules('user_id', 'User Id', 'required|trim');

                        if ($this->form_validation->run() == TRUE) {

                            $result = $this->CommonModel->select_rec('user', '*', array('id' => $user_id, 'role_id' => 4))->result_array();


                            if ($result) {
                                // $result = $this->UserModel->update('skill_info', $userData, array('user_id' => $user_id));

                                $this->responseData['code']         = 200;
                                $this->responseData['status']       = 'success';
                                $this->responseData['message']      = 'Profile fetched successfully.';
                                $this->responseData['data']         = $result;
                            } else {
                                $this->responseData['code']    = 404;
                                $this->responseData['message'] = 'Not fetched successfully!';
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
                        $this->responseData['message'] = 'Required param missing: user_id!';
                    }
                } else {
                    $this->responseData['code']    = 400;
                    $this->responseData['status']  = 'failed';
                    $this->responseData['message'] = 'Invalid api key!';
                }
            } else {
                $this->responseData['code']    = 400;
                $this->responseData['status']  = 'failed';
                $this->responseData['message'] = 'Invalid request!';
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
