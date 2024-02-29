<?php
defined('BASEPATH') or exit('No direct script access allowed');

class JobDetails extends CI_Controller
{
    public $responseData = array('code' => 200, 'status' => 'success', 'message' => 'Your have registered successfully'); // set API response array
    public $customerRoleId    = null;
    public $DIR   = 'assets/images/';
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

    /*
     * Adding job details
     */
    public function add()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {

            if (empty($json_data = json_decode(file_get_contents("php://input")))) {
                $this->responseData['code']    = 404;
                $this->responseData['status']  = 'failed';
                $this->responseData['message'] = 'Required fields are missing';
            } else {
                $json_data  = json_decode(file_get_contents("php://input"));

                // print_r($json_data);
                // die();
                $api_key    = $json_data->api_key;
                //job details
                $user_id         = $json_data->user_id;
                $job_title       = $json_data->job_title;
                $industry        = $json_data->industry;
                $department      = $json_data->department;
                $role            = $json_data->role;
                $job_type        = $json_data->job_type;
                // $night_shift     = $json_data->night_shift;
                $add_perks       = $json_data->add_perks;
                $job_des         = $json_data->job_des;

                //salary range
                // $paytype         = $json_data->paytype;
                $min_salary      = $json_data->min_salary;
                $max_salary      = $json_data->max_salary;
                // $incentive       = $json_data->incentive;

                //job location
                $location_type   = $json_data->location_type;
                $wh_city         = $json_data->wh_city;
                $wh_address      = $json_data->wh_address;
                $wh_address2     = $json_data->wh_address2;
                $wo_place        = $json_data->wo_place;
                $wo_city         = $json_data->wo_city;
                // $fj_area         = $json_data->fj_area;

                //work location
                $work_location_type   = $json_data->work_location_type;
                $wl_country           = $json_data->wl_country;
                $wl_state             = $json_data->wl_state;
                $wl_pincode           = $json_data->wl_pincode;
                $wl_latitude          = $json_data->wl_latitude;
                $wl_longitude         = $json_data->wl_longitude;
            }

            if (isset($json_data)) {
                $api_key = $json_data->api_key;

                if ($this->ApiCommonModel->checkApiKey($api_key)) {
                    $reqData = $json_data;
                    $reqData = (array) $reqData;

                    if (!empty($reqData)) {
                        $verification = $this->JobDetailsModel->getRecord('user', array('id' => $user_id, 'role_id' => 3))->row_array()['is_verify'];

                        if ($verification == 1) {

                            //job details
                            $this->form_validation->set_data($reqData);
                            $this->form_validation->set_rules('user_id', 'User Id', 'required|trim');
                            $this->form_validation->set_rules('job_title', 'Job Title', 'required|trim');
                            $this->form_validation->set_rules('industry', 'Industry', 'required|trim');
                            $this->form_validation->set_rules('department', 'Department', 'required|trim');
                            $this->form_validation->set_rules('role', 'Role', 'required|trim');
                            $this->form_validation->set_rules('job_type', 'Job Type', 'required|trim');
                            // $this->form_validation->set_rules('add_perks', 'Additions perks', 'required|trim');
                            // $this->form_validation->set_rules('joining_fee', 'Joining fee', 'required|trim');
                            // $this->form_validation->set_rules('comments', 'Comments', 'required|trim');
                            $this->form_validation->set_rules('location_type', 'Location Type', 'required|trim');
                            $this->form_validation->set_rules('work_location_type', 'Work Location Type', 'required|trim');


                            if ($this->form_validation->run() == TRUE) {
                                // job details
                                $jobData['user_id']            = $user_id;
                                $jobData['job_title']          = $job_title;
                                $jobData['industry']           = $industry;
                                $jobData['department']         = $department;
                                $jobData['role']               = $role;
                                $jobData['job_type']           = $job_type;
                                // $jobData['night_shift']       = $night_shift;
                                $jobData['perks']              = $add_perks;
                                $jobData['job_des']            = $job_des;
                                $jobData['location_type']      = $location_type;
                                $jobData['work_location_type'] = $work_location_type;
                                $jobData['address_no']         = $this->JobDetailsModel->generate_unique_string(4);
                                $jobData['is_active']          = 1;
                                $jobData['is_verify']          = 2;
                                $jobData['created_at']         = strtotime(date('d-m-Y'));
                                $jobData['create_date']        = date('d-m-Y');
                                $jobData['is_completed']       = 1;

                                //salary range details 
                                $jobData['min_salary'] = $min_salary;
                                $jobData['max_salary'] = $max_salary;
                                //salary range details end

                                $result = $this->JobDetailsModel->insert('job_details', $jobData);
                                // job details end


                                // location details
                                if ($location_type != 1 && $location_type == 2 && $location_type != 3) {

                                    $locData['user_id']        = $user_id;
                                    $locData['address_no']     = $jobData['address_no'];
                                    $locData['location_type']  = $location_type;
                                    $locData['location_type_name']  = "WFH";
                                    $locData['wh_city']        = $wh_city;
                                    $locData['wh_address']     = $wh_address;
                                    $locData['wh_address2']    = $wh_address2;
                                    $locData['is_active']      = 1;
                                    $locData['created_at']     = strtotime(date('d-m-Y'));
                                } elseif ($location_type == 1 && $location_type != 2 && $location_type != 3) {

                                    $locData['user_id']        = $user_id;
                                    $locData['address_no']     = $jobData['address_no'];
                                    $locData['location_type']  = $location_type;
                                    $locData['location_type_name']  = "WFO";
                                    $locData['wo_place']       = $wo_place;
                                    if ($wo_place == 1) {
                                        $locData['wo_city']    = $wo_city;
                                    }
                                    $locData['is_active']      = 1;
                                    $locData['created_at']     = strtotime(date('d-m-Y'));
                                } elseif ($location_type != 1 && $location_type != 2 && $location_type == 3) {

                                    $locData['user_id']        = $user_id;
                                    $locData['address_no']     = $jobData['address_no'];
                                    $locData['location_type']  = $location_type;
                                    $locData['location_type_name']  = "HYBRID";
                                    $locData['is_active']      = 1;
                                    $locData['created_at']     = strtotime(date('d-m-Y'));
                                }

                                $locResult = $this->JobDetailsModel->insert('job_location', $locData);
                                //location details end

                                // work location details
                                if ($work_location_type == 1 && $work_location_type != 2) {
                                    $wlData['user_id']         = $user_id;
                                    $wlData['address_no']      = $jobData['address_no'];
                                    $wlData['work_location_type'] = $work_location_type;
                                    $wlData['wl_country']      = $wl_country;
                                    $wlData['wl_state']        = $wl_state;
                                    $wlData['wl_pincode']      = $wl_pincode;
                                    $wlData['wl_latitude']     = $wl_latitude;
                                    $wlData['wl_longitude']    = $wl_longitude;
                                    $wlData['is_active']       = 1;
                                    $wlData['created_at']      = strtotime(date('d-m-Y'));
                                } elseif ($work_location_type != 1 && $work_location_type == 2) {
                                    $wlData['user_id']            = $user_id;
                                    $wlData['address_no']         = $jobData['address_no'];
                                    $wlData['work_location_type'] = $work_location_type;
                                    $wlData['same_reg_address']   = $this->CommonModel->getRecord('user', array('id' => $user_id))->row_array()['reg_address'];
                                    $wlData['is_active']          = 1;
                                    $wlData['created_at']         = strtotime(date('d-m-Y'));
                                }

                                $wlResult = $this->JobDetailsModel->insert('jd_location', $wlData);
                                // work location details end


                                if ($jobData['user_id']) {
                                    if ($result && $locResult) {
                                        $this->JobDetailsModel->insert('candidate_req', array('job_id' => $result, 'user_id' => $user_id));
                                        $this->JobDetailsModel->insert('interviewer_info', array('job_id' => $result, 'user_id' => $user_id));
                                        $this->responseData['code']         = 200;
                                        $this->responseData['status']       = 'success';
                                        $this->responseData['job_id']       = $result;
                                        $this->responseData['job_details']  = $this->JobDetailsModel->getRecord('job_details', array('id' => $result))->row_array();
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
                        } elseif ($verification == 2) {
                            $this->responseData['code']    = 400;
                            $this->responseData['status']  = 'failed';
                            $this->responseData['message'] = 'User not verified by Admin!';
                        } else {
                            $this->responseData['code']    = 400;
                            $this->responseData['status']  = 'failed';
                            $this->responseData['message'] = 'User rejected by Admin!';
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

    /*
     * Update job details
     */
    public function edit()
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
                //job details
                $job_id          = $json_data->job_id;
                $user_id         = $json_data->user_id;
                $company_name    = $json_data->company_name;
                $designation     = $json_data->designation;
                $department      = $json_data->department;
                $role            = $json_data->role;
                $job_type        = $json_data->job_type;
                $night_shift     = $json_data->night_shift;
                $add_perks       = $json_data->add_perks;
                $joining_fee     = $json_data->joining_fee;
                $comments        = $json_data->comments;

                //compensation
                $paytype         = $json_data->paytype;
                $min_salary      = $json_data->min_salary;
                $max_salary      = $json_data->max_salary;
                $incentive       = $json_data->incentive;

                //location
                $location_type   = $json_data->location_type;
                $wh_city         = $json_data->wh_city;
                $wh_address      = $json_data->wh_address;
                $wh_address2     = $json_data->wh_address2;
                $wo_place        = $json_data->wo_place;
                $wo_city         = $json_data->wo_city;
                $fj_area         = $json_data->fj_area;
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
                        $this->form_validation->set_rules('company_name', 'Company Name', 'required|trim');
                        $this->form_validation->set_rules('designation', 'Designation', 'required|trim');
                        $this->form_validation->set_rules('department', 'Department', 'required|trim');
                        $this->form_validation->set_rules('role', 'Role', 'required|trim');
                        $this->form_validation->set_rules('job_type', 'Job Type', 'required|trim');
                        // $this->form_validation->set_rules('add_perks', 'Additions perks', 'required|trim');
                        // $this->form_validation->set_rules('joining_fee', 'Joining fee', 'required|trim');
                        // $this->form_validation->set_rules('comments', 'Comments', 'required|trim');
                        $this->form_validation->set_rules('location_type', 'Location Type', 'required|trim');
                        $this->form_validation->set_rules('paytype', 'Pay Type', 'required|trim');


                        if ($this->form_validation->run() == TRUE) {
                            // job details
                            $jobData['user_id']           = $user_id;
                            $jobData['company_name']      = $company_name;
                            $jobData['designation']       = $designation;
                            $jobData['department']        = $department;
                            $jobData['role']              = $role;
                            $jobData['job_type']          = $job_type;
                            $jobData['night_shift']       = $night_shift;
                            $jobData['perks']             = $add_perks;
                            $jobData['joining_fee']       = $joining_fee;
                            $jobData['comments']          = $comments;
                            $jobData['location_type']     = $location_type;
                            // $jobData['address_no']        = $this->JobDetailsModel->generate_unique_string(4);
                            $jobData['is_active']         = 1;
                            $jobData['is_verify']         = 1;
                            $jobData['updated_at']        = strtotime(date('d-m-Y'));
                            $jobData['update_date']       = date('d-m-Y');
                            $jobData['is_completed']      = 1;

                            //compensation details 
                            $jobData['paytype'] = $paytype;
                            if ($paytype == 1 && $paytype != 2 && $paytype != 3) {
                                $jobData['min_salary'] = $min_salary;
                                $jobData['max_salary'] = $max_salary;
                            } elseif ($paytype != 1 && $paytype == 2 && $paytype != 3) {
                                $jobData['min_salary'] = $min_salary;
                                $jobData['max_salary'] = $max_salary;
                                $jobData['incentive']  = $incentive;
                            } elseif ($paytype != 1 && $paytype != 2 && $paytype == 3) {
                                $jobData['incentive']  = $incentive;
                            }
                            //compensation details end


                            $result = $this->JobDetailsModel->update('job_details', $jobData, array('id' => $job_id));
                            // job details end

                            // location details
                            if ($location_type != 1 && $location_type == 2 && $location_type != 3) {

                                $locData['user_id']        = $user_id;
                                $locData['location_type']  = $location_type;
                                $locData['location_type_name']  = "WFH";
                                $locData['wh_city']        = $wh_city;
                                $locData['wh_address']     = $wh_address;
                                $locData['wh_address2']    = $wh_address2;
                                // $locData['address_no']     = $jobData['address_no'];
                                $locData['is_active']      = 1;
                                $locData['is_verify']      = 1;
                                $locData['updated_at']     = strtotime(date('d-m-Y'));
                            } elseif ($location_type == 1 && $location_type != 2 && $location_type != 3) {

                                $locData['user_id']        = $user_id;
                                $locData['location_type']  = $location_type;
                                $locData['location_type_name']  = "WFO";
                                $locData['wo_place']       = $wo_place;
                                if ($wo_place == 1) {
                                    $locData['wo_city']    = $wo_city;
                                }
                                // $locData['address_no']     = $jobData['address_no'];
                                $locData['is_active']      = 1;
                                $locData['is_verify']      = 1;
                                $locData['updated_at']     = strtotime(date('d-m-Y'));
                            } elseif ($location_type != 1 && $location_type != 2 && $location_type == 3) {

                                $locData['user_id']        = $user_id;
                                $locData['location_type']  = $location_type;
                                $locData['location_type_name']  = "FJ";
                                $locData['fj_area']        = $fj_area;
                                // $locData['address_no']     = $jobData['address_no'];
                                $locData['is_active']      = 1;
                                $locData['is_verify']      = 1;
                                $locData['updated_at']     = strtotime(date('d-m-Y'));
                            }

                            $getLocation = $this->JobDetailsModel->getRecord('job_details', array('id' => $job_id))->row_array()['address_no'];

                            $locResult = $this->JobDetailsModel->update('job_location', $locData, array('address_no' => $getLocation));

                            // print_r($locResult);
                            // die();
                            //location details end


                            if ($jobData['user_id']) {

                                if ($result && $locResult) {
                                    // $this->JobDetailsModel->insert('candidate_req', array('job_id' => $result, 'user_id' => $user_id));
                                    // $this->JobDetailsModel->insert('interviewer_info', array('job_id' => $result, 'user_id' => $user_id));
                                    $this->responseData['code']         = 200;
                                    $this->responseData['status']       = 'success';
                                    // $this->responseData['job_id']       = $result;
                                    // $this->responseData['job_details']  = $this->JobDetailsModel->getRecord('job_details', array('id' => $result))->row_array();
                                    $this->responseData['message']      = "Updated successfully.";
                                } else {
                                    $this->responseData['code']    = 401;
                                    $this->responseData['status']  = 'failed';
                                    $this->responseData['message'] = 'Not updated successfully';
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
     *  get job list by user_id(employer)
     */

    public function getJobListByUserId()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {

            $json_data   = json_decode(file_get_contents("php://input"));

            if (isset($json_data)) {
                $api_key = $json_data->api_key;

                if ($this->ApiCommonModel->checkApiKey($api_key)) {
                    $reqData = $json_data;
                    $reqData = (array) $reqData;

                    if (!empty($reqData['user_id'])) {
                        $this->form_validation->set_data($reqData);
                        $this->form_validation->set_rules('user_id', 'User Id', 'required');

                        if ($this->form_validation->run() == TRUE) {

                            $val = 'job_details.id,job_details.user_id,job_details.address_no,job_details.job_title,job_details.job_type,job_details.location_type,job_details.min_salary,job_details.max_salary,job_details.work_location_type,job_details.is_active,job_details.is_verify,
                            job_details.is_completed, job_details.create_date,
                        
                            job_location.address_no,job_location.location_type,job_location.location_type_name, job_location.wo_place, job_location.wo_city, job_location.wh_address, job_location.wh_address2,job_location.wh_city,

                            department.department_name,industry.industry_name,role.role_name
                            ';

                            $join = array(
                                array('table' => 'job_location', 'condition' => 'job_details.address_no = job_location.address_no', 'jointype' => 'LEFT JOIN'),
                                array('table' => 'industry', 'condition' => 'job_details.industry = industry.id', 'jointype' => 'LEFT JOIN'),
                                array('table' => 'department', 'condition' => 'job_details.department = department.id', 'jointype' => 'LEFT JOIN'),
                                array('table' => 'role', 'condition' => 'job_details.role = role.id', 'jointype' => 'LEFT JOIN'),
                            );

                            // $likearray = null;

                            // if ($userData['user_id'] && $userData['user_id'] != '') {
                            //     $likearray['check_in.user_id'] = $userData['user_id'];
                            // }
                            // $user_id =
                            $whereCompleted  = array('job_details.user_id' => $reqData['user_id'], 'job_details.is_completed ' => 3);
                            $result = $this->JobDetailsModel->get_join('job_details', $val, $join, $whereCompleted, $order_by = 'job_details.id', $order = 'ASC', $limit = '', $offset = '', $distinct = '', $likearray = null, $groupby = '', $whereinvalue = '', $whereinarray = '', $find_in_set = '')->result_array();

                            $wherePending  = array('job_details.user_id' => $reqData['user_id'], 'job_details.is_completed <' => 3);
                            $pendingResult = $this->JobDetailsModel->get_join('job_details', $val, $join, $wherePending, $order_by = 'job_details.id', $order = 'ASC', $limit = '', $offset = '', $distinct = '', $likearray = null, $groupby = '', $whereinvalue = '', $whereinarray = '', $find_in_set = '')->result_array();

                            if ($result || $pendingResult) {

                                $this->responseData['code']        = 200;
                                $this->responseData['status']      = 'success';
                                $this->responseData['message']     = "Fetched successfully.";
                                $this->responseData['completed']   = $result;
                                $this->responseData['pending']     = $pendingResult;
                            } else {
                                $this->responseData['code']    = 401;
                                $this->responseData['status']  = 'failed';
                                $this->responseData['message'] = 'Not fetched';
                                $this->responseData['completed']   = [];
                                $this->responseData['pending']     = [];
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
                        $this->responseData['message'] = 'Required param missing: user id!';
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
                $this->responseData['message'] = 'Invalid Request!';
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
     *  get users list by job_id(employer)
     */

    public function getUsersByJobId()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {

            $json_data   = json_decode(file_get_contents("php://input"));

            if (isset($json_data)) {
                $api_key = $json_data->api_key;

                if ($this->ApiCommonModel->checkApiKey($api_key)) {
                    $reqData = $json_data;
                    $reqData = (array) $reqData;

                    if (!empty($reqData['job_id'])) {
                        $this->form_validation->set_data($reqData);
                        $this->form_validation->set_rules('job_id', 'Job Id', 'required');

                        if ($this->form_validation->run() == TRUE) {

                            $val = 'applied_jobs.id,applied_jobs.job_id,applied_jobs.user_id,
                            applied_jobs.is_active, applied_jobs.created_at,
                         
                            user.role_id, user.first_name,user.last_name,user.mobile,user.email,user.dob,user.gender,user.is_active,user.is_completed,user.city,user.created_at
                             ';

                            $join = array(
                                array('table' => 'user', 'condition' => 'user.id = applied_jobs.user_id', 'jointype' => 'LEFT JOIN'),
                            );

                            // $likearray = null;

                            // if ($userData['user_id'] && $userData['user_id'] != '') {
                            //     $likearray['check_in.user_id'] = $userData['user_id'];
                            // }
                            // $user_id =
                            $whereCompleted  = array('applied_jobs.job_id' => $reqData['job_id'], 'user.role_id' => 4);
                            $result = $this->JobDetailsModel->get_join('applied_jobs', $val, $join, $whereCompleted, $order_by = 'applied_jobs.id', $order = 'ASC', $limit = '', $offset = '', $distinct = '', $likearray = null, $groupby = '', $whereinvalue = '', $whereinarray = '', $find_in_set = '')->result_array();

                            // print_r($result);
                            // die();

                            if ($result) {

                                $this->responseData['code']        = 200;
                                $this->responseData['status']      = 'success';
                                $this->responseData['message']     = "Fetched successfully.";
                                $this->responseData['data']        = $result;
                            } else {
                                $this->responseData['code']    = 401;
                                $this->responseData['status']  = 'failed';
                                $this->responseData['message'] = 'Not fetched';
                                $this->responseData['data']    = [];
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
                        $this->responseData['message'] = 'Required param missing: job id!';
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
                $this->responseData['message'] = 'Invalid Request!';
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
     *  get user profile by user_id(employer)
     */

    public function getUserByUserId()
    {
        $isAuth = $this->ApiCommonModel->decodeToken();
        if ($isAuth == 1) {

            $json_data   = json_decode(file_get_contents("php://input"));

            if (isset($json_data)) {
                $api_key = $json_data->api_key;
                $user_id = $json_data->user_id;

                if ($this->ApiCommonModel->checkApiKey($api_key)) {
                    $reqData = $json_data;
                    $reqData = (array) $reqData;

                    if (!empty($reqData['user_id'])) {
                        $this->form_validation->set_data($reqData);
                        $this->form_validation->set_rules('user_id', 'User Id', 'required');

                        if ($this->form_validation->run() == TRUE) {

                            $result = $this->CommonModel->getRecord('user', array('id' => $user_id, 'role_id' => 4))->row(); //only employee profile

                            if ($result) {

                                $this->responseData['code']        = 200;
                                $this->responseData['status']      = 'success';
                                $this->responseData['message']     = "Fetched successfully.";
                                $this->responseData['data']        = $result;
                            } else {
                                $this->responseData['code']    = 401;
                                $this->responseData['status']  = 'failed';
                                $this->responseData['message'] = 'Not fetched';
                                $this->responseData['data']    = [];
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
                        $this->responseData['message'] = 'Required param missing: user id!';
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
                $this->responseData['message'] = 'Invalid Request!';
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
     *  get job details by id
     */

    public function getById()
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

                    if (!empty($reqData['id'])) {
                        $this->form_validation->set_data($reqData);
                        $this->form_validation->set_rules('id', 'Id', 'required');

                        if ($this->form_validation->run() == TRUE) {

                            $val = 'job_details.*, job_location.address_no, job_location.location_type, job_location.location_type_name, job_location.wh_city, job_location.wh_address, job_location.wh_address2, job_location.wo_place,job_location.wo_city,job_location.fj_area,
                        
                        candidate_req.education,candidate_req.experience,candidate_req.eng_lvl,candidate_req.description,
                            
                        interviewer_info.com_pref,interviewer_info.com_pref_fn,interviewer_info.com_pref_mob,interviewer_info.noti_pref,interviewer_info.noti_pref_fn,interviewer_info.noti_pref_mob,interviewer_info.interview_method,
                        
                      ';

                            $join = array(
                                array('table' => 'user', 'condition' => 'job_details.address_no = job_location.address_no', 'jointype' => 'LEFT JOIN'),
                            );

                            // $likearray = null;

                            // if ($userData['user_id'] && $userData['user_id'] != '') {
                            //     $likearray['check_in.user_id'] = $userData['user_id'];
                            // }

                            $whereArray  = $reqData['id'];
                            $result = $this->JobDetailsModel->getJoinById($val, $whereArray);
                            // print_r($result[0]->id);
                            // die();

                            if ($result) {
                                //  if($result[0]->job_type == 1){
                                //         $job_type = "FULL TIME";
                                //     }elseif($result[0]->job_type == 2){
                                //         $job_type = "PART TIME";
                                //     }else{
                                //         $job_type = "BOTH (Full Time & Part Time)";
                                //     }
                                $this->responseData['code']     = 200;
                                $this->responseData['status']   = 'success';
                                $this->responseData['message']  = "Fetched successfully.";
                                // $this->responseData['data']         = $result;
                                $this->responseData['id']              = intval($result[0]->id);
                                $this->responseData['user_id']         = intval($result[0]->user_id);
                                $this->responseData['company_name']    = $result[0]->company_name;
                                // $this->responseData['designation']     = $this->JobDetailsModel->getRecord('designation',array('id'=>$result[0]->designation))->row_array()['designation'];
                                $this->responseData['designation']     = ($result[0]->designation == null) ? null : intval($result[0]->designation);
                                // $this->responseData['department']      = $this->JobDetailsModel->getRecord('department',array('id'=>$result[0]->department))->row_array()['department'];
                                $this->responseData['department']      = ($result[0]->department == null) ? null : intval($result[0]->department);
                                // $this->responseData['role']            = $this->JobDetailsModel->getRecord('category',array('id'=>$result[0]->role))->row_array()['category'];
                                $this->responseData['role']            = ($result[0]->role == null) ? null : intval($result[0]->role);
                                $this->responseData['job_type']        = ($result[0]->job_type == null) ? null : intval($result[0]->job_type);
                                $this->responseData['night_shift']     = ($result[0]->night_shift == null) ? null : intval($result[0]->night_shift);
                                $this->responseData['location_type']   = ($result[0]->location_type == null) ? null : intval($result[0]->location_type);
                                $this->responseData['paytype']         = ($result[0]->paytype == null) ? null : intval($result[0]->paytype);
                                $this->responseData['min_salary']      = ($result[0]->min_salary == null) ? null : intval($result[0]->min_salary);
                                $this->responseData['max_salary']      = ($result[0]->max_salary == null) ? null : intval($result[0]->max_salary);
                                $this->responseData['incentive']       = ($result[0]->incentive == null) ? null : intval($result[0]->incentive);
                                $this->responseData['is_verify']       = ($result[0]->is_verify == null) ? null : intval($result[0]->is_verify);
                                $this->responseData['create_date']     = $result[0]->create_date;
                                $this->responseData['update_date']     = ($result[0]->updated_at != NULL) ? date('d-m-Y', $result[0]->updated_at) : NULL;
                                $this->responseData['location_type_name']   = $result[0]->location_type_name;
                                $this->responseData['wo_place']        = ($result[0]->wo_place == null) ? null : intval($result[0]->wo_place);
                                $this->responseData['wo_city']         = ($result[0]->wo_city == null) ? null : intval($result[0]->wo_city);
                                $this->responseData['wh_city']         = ($result[0]->wh_city == null) ? null : intval($result[0]->wh_city);
                                $this->responseData['wh_address']      = $result[0]->wh_address;
                                $this->responseData['wh_address2']     = $result[0]->wh_address2;
                                $this->responseData['fj_area']         = $result[0]->fj_area;
                                $this->responseData['education']       = ($result[0]->education == null) ? null : intval($result[0]->education);
                                $this->responseData['experience']      = ($result[0]->experience == null) ? null : intval($result[0]->experience);
                                $this->responseData['eng_lvl']         = ($result[0]->eng_lvl == null) ? null : intval($result[0]->eng_lvl);
                                $this->responseData['description']     = $result[0]->description;
                                $this->responseData['com_pref']        = ($result[0]->com_pref == null) ? null : intval($result[0]->com_pref);
                                $this->responseData['com_pref_fn']     = $result[0]->com_pref_fn;
                                $this->responseData['com_pref_mob']    = $result[0]->com_pref_mob;
                                $this->responseData['noti_pref']       = ($result[0]->noti_pref == null) ? null : intval($result[0]->noti_pref);
                                $this->responseData['noti_pref_fn']    = $result[0]->noti_pref_fn;
                                $this->responseData['noti_pref_mob']   = $result[0]->noti_pref_mob;
                                $this->responseData['interview_method'] = ($result[0]->interview_method == null) ? null : intval($result[0]->interview_method);
                            } else {
                                $this->responseData['code']    = 401;
                                $this->responseData['status']  = 'failed';
                                $this->responseData['message'] = 'Not fetched credentials';
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
                        $this->responseData['message'] = 'Required param missing: Id';
                        unset($this->responseData['data']);
                    }
                } else {
                    $this->responseData['code']    = 400;
                    $this->responseData['status']  = 'failed';
                    $this->responseData['message'] = 'Invalid api key!';
                }
            } else {
                $this->responseData['code']    = 400;
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

    public function getDesignationList()
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

                    $result = $this->JobDetailsModel->select_rec('designation', '*')->result_array();

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
     *  get category and department by designation
     */

    public function getListByDesignationId()
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

                    if (!empty($reqData['id'])) {
                        $this->form_validation->set_data($reqData);
                        $this->form_validation->set_rules('id', 'Id', 'required');

                        if ($this->form_validation->run() == TRUE) {

                            $val = 'designation.*,department.department,category.category';

                            $join = array(
                                array('table' => 'department', 'condition' => 'department.desig_id = designation.id', 'jointype' => 'LEFT JOIN'),
                                array('table' => 'category', 'condition' => 'category.desig_id = designation.id', 'jointype' => 'LEFT JOIN'),
                            );

                            // $likearray = null;

                            // if ($userData['user_id'] && $userData['user_id'] != '') {
                            //     $likearray['check_in.user_id'] = $userData['user_id'];
                            // }

                            $whereArray  = array('designation.id' => $reqData['id']);

                            $result = $this->JobDetailsModel->get_join('designation', $val, $join, $whereArray, $order_by = 'designation.id', $order = 'DESC')->result_array();
                            // $result = $this->JobDetailsModel->getJoinById3($val, $whereArray);

                            if ($result) {
                                $this->responseData['code']     = 200;
                                $this->responseData['status']   = 'success';
                                // $this->responseData['data']  = $result;
                                $this->responseData['designation']       = $result[0]['designation'];
                                $this->responseData['department_list']   = $this->JobDetailsModel->select_rec('department', '*', array('desig_id' => $reqData['id']))->result_array();
                                $this->responseData['category_list']     = $this->JobDetailsModel->select_rec('category', '*', array('desig_id' => $reqData['id']))->result_array();
                                $this->responseData['message']           = "Fetched successfully.";
                            } else {
                                $this->responseData['code']    = 4001;
                                $this->responseData['status']  = 'failed';
                                $this->responseData['message'] = 'Not fetched';
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
     *  get category list
     */

    public function getCategoryList()
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

                    $result = $this->JobDetailsModel->select_rec('category', '*')->result_array();

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
     *  get job preview by job id
     */

    public function getJobPreviewById()
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

                    if (!empty($reqData['job_id'])) {
                        $this->form_validation->set_data($reqData);
                        $this->form_validation->set_rules('job_id', 'Job Id', 'required');

                        if ($this->form_validation->run() == TRUE) {

                            $val = 'job_details.*, job_location.address_no, job_location.location_type, job_location.location_type_name, job_location.wh_city, job_location.wh_address, job_location.wh_address2, job_location.wo_place,job_location.wo_city,job_location.fj_area,
                        
                        candidate_req.education,candidate_req.experience,candidate_req.eng_lvl,candidate_req.description,
                        
                        interviewer_info.com_pref,interviewer_info.com_pref_fn,interviewer_info.com_pref_mob,interviewer_info.noti_pref,interviewer_info.noti_pref_fn,interviewer_info.noti_pref_mob,interviewer_info.interview_method,
                        
                        designation.designation,department.department,category.category,
                        
                        education.education,experience.experience,eng_lvl.eng_lvl';

                            $join = array(
                                array('table' => 'job_location', 'condition' => 'job_location.address_no = job_details.address_no', 'jointype' => 'LEFT JOIN'),
                                array('table' => 'candidate_req', 'condition' => 'candidate_req.job_id = job_details.id', 'jointype' => 'LEFT JOIN'),
                                array('table' => 'interviewer_info', 'condition' => 'interviewer_info.job_id = job_details.id', 'jointype' => 'LEFT JOIN'),
                                array('table' => 'designation', 'condition' => 'job_details.designation = designation.id', 'jointype' => 'LEFT JOIN'),
                                array('table' => 'department', 'condition' => 'job_details.department = department.id', 'jointype' => 'LEFT JOIN'),
                                array('table' => 'category', 'condition' => 'job_details.role = category.id', 'jointype' => 'LEFT JOIN'),
                                array('table' => 'education', 'condition' => 'candidate_req.education = education.id', 'jointype' => 'LEFT JOIN'),
                                array('table' => 'experience', 'condition' => 'candidate_req.experience = experience.id', 'jointype' => 'LEFT JOIN'),
                                array('table' => 'eng_lvl', 'condition' => 'candidate_req.eng_lvl = eng_lvl.id', 'jointype' => 'LEFT JOIN'),
                            );

                            // $likearray = null;

                            // if ($userData['user_id'] && $userData['user_id'] != '') {
                            //     $likearray['check_in.user_id'] = $userData['user_id'];
                            // }

                            $whereArray  = $reqData['job_id'];
                            // $result = $this->JobDetailsModel->getJoinById2($val, $whereArray);
                            $result = $this->JobDetailsModel->get_join('job_details', $val, $join, array('job_details.id' => $reqData['job_id']), $order_by = 'job_details.id', $order = 'DESC')->result_array();


                            if ($result) {
                                foreach ($result as $res) {

                                    if ($res['job_type'] == 1) {
                                        $job_type = "FULL TIME";
                                    } elseif ($res['job_type'] == 2) {
                                        $job_type = "PART TIME";
                                    } else {
                                        $job_type = "BOTH (Full Time & Part Time)";
                                    }

                                    $this->responseData['code']     = 200;
                                    $this->responseData['status']   = 'success';
                                    $this->responseData['message']  = "Fetched successfully.";
                                    $this->responseData['id']       = intval($res['id']);

                                    $this->responseData['data']     = $result;
                                    // $this->responseData['user_id']        = intval($res['user_id']);
                                    // // $this->responseData['address_no']     = $res['address_no'];
                                    // $this->responseData['company_name']   = $res['company_name'];
                                    // $this->responseData['designation']    = $this->JobDetailsModel->getRecord('designation',array('id'=>$res['designation']))->row_array()['designation'];
                                    // $this->responseData['department']     = $this->JobDetailsModel->getRecord('department',array('id'=>$res['department']))->row_array()['department'];
                                    // $this->responseData['role']           = $this->JobDetailsModel->getRecord('category',array('id'=>$res['role']))->row_array()['category'];
                                    // $this->responseData['job_type']       = $job_type;   
                                    // $this->responseData['night_shift']    = intval($res['night_shift']);
                                    // $this->responseData['location_type']     = intval($res['location_type']);
                                    // $this->responseData['paytype']           = intval($res['paytype']);
                                    // $this->responseData['min_salary']        = intval($res['min_salary']);
                                    // $this->responseData['max_salary']        = intval($res['max_salary']);
                                    // $this->responseData['incentive']         = intval($res['incentive']);
                                    // $this->responseData['is_active']         = intval($res['is_active']);
                                    // $this->responseData['created_at']        = date('d-m-Y',$res['created_at']);
                                    // $this->responseData['updated_at']        = ($res['updated_at'] != NULL) ? date('d-m-Y',$res['updated_at']): NULL;
                                    // $this->responseData['location_type_name']   = $res['location_type_name'];
                                    // $this->responseData['wo_city']              = $res['wo_city'];
                                    // $this->responseData['wo_address']           = $res['wo_address'];
                                    // $this->responseData['wo_address2']          = $res['wo_address2'];
                                    // $this->responseData['wh_place']             = $res['wh_place'];
                                    // $this->responseData['wh_city']              = $res['wh_city'];
                                    // $this->responseData['fj_area']              = $res['fj_area'];
                                    // $this->responseData['education']            = ($res['education'] !=NULL)?$this->JobDetailsModel->getRecord('education',array('id'=>$res['education']))->row_array()['education'] : NULL;

                                    // $this->responseData['experience']           = ($res['experience'] !=NULL)?$this->JobDetailsModel->getRecord('experience',array('id'=>$res['experience']))->row_array()['experience'] : NULL ;

                                    // $this->responseData['eng_lvl']              = ($res['eng_lvl'] !=NULL)?$this->JobDetailsModel->getRecord('eng_lvl',array('id'=>$res['eng_lvl']))->row_array()['eng_lvl'] : NULL;

                                    // $this->responseData['description']          = $res['description'];
                                    // $this->responseData['com_pref']             = intval($res['com_pref']);
                                    // $this->responseData['com_pref_fn']          = $res['com_pref_fn'];
                                    // $this->responseData['com_pref_mob']         = intval($res['com_pref_mob']);
                                    // $this->responseData['noti_pref']            = intval($res['noti_pref']);
                                    // $this->responseData['noti_pref_fn']         = $res['noti_pref_fn'];
                                    // $this->responseData['noti_pref_mob']        = intval($res['noti_pref_mob']);
                                    // $this->responseData['interview_method']     = intval($res['interview_method']);

                                }
                            } else {
                                $this->responseData['code']    = 401;
                                $this->responseData['status']  = 'failed';
                                $this->responseData['message'] = 'Not fetched';
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
                        $this->responseData['message'] = 'Required param missing: Id';
                        unset($this->responseData['data']);
                    }
                } else {
                    $this->responseData['code']    = 400;
                    $this->responseData['status']  = 'failed';
                    $this->responseData['message'] = 'Invalid api key!';
                }
            } else {
                $this->responseData['code']    = 400;
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

                    $result = $this->JobDetailsModel->select_rec('city', '*')->result_array();

                    if ($result) {
                        $this->responseData['code']        = 200;
                        $this->responseData['status']      = 'success';
                        $this->responseData['data']     = $result;
                        // $this->responseData['city_id']     = $result;
                        // $this->responseData['city_name']   = $result;
                        // $this->responseData['city_state']  = $result;
                        $this->responseData['message']     = "Fetched successfully.";
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
     *  get Job profile by mobile no
     */

    public function getJobProfile()
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

                    if (!empty($reqData['mobile'])) {
                        $this->form_validation->set_data($reqData);
                        $this->form_validation->set_rules('mobile', 'Mobile No', 'required');
                        $this->form_validation->set_rules('role_id', 'Role ID', 'required');


                        if ($this->form_validation->run() == TRUE) {

                            $result = $this->JobDetailsModel->getRecord('user', array('mobile' => $reqData['mobile'], 'role_id' => $reqData['role_id']))->row_array();
                            // print_r($result);
                            // die();

                            if ($result) {
                                $this->responseData['code']     = 200;
                                $this->responseData['status']   = 'success';
                                $this->responseData['message']  = 'Fetched';
                                $this->responseData['user_id']           = intval($result['id']);
                                $this->responseData['mobile']            = intval($result['mobile']);
                                $this->responseData['company_name']      = $result['company_name'];
                                $this->responseData['email']             = $result['email'];
                                $this->responseData['website']           = $result['website'];
                                $this->responseData['no_of_employees']   = intval($result['no_of_employees']);
                                $this->responseData['is_company']        = intval($result['is_company']);
                                $this->responseData['is_accept']         = intval($result['is_accept']);
                            } else {
                                $this->responseData['code']    = 404;
                                $this->responseData['status']  = 'failed';
                                $this->responseData['message'] = 'Not found';
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
                        $this->responseData['message'] = 'Required param missing';
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
     * Edit Job profile
     */
    public function editJobProfile()
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
                $role_id    = $json_data->role_id;
                $mobile     = $json_data->mobile;
                $company_name    = $json_data->company_name;
                $email           = $json_data->email;
                $website         = $json_data->website;
                $no_of_employees = $json_data->no_of_employees;
                $is_company      = $json_data->is_company;
            }
            if ($json_data) {
                // $api_key = $this->input->post('api_key');
                $api_key = $json_data->api_key;

                if ($this->ApiCommonModel->checkApiKey($api_key)) {
                    $reqData = $json_data;
                    $reqData = (array) $reqData;

                    if (!empty($mobile)) {

                        $this->form_validation->set_data($reqData);
                        $this->form_validation->set_rules('role_id', 'Role Id', 'required|trim');
                        $this->form_validation->set_rules('mobile', 'Mobile No', 'required|trim');

                        if ($this->form_validation->run() == TRUE) {
                            $jobData['company_name']       = $company_name;
                            $jobData['email']              = $email;
                            $jobData['website']            = $website;
                            $jobData['no_of_employees']    = $no_of_employees;
                            $jobData['is_company']         = $is_company;
                            $jobData['updated_at']         = strtotime(date('d-m-Y'));

                            if ($mobile && $role_id) {
                                $getRecord = $this->JobDetailsModel->getRecord('user', array('mobile' => $mobile, 'role_id' => $role_id))->row_array();

                                if (!empty($getRecord)) {

                                    $result = $this->JobDetailsModel->update('user', $jobData, array('mobile' => $mobile, 'role_id' => $role_id));

                                    if ($result) {
                                        $this->responseData['code']           = 200;
                                        $this->responseData['status']         = 'success';
                                        $this->responseData['message']          = "Updated successfully.";
                                        $this->responseData['user_id']          = intval($getRecord['id']);
                                        $this->responseData['mobile']           = intval($getRecord['mobile']);
                                        $this->responseData['company_name']     = $company_name;
                                        $this->responseData['email']            = $email;
                                        $this->responseData['website']          = $website;
                                        $this->responseData['no_of_employees']  = intval($no_of_employees);
                                        $this->responseData['is_company']       = intval($is_company);
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
}
