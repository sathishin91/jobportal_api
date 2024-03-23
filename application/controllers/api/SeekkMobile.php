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
                        // $this->form_validation->set_rules('last_name', 'Last Name', 'required|trim');
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

                            if ($img) {
                                $rand  = rand(10, 10000);
                                $image = preg_replace('#^data:image/[^;]+;base64,#', '', $img);

                                $name               = $this->DIR . $rand . 'image.png';
                                $new                = file_put_contents($name, base64_decode($image));
                                $userData['user_avatar'] = $rand . 'image.png';
                            } else {
                                $userData['user_avatar'] = NULL;
                            }

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
                                        if ($userData['user_avatar']) {
                                            $this->responseData['img_url']  = base_url('assets/api/images/' . $userData['user_avatar']);
                                        } else {
                                            $this->responseData['img_url']  = NULL;
                                        }
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
                                if (empty($getEducationRecord)) {
                                    $getEducationRecordId = '';
                                } else {
                                    $getEducationRecordId = $this->UserModel->getRecord('experience_info', array('user_id' => $user_id))->row_array()['id'];
                                }
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
     * Add Documents
     */
    public function addDocuments()
    {
        $json_data = json_decode(file_get_contents("php://input"));
        $api_key           = $json_data->api_key;
        $user_id           = $json_data->user_id;
        $resumes           = $json_data->resumes;

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

                        if ($user_id) {
                            $getRecord          = $this->UserModel->getRecord('user', array('id' => $user_id, 'role_id' => 4))->row_array();
                            $getDocCount = $this->UserModel->countRecord('documents', array('user_id' => $user_id));
                            $resumeCount = count($resumes);

                            if (!empty($getRecord)) {
                                if ($getDocCount + $resumeCount <= 5) {
                                    foreach ($resumes as $resume) {
                                        $userData['user_id']        = $user_id;
                                        $userData['document']       = $resume;
                                        $userData['uploaded_at']    = strtotime(date('d-m-Y'));

                                        $rand  = rand(10, 10000);
                                        $doc = preg_replace('#^data:image/[^;]+;base64,#', '', $userData['document']);

                                        $name                 = $this->DIR_doc . $rand . 'doc.pdf';
                                        $new                  = file_put_contents($name, base64_decode($doc));
                                        $userData['document'] = $rand . 'doc.pdf';

                                        $res = $this->CommonModel->insert('documents', $userData);
                                        if ($res) {
                                            $this->responseData['code']         = 200;
                                            $this->responseData['status']       = 'success';
                                            $this->responseData['message']      = "Uploaded successfully.";
                                            $this->responseData['data']         = $res;
                                        } else {
                                            $this->responseData['code']    = 401;
                                            $this->responseData['status']  = 'failed';
                                            $this->responseData['message'] = 'Not uploaded successfully.';
                                            unset($this->responseData['data']);
                                        }
                                    }
                                } else {
                                    $this->responseData['code']    = 401;
                                    $this->responseData['message'] = 'Document count reach, plz remove the doc and try again!';
                                    $this->responseData['status']  = 'failed';
                                }
                            } else {
                                $this->responseData['code']    = 404;
                                $this->responseData['message'] = 'User not found!';
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
                                    $this->responseData['resume_url']   = base_url('assets/api/doc/');
                                    // $this->responseData['data']         = $result;
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

                                $getUserInfoStatus     = $this->UserModel->getRecord('user', array('id' => $user_id, 'role_id' => 4))->row();

                                if ($getUserInfoStatus == NULL) {
                                    $getUserInfoStatus = 0;
                                } else {
                                    $getUserInfoStatus = $getUserInfoStatus->is_completed;
                                }



                                $getEduInfoStatus      = $this->UserModel->getRecord('education_info', array('user_id' => $user_id))->row();
                                if ($getEduInfoStatus == NULL) {
                                    $getEduInfoStatus = 0;
                                } else {
                                    $getEduInfoStatus = $getEduInfoStatus->is_completed;
                                }


                                $getExpInfoStatus      = $this->UserModel->getRecord('experience_info', array('user_id' => $user_id))->row();
                                if ($getExpInfoStatus == NULL) {
                                    $getExpInfoStatus = 0;
                                } else {
                                    $getExpInfoStatus = $getExpInfoStatus->is_completed;
                                }


                                $getSkillsInfoStatus   = $this->UserModel->getRecord('skill_info', array('user_id' => $user_id))->row();

                                if ($getSkillsInfoStatus == NULL) {
                                    $getSkillsInfoStatus = 0;
                                } else {
                                    $getSkillsInfoStatus = $getSkillsInfoStatus->is_completed;
                                }

                                $final = $getUserInfoStatus + $getEduInfoStatus + $getExpInfoStatus + $getSkillsInfoStatus;

                                if ($final == 0) {
                                    $this->responseData['code']         = 200;
                                    $this->responseData['status']       = 'success';
                                    $this->responseData['completion_status']  = $final;
                                    $this->responseData['message']      = "No info found!";
                                } elseif ($final > 0) {
                                    $this->responseData['code']         = 200;
                                    $this->responseData['status']       = 'success';
                                    $this->responseData['completion_status']  = $final;
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

                            $result = $this->CommonModel->select_rec('job_details', '*')->result_array();

                            if ($result) {
                                // $result = $this->UserModel->update('skill_info', $userData, array('user_id' => $user_id));

                                $this->responseData['code']         = 200;
                                $this->responseData['status']       = 'success';
                                $this->responseData['message']      = 'Job list fetched successfully.';
                                // $this->responseData['logo_url']     =  base_url('assets/api/logo/');
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

                            $val = 'job_details.*,
                            job_location.location_type,job_location.location_type_name,job_location.wo_place,job_location.wo_city,job_location.wh_address,job_location.wh_address2,job_location.wh_city,
                            industry.industry_name,
                            department.department_name,
                            role.role_name,
                            user.company_name
                           ';

                            $join = array(
                                array('table' => 'job_location', 'condition' => 'job_location.address_no = job_details.address_no', 'jointype' => 'LEFT JOIN'),
                                array('table' => 'industry', 'condition' => 'industry.id = job_details.industry', 'jointype' => 'LEFT JOIN'),
                                array('table' => 'department', 'condition' => 'department.id = job_details.department', 'jointype' => 'LEFT JOIN'),
                                array('table' => 'role', 'condition' => 'role.id = job_details.role', 'jointype' => 'LEFT JOIN'),
                                array('table' => 'user', 'condition' => 'user.id = job_details.user_id', 'jointype' => 'LEFT JOIN'),

                            );

                            $where = array('job_details.id' => $job_id);

                            $result = $this->CommonModel->get_join('job_details', $val, $join, $where, $order_by = 'job_details.id', $order = '', $limit = '', $offset = '', $distinct = '', $likearray = null, $groupby = '', $whereinvalue = '', $whereinarray = '', $find_in_set = '')->row_array();

                            if ($result) {

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
                                        $this->responseData['doc_url']      = base_url('assets/api/doc/' . $userData['resume']);
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



                            // $val = 'user.user_avatar,user.first_name,user.last_name,user.dob,user.email,user.mobile,user.id_proof,user.created_at,user.updated_at,user.is_active,user.is_verify,
                            //     skill_info.skill,
                            //     education_info.highest_education,education_info.college_name,education_info.degree,education_info.specialization,education_info.education_type,education_info.comp_year,
                            //     skill_info.pref_work_type,skill_info.pref_job_city';

                            // $join = array(
                            //     // array('table' => 'doc_resume', 'condition' => 'doc_resume.user_id = user.id', 'jointype' => 'LEFT JOIN'),
                            //     array('table' => 'skill_info', 'condition' => 'skill_info.user_id = user.id', 'jointype' => 'LEFT JOIN'),
                            //     array('table' => 'education_info', 'condition' => 'education_info.user_id = user.id', 'jointype' => 'LEFT JOIN'),
                            // );

                            // $likearray = null;

                            // if ($userData['user_id'] && $userData['user_id'] != '') {
                            //     $likearray['check_in.user_id'] = $userData['user_id'];
                            // }
                            // $user_id =
                            // $whereCompleted  = array('user.id' => $user_id, 'user.role_id' => 4);
                            // $result = $this->CommonModel->get_join('user', $val, $join, $whereCompleted, $order_by = 'user.id', $order = 'ASC', $limit = '', $offset = '', $distinct = '', $likearray = null, $groupby = '', $whereinvalue = '', $whereinarray = '', $find_in_set = '')->row();
                            // print_r($result);
                            // die();

                            // $getUserAvatar = ($this->CommonModel->getRecord('user', array('id' => $user_id))->row()) ? base_url('assets/api/images/' . $user_avatar->user_avatar) : NULL;

                            $getBasicDetails = $this->CommonModel->getRecord('user', array('id' => $user_id))->row();

                            // print_r($getBasicDetails->is_active);
                            // die();

                            $getSkillsInfo = $this->CommonModel->getRecord('skill_info', array('user_id' => $user_id))->row();

                            $getEducationInfo = $this->CommonModel->getRecord('education_info', array('user_id' => $user_id))->row();

                            $getExperienceInfo = $this->CommonModel->getRecord('experience_info', array('user_id' => $user_id))->row();

                            if ($getExperienceInfo == NULL) {
                                $getExperienceInfo == "";
                            }

                            // print_r($getExperienceInfo);
                            // die();

                            // $checkSkillsInfo    = $this->CommonModel->select('skill_info', array('user_id' => $user_id))->row();
                            // $checkEducationInfo = $this->CommonModel->getRecord('education_info', array('user_id' => $user_id))->row();

                            if ($getBasicDetails || $getSkillsInfo || $getEducationInfo || $getExperienceInfo) {

                                $this->responseData['code']         = 200;
                                $this->responseData['status']       = 'success';
                                $this->responseData['message']      = 'Profile fetched successfully.';
                                // $this->responseData['data']         = [

                                //     'user_avatar'          => ($user_avatar = $this->CommonModel->getRecord('user', array('id' => $user_id))->row()) ? base_url('assets/api/images/' . $user_avatar->user_avatar) : NULL,

                                //     'first_name'      => $result->first_name,
                                //     'last_name'       => $result->last_name,
                                //     'dob'             => $result->dob,
                                //     'email'           => $result->email,
                                //     'mobile'          => $result->mobile,
                                //     'id_proof'        => $result->id_proof,
                                //     // 'resume'          => base_url('assets/api/doc/' . $result->resume),
                                //     'resume'          => ($resume = $this->CommonModel->getRecord('doc_resume', array('user_id' => $user_id))->row()) ? base_url('assets/api/doc/' . $resume->resume) : NULL,
                                //     'skill'           => $result->skill,
                                //     'highest_education'  => $result->highest_education,
                                //     'college_name'    => $result->college_name,
                                //     'degree'          => $result->degree,
                                //     'specialization'  => $result->specialization,
                                //     'education_type'  => $result->education_type,
                                //     'comp_year'       => $result->comp_year,
                                //     'pref_work_type'  => $result->pref_work_type, 'pref_job_city'   => $result->pref_job_city,
                                //     'is_active'       => $result->is_active,
                                //     'is_verify'       => $result->is_verify,
                                //     'created_at'      => $result->created_at,
                                //     'updated_at'      => $result->updated_at,
                                // ];
                                $user_avatar = $this->CommonModel->getRecord('user', array('id' => $user_id))->row_array()['user_avatar'];
                                if ($user_avatar) {
                                    $this->responseData['user_avatar'] = base_url('assets/api/images/' . $user_avatar);
                                } else {
                                    $this->responseData['user_avatar'] = NULL;
                                }
                                $this->responseData['basic_details'] = [
                                    'first_name'      => ($getBasicDetails) ? $getBasicDetails->first_name : NULL,
                                    'last_name'       => ($getBasicDetails) ? $getBasicDetails->last_name : NULL,
                                    'dob'             => ($getBasicDetails) ? $getBasicDetails->dob : NULL,
                                    'gender'          => ($getBasicDetails) ? $getBasicDetails->gender : NULL,
                                    'email'           => ($getBasicDetails) ? $getBasicDetails->email : NULL,
                                    'mobile'          => ($getBasicDetails) ? $getBasicDetails->mobile : NULL,
                                    'id_proof'        => ($getBasicDetails) ? $getBasicDetails->id_proof : NULL,
                                    'is_active'       => ($getBasicDetails) ? $getBasicDetails->is_active : NULL,
                                    'is_verify'       => ($getBasicDetails) ? $getBasicDetails->is_verify : NULL,
                                ];

                                $this->responseData['resume'] = ($resume = $this->CommonModel->getRecord('doc_resume', array('user_id' => $user_id))->row()) ? base_url('assets/api/doc/' . $resume->resume) : NULL;

                                $this->responseData['skill_info'] = [
                                    'skill'           => ($getSkillsInfo) ? $getSkillsInfo->skill : NULL,
                                ];

                                $this->responseData['education_info'] = [
                                    'highest_education'  => ($getEducationInfo) ? $getEducationInfo->highest_education : NULL,
                                    'college_name'       => ($getEducationInfo) ? $getEducationInfo->college_name : NULL,
                                    'degree'             => ($getEducationInfo) ? $getEducationInfo->degree : NULL,
                                    'specialization'     => ($getEducationInfo) ? $getEducationInfo->specialization : NULL,
                                    'education_type'     => ($getEducationInfo) ? $getEducationInfo->education_type : NULL,
                                    'comp_year'          => ($getEducationInfo) ? $getEducationInfo->comp_year : NULL,
                                ];

                                $this->responseData['job_info'] = [
                                    'pref_work_type'    => ($getSkillsInfo) ? $getSkillsInfo->pref_work_type : NULL,
                                    'pref_job_city'     => ($getSkillsInfo) ? $getSkillsInfo->pref_job_city : NULL,
                                ];

                                if ($getExperienceInfo != "") {
                                    if ($getExperienceInfo->experience == 1) {
                                        $this->responseData['experience_info'] = [
                                            'experience' => 1,
                                            'total_experience_month'    => ($getExperienceInfo) ? $getExperienceInfo->total_experience_month : NULL,
                                            'total_experience_year'     => ($getExperienceInfo) ? $getExperienceInfo->total_experience_year : NULL,
                                            'job_title'     => ($getExperienceInfo) ? $getExperienceInfo->job_title : NULL,
                                            'department'     => ($getExperienceInfo) ? $getExperienceInfo->department : NULL,
                                            'category'     => ($getExperienceInfo) ? $getExperienceInfo->category : NULL,
                                            'company_name'     => ($getExperienceInfo) ? $getExperienceInfo->company_name : NULL,
                                            'industry'     => ($getExperienceInfo) ? $getExperienceInfo->industry : NULL,
                                            'current_work'     => ($getExperienceInfo) ? $getExperienceInfo->current_work : NULL,
                                            'current_salary'     => ($getExperienceInfo) ? $getExperienceInfo->current_salary : NULL,
                                            'employment_type'     => ($getExperienceInfo) ? $getExperienceInfo->employment_type : NULL,
                                            'notice_period'     => ($getExperienceInfo) ? $getExperienceInfo->notice_period : NULL,
                                            'start_date'     => ($getExperienceInfo) ? $getExperienceInfo->start_date : NULL,
                                            'end_date'     => ($getExperienceInfo) ? $getExperienceInfo->end_date : NULL,

                                        ];
                                    } else {
                                        $this->responseData['experience_info'] = [
                                            'experience' => 0
                                        ];
                                    }
                                } else {
                                    $this->responseData['experience_info'] = [];
                                }
                            } else {
                                $this->responseData['code']    = 404;
                                $this->responseData['message'] = 'No user data found!';
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

    /*
     * search job by skills
     */
    public function searchJobBySkill()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {
            $json_data = json_decode(file_get_contents("php://input"));

            $api_key           = $json_data->api_key;
            $skill             = $json_data->skill;

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
                            $val = 'job_details.*,
                            candidate_req.job_id, candidate_req.skills,
                            job_location.location_type,job_location.location_type_name,job_location.wo_place,job_location.wo_city,job_location.wh_address,job_location.wh_address2,job_location.wh_city,
                            industry.industry_name,
                            department.department_name,
                            role.role_name,
                            user.company_name
                           ';

                            $join = array(
                                array('table' => 'job_details', 'condition' => 'job_details.id = candidate_req.job_id', 'jointype' => 'LEFT JOIN'),
                                array('table' => 'job_location', 'condition' => 'job_location.address_no = job_details.address_no', 'jointype' => 'LEFT JOIN'),
                                array('table' => 'industry', 'condition' => 'industry.id = job_details.industry', 'jointype' => 'LEFT JOIN'),
                                array('table' => 'department', 'condition' => 'department.id = job_details.department', 'jointype' => 'LEFT JOIN'),
                                array('table' => 'role', 'condition' => 'role.id = job_details.role', 'jointype' => 'LEFT JOIN'),
                                array('table' => 'user', 'condition' => 'user.id = job_details.user_id', 'jointype' => 'LEFT JOIN'),

                            );

                            $like['candidate_req.skills'] = $skill;

                            $whereCompleted  = array('job_details.is_verify' => 1);
                            $result = $this->CommonModel->get_join('candidate_req', $val, $join, $whereCompleted, $order_by = 'candidate_req.id', $order = 'ASC', $limit = '', $offset = '', $distinct = '', $likearray = $like, $groupby = '', $whereinvalue = '', $whereinarray = '', $find_in_set = '')->result_array();

                            if ($result) {
                                // $result = $this->UserModel->update('skill_info', $userData, array('user_id' => $user_id));

                                $this->responseData['code']         = 200;
                                $this->responseData['status']       = 'success';
                                $this->responseData['message']      = 'Job list fetched successfully.';
                                if ($skill == "") {
                                    $this->responseData['data']         = [];
                                } else {
                                    $this->responseData['data']         = $result;
                                }
                            } else {
                                $this->responseData['code']    = 404;
                                $this->responseData['message'] = 'No job found!';
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
     * Update or add user profile photo
     */
    public function updateUserAvatar()
    {
        $json_data = json_decode(file_get_contents("php://input"));
        $api_key           = $json_data->api_key;
        $user_id           = $json_data->user_id;
        $user_avatar       = $json_data->user_avatar;

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

                        $userData['id']             = $user_id;
                        $userData['user_avatar']    = $user_avatar;
                        $userData['updated_at']     = strtotime(date('d-m-Y'));

                        $rand  = rand(10, 10000);
                        $image = preg_replace('#^data:image/[^;]+;base64,#', '',  $userData['user_avatar']);
                        $name               = $this->DIR . $rand . 'image.png';
                        $new                = file_put_contents($name, base64_decode($image));
                        $userData['user_avatar'] = $rand . 'image.png';

                        if ($user_id) {

                            $getRecord          = $this->UserModel->getRecord('user', array('id' => $user_id, 'role_id' => 4))->row_array();

                            if (!empty($getRecord)) {
                                if (empty($getRecord['user_avatar'])) {
                                    $this->CommonModel->update('user', array('user_avatar' => $userData['user_avatar']), array('id' => $user_id));
                                } else {
                                    unlink($this->DIR . $getRecord['user_avatar']);
                                }
                                $result = $this->CommonModel->update('user', $userData, array('id' => $user_id));

                                if ($result) {
                                    $this->responseData['code']         = 200;
                                    $this->responseData['status']       = 'success';
                                    $this->responseData['img_url']   = base_url('assets/api/images/' . $userData['user_avatar']);
                                    // $this->responseData['data']         = $result;
                                    $this->responseData['message']      = "Updated successfully.";
                                } else {
                                    $this->responseData['code']    = 401;
                                    $this->responseData['status']  = 'failed';
                                    $this->responseData['message'] = 'Wrong User';
                                    unset($this->responseData['data']);
                                }
                            } elseif (!empty($getRecord)) {
                                $result = $this->CommonModel->update('user', $userData, array('id' => $user_id));
                                if ($result) {
                                    $this->responseData['code']         = 200;
                                    $this->responseData['status']       = 'success';
                                    $this->responseData['img_url']      = base_url('assets/api/images/' . $userData['user_avatar']);
                                    // $this->responseData['data']         = $result;
                                    $this->responseData['message']      = "Updated successfully.";
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


    /**
     *  get department list
     */

    public function getDepartmentList()
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

                    $result = $this->CommonModel->select_rec('department', '*')->result_array();

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
     *  get department list
     */

    public function getRoleList()
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

                    $result = $this->CommonModel->select_rec('role', '*')->result_array();

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
     *  get designation list
     */

    public function getIndustryList()
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

                    $result = $this->CommonModel->select_rec('industry', '*')->result_array();

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
     *  get degreee list
     */

    public function getDegreeList()
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

                    $result = $this->CommonModel->select_rec('degree', '*')->result_array();

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
     *  get specialization list
     */

    public function getSpecializationList()
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

                    $result = $this->CommonModel->select_rec('specialization', '*')->result_array();

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
     *  get skills list
     */

    public function getSkillList()
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

                    $result = $this->CommonModel->select_rec('skill', '*')->result_array();

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
     *  get language list
     */

    public function getLanguageList()
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

                    $result = $this->CommonModel->select_rec('languages', '*')->result_array();

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
     *  get city list
     */

    public function getCityList()
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

                    $result = $this->CommonModel->select_rec('city', '*')->result_array();

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
     *  get employment list
     */

    public function getEmpTypeList()
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

                    $result = $this->CommonModel->select_rec('employment_type', '*')->result_array();

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

    /*
     * Update Employee Experience Info
     */
    public function editExperienceInfo()
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
                            } else {
                                $userData['total_experience_month']    = NULL;
                                $userData['total_experience_year']     = NULL;
                                $userData['job_title']           = NULL;
                                $userData['department']          = NULL;
                                $userData['category']            = NULL;
                                $userData['company_name']        = NULL;
                                $userData['industry']            = NULL;
                                $userData['current_work']        = NULL;
                                $userData['current_salary']   = NULL;
                                $userData['employment_type']  = NULL;
                                $userData['notice_period']    = NULL;
                                $userData['start_date']       = NULL;
                                $userData['end_date']         = NULL;
                            }

                            $userData['updated_at']         = strtotime(date('d-m-Y'));
                            $userData['is_completed']       = 1;

                            if ($user_id) {
                                $getRecord = $this->UserModel->getRecord('user', array('id' => $user_id, 'role_id' => 4))->row_array();

                                $getEducationRecord = $this->UserModel->getRecord('experience_info', array('user_id' => $user_id))->row_array();

                                if (!empty($getRecord) && !empty($getEducationRecord)) {

                                    $result = $this->UserModel->update('experience_info', $userData, array('user_id' => $user_id));

                                    if ($result) {
                                        $this->responseData['code']         = 200;
                                        $this->responseData['status']       = 'success';
                                        $this->responseData['data']         = $result;
                                        $this->responseData['message']      = "Updated successfully.";
                                    } else {
                                        $this->responseData['code']    = 401;
                                        $this->responseData['status']  = 'failed';
                                        $this->responseData['message'] = 'Not updated successfully!';
                                        unset($this->responseData['data']);
                                    }
                                } elseif (!empty($getRecord) && empty($getEducationRecord)) {


                                    $result = $this->UserModel->insert('experience_info', $userData);

                                    if ($result) {
                                        $this->responseData['code']         = 200;
                                        $this->responseData['status']       = 'success';
                                        $this->responseData['data']         = $result;
                                        $this->responseData['message']      = "Updated successfully.";
                                    } else {
                                        $this->responseData['code']    = 401;
                                        $this->responseData['status']  = 'failed';
                                        $this->responseData['message'] = 'Not updated successfully!';
                                        unset($this->responseData['data']);
                                    }
                                } else {
                                    $this->responseData['code'] = 404;
                                    $this->responseData['message'] = 'Not found User!';
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
