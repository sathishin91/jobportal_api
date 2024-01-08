<?php

use Willywes\AgoraSDK\RtcTokenBuilder;

class CommonModel extends CI_Model
{
	public $imageExtensions = array('jpg', 'png', 'bmp', 'jpeg', 'gif', 'ico');
	public $phpDateFormat = 'm/d/Y g:i:a';  /// default Date Format for php
	public $jsDateFormat = 'mm/dd/yy';  /// default Date Format for Js
	public $STIME = '12:00:00 AM';  /// default Start Time
	public $ETIME = '11:59:59 PM';  /// default End  Time

	///////////////////////////////////
	#######  COMMON FUNCTIONS  ########
	///////////////////////////////////

	public function __construct()
	{
		parent::__construct();
		$this->load->library('image_lib');
		// $this->load->vendor('RtcTokenBuilder');
		date_default_timezone_set("Asia/Kolkata");
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
	}

	public static function GetToken($user_id)
	{

		$appID = "31daf2fe9cbc472aa9ab566541a12467";
		$appCertificate = "b059919a71934cd89f915ee2675f31cc";
		$channelName = "Test";
		$uid = $user_id;
		$uidStr = ($user_id) . '';
		$role = RtcTokenBuilder::RoleAttendee;
		$expireTimeInSeconds = 3600;
		$currentTimestamp = (new \DateTime("now", new \DateTimeZone('UTC')))->getTimestamp();
		$privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

		return RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);
	}


	/**
	 *Get All Record based on table and condition
	 * @param string $table
	 * @param array/string $where
	 * @param string/array $select  list of column for select , NOTE: add column alias during join used (array('*','table.col1 as column1','table.col2 as column2'))
	 * @param array $joins default empty array , default join type LEFT  array('table' => tablename,'condition'=>condition,'jointype'=> 'LEFT,RIGHT,OTHER..');
	 * @param array $likearray default empty array like array are join with OR (title LIKE %title% OR title1 LIKE %title1%)
	 * @param int $limit  default false
	 * @param int $offset default false
	 * @param string/array $orderby default id order by column
	 * @param string  $order default DESC ,  order = ASC or DESC
	 * @param string/array $groupby
	 */
	public function getRecord($table, $where = NULL, $select = '*', $limit = FALSE, $offset = 0)
	{
		$this->db->from($table);
		$this->db->select($select);
		// where condition
		if ($where) {
			$this->db->where($where);
		}

		if ($limit) {
			$this->db->limit($limit, $offset);
		}

		return $this->db->get();
	}

	public function dbjoin($joins)
	{
		if (is_array($joins) && !empty($joins)) {
			foreach ($joins as $joinInfo) {
				$joinType = (array_key_exists('jointype', $joinInfo)) ? $joinInfo['jointype'] : 'LEFT';
				$this->db->join($joinInfo['table'], $joinInfo['condition'], $joinType);
			}
		}
		return $this;
	}

	public function select_rec($tbl, $select = '*', $where = '', $order_by = '', $order = '', $like = '', $limit = FALSE, $offset = 0, $where_in_field = '', $where_in_val = '', $groupby = '', $find_in_set = '')
	{
		$this->db->select($select);
		if ($where != '')
			$this->db->where($where);

		if ($where_in_field != '')
			$this->db->where_in($where_in_field, $where_in_val);

		if ($find_in_set && $find_in_set != '')
			$this->db->where($find_in_set);

		if ($order_by != '')
			$this->db->order_by($order_by, $order);

		if ($like != '') {
			$this->db->group_start();
			$this->db->or_like($like);
			$this->db->group_end();
		}

		if ($groupby != '')
			$this->db->group_by($groupby);

		if ($limit)
			$this->db->limit($limit, $offset);

		return $this->db->get($tbl);
	}

	public function select_r($tbl, $select = '*', $where = '', $order_by = '', $order = '', $like = '', $limit = FALSE, $offset = 0, $where_in_field = '', $where_in_val = '', $groupby = '', $find_in_set = '')
	{
		$this->db->select($select);
		if ($where != '')
			$this->db->where('datentime>= now() - interval 24 hour');

		$this->db->select($select);
		if ($where != '')
			$this->db->where($where);

		if ($where_in_field != '')
			$this->db->where_in($where_in_field, $where_in_val);

		if ($find_in_set && $find_in_set != '')
			$this->db->where($find_in_set);

		if ($order_by != '')
			$this->db->order_by(SORT_ASC, $order);

		if ($like != '') {
			$this->db->group_start();
			$this->db->or_like($like);
			$this->db->group_end();
		}

		if ($groupby != '')
			$this->db->group_by($groupby);

		if ($limit)
			$this->db->limit($limit, $offset);

		return $this->db->get($tbl);
	}

	public function select_contact($tbl, $select = '*', $where = '', $order_by = '', $order = '', $like = '', $limit = FALSE, $offset = 0, $where_in_field = '', $where_in_val = '', $groupby = '', $find_in_set = '')
	{
		$this->db->select($select);
		if ($where != '')
			$this->db->where($where);

		if ($where_in_field != '')
			$this->db->where_in($where_in_field, $where_in_val);

		if ($find_in_set && $find_in_set != '')
			$this->db->where($find_in_set);

		if ($order_by != '')
			$this->db->order_by($order_by, $order);

		if ($like != '') {
			$this->db->group_start();
			$this->db->or_like($like);
			$this->db->group_end();
		}

		if ($groupby != '')
			$this->db->group_by($groupby);

		if ($limit)
			$this->db->limit($limit, $offset);

		//  print_r($this->db->last_query());
		//  die();

		return $this->db->get($tbl);
	}
	/**
	 * get join data
	 * @param unknown $table
	 * @param unknown $value
	 * @param unknown $joins
	 * @param unknown $where
	 * @param string $order_by
	 * @param string $order
	 * @param string $limit
	 * @param string $offset
	 * @param string $distinct
	 * @param unknown $likearray
	 * @param string $groupby
	 * @param string $whereinvalue
	 * @param string $whereinarray
	 * @param string $find_in_set
	 */
	public function get_joins($table, $value, $joins, $where, $order_by = '', $order = '', $limit = '', $offset = '', $distinct = '', $likearray = null, $groupby = '', $whereinvalue = '', $whereinarray = '', $find_in_set = '')
	{

		$this->db->select($value);
		if (is_array($joins) && count($joins) > 0) {
			foreach ($joins as $k => $v) {
				$this->db->join($v['table'], $v['condition'], $v['jointype']);
			}
		}
		$this->db->order_by($order_by, $order);
		$this->db->where('datentime>= now() - interval 24 hour');
		if ($find_in_set && $find_in_set != '')
			$this->db->where($find_in_set);


		if ($distinct !== '')
			$this->db->distinct();

		if ($limit)
			$this->db->limit($limit, $offset);

		if ($likearray != '') {
			if (is_array($likearray)) {
				if (!empty($likearray)) {
					$this->db->group_start();
					$this->db->or_like($likearray);
					$this->db->group_end();
				}
			} else {
				$this->db->group_start();
				$this->db->or_like($likearray);
				$this->db->group_end();
			}
		}

		if ($groupby != '')
			$this->db->group_by($groupby);

		if (!empty($whereinvalue) && $whereinvalue != '')
			$this->db->where_in($whereinvalue, $whereinarray);

		if ($limit != '' && $offset != '')
			return $this->db->get($table, $limit, $offset);
		else
			return $this->db->get($table);
	}

	public function get_join($table, $value, $joins, $where, $order_by = '', $order = '', $limit = '', $offset = '', $distinct = '', $likearray = null, $groupby = '', $whereinvalue = '', $whereinarray = '', $find_in_set = '')
	{

		$this->db->select($value);
		if (is_array($joins) && count($joins) > 0) {
			foreach ($joins as $k => $v) {
				$this->db->join($v['table'], $v['condition'], $v['jointype']);
			}
		}
		$this->db->order_by($order_by, $order);
		$this->db->where($where);
		if ($find_in_set && $find_in_set != '')
			$this->db->where($find_in_set);


		if ($distinct !== '')
			$this->db->distinct();

		if ($limit)
			$this->db->limit($limit, $offset);

		if ($likearray != '') {
			if (is_array($likearray)) {
				if (!empty($likearray)) {
					$this->db->group_start();
					$this->db->or_like($likearray);
					$this->db->group_end();
				}
			} else {
				$this->db->group_start();
				$this->db->or_like($likearray);
				$this->db->group_end();
			}
		}

		if ($groupby != '')
			$this->db->group_by($groupby);

		if (!empty($whereinvalue) && $whereinvalue != '')
			$this->db->where_in($whereinvalue, $whereinarray);

		if ($limit != '' && $offset != '')
			return $this->db->get($table, $limit, $offset);
		else
			return $this->db->get($table);
	}
	

    public function getJoinById($val, $id)
	{
		$query = $this->db
			->select($val)
			->from("job_details")
			->join("job_location", "job_location.address_no = job_details.address_no")
            ->join("candidate_req", "candidate_req.job_id = job_details.id")
			->join("interviewer_info", "interviewer_info.job_id = job_details.id")
            
			->where('job_details.id', $id)
			->get();

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	
	 public function getJoinById2($val, $id)
	{
		$query = $this->db
			->select($val)
			->from("job_details")
			->join("job_location", "job_location.address_no = job_details.address_no")
			->join("candidate_req", "candidate_req.job_id = job_details.id")
			->join("interviewer_info", "interviewer_info.job_id = job_details.id")
			->where('job_details.id', $id)
			->get();

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	
	public function getJoinById3($val, $id)
	{
		$query = $this->db
			->select($val)
			->from("designation")
			->join("department", "department.desig_id = designation.id")
			->join("category", "candidate_req.job_id = job_details.id")
			->where('designation.id', $id)
			->get();

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}


	public function dblike($likearray)
	{
		if (count($likearray) > 0) {
			$this->db->group_start();
			$this->db->or_like($likearray);
			$this->db->group_end();
		}
		return $this;
	}

	public function dbwhere($type, $whr)
	{
		if ($type && !empty($whr)) {
			$this->db->group_start();
			if ($type === 'OR') {
				$this->db->or_where($whr);
			}
			if ($type === 'IN' && isset($whr[0]) && isset($whr[1]) && !empty($whr[1])) {
				$this->db->where_in($whr[0], $whr[1]);
			}
			if ($type === 'NOTIN' && isset($whr[0]) && isset($whr[1]) && !empty($whr[1])) {
				$this->db->where_not_in($whr[0], $whr[1]);
			}
			$this->db->group_end();
		}
		return $this;

		/* 
    	$this->db->where_not_in()
    	$this->db->or_where_not_in() 
    	*/
	}
	public function dbOrderBy($orderBy)
	{
		if (is_array($orderBy)) {
			foreach ($orderBy as $orderkey => $orderval) {
				$this->db->order_by($orderkey, $orderval);
			}
		}
		return $this;
	}
	public function dbGroupBy($groupBy)
	{
		if ($groupBy) {
			$this->db->group_by($groupBy);
		}
		return $this;
	}



	public function getSum($table, $column, $whr = null)
	{
		$this->db->select_sum($column);
		$this->db->from($table);
		if ($whr) {
			$this->db->where($whr);
		}

		return $this->db->get()->row()->$column;
	}



	function save($table, $array)
	{
		if (!array_key_exists('created_at', $array)) {
			$array['created_at'] = now();
		}
		if (!array_key_exists('updated_at', $array)) {
			$array['updated_at'] = now();
		}

		$this->db->insert($table, $array);
		return $this->db->insert_id();
	}

	function insert($table, $array)
	{
		$this->db->insert($table, $array);
		return $this->db->insert_id();
	}

	function update($table, $array, $where)
	{
		/* if(!array_key_exists('updated_at', $array)){
    		$array['updated_at'] = now();
    	} */
		$this->db->where($where);
		return $this->db->update($table, $array);
	}

	function update_t($table, $array)
	{
		/* if(!array_key_exists('updated_at', $array)){
    		$array['updated_at'] = now();
    	} */

		return $this->db->update($table, $array);
	}

	function updateIncrement($table, $array, $where)
	{
		$updateArray = array();
		foreach ($array as $col)
			$updateArray[] = $col;

		$updateArray[] = array('col' => 'updated_at', 'val' => now(), 'action' => TRUE);

		foreach ($updateArray as $updateCol) {
			$this->db->set($updateCol['col'], $updateCol['val'], ($updateCol['action']) ? TRUE : FALSE);
		}

		$this->db->where($where);
		return $this->db->update($table);
	}

	function delete($table, $where)
	{
		$this->db->where($where);
		return $this->db->delete($table);
	}

	function slug($string)
	{
		return strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', $string));
	}

	public function GetList($table, $value, $joins, $likearray, $where, $limit, $offset, $order_by, $order)
	{
		$this->db->start_cache();
		$this->db->select($value);

		if (is_array($joins) && count($joins) > 0) {
			foreach ($joins as $k => $v) {
				$this->db->join($v['table'], $v['condition'], $v['jointype']);
			}
		}
		if (!empty($where)) {
			$this->db->where($where);
		}
		if (!empty($likearray)) {
			$this->db->where($likearray);
		}
		$this->db->order_by($order_by, $order);
		$this->db->stop_cache();
		$query['total_records'] = $this->db->get($table)->num_rows();
		$query['results'] = $this->db->get($table, $limit, $offset)->result();

		return $query;
	}

	/**
	 * count number of record in table 
	 * @param string $table
	 * @param string/array $whr
	 */
	public function countRecord($table, $where = NULL, $select = '*', $joins = NULL, $likearray = NULL, $groupby = FALSE)
	{
		$this->db->from($table);
		$this->db->select($select);

		// where condition
		if ($where) {
			$this->db->where($where);
		}

		if (is_array($joins) && !empty($joins)) {
			foreach ($joins as $joinInfo) {
				$joinType = (array_key_exists('jointype', $joinInfo)) ? $joinInfo['jointype'] : 'LEFT';
				$this->db->join($joinInfo['table'], $joinInfo['condition'], $joinType);
			}
		}

		/* if(count($likearray) > 0){
    		$this->db->group_start();
    		$this->db->or_like($likearray);
    		$this->db->group_end();
    	} */

		if ($likearray != '') {
			if (is_array($likearray)) {
				if (!empty($likearray)) {
					$this->db->group_start();
					$this->db->or_like($likearray);
					$this->db->group_end();
				}
			} else {
				$this->db->group_start();
				$this->db->or_like($likearray);
				$this->db->group_end();
			}
		}

		if ($groupby) {
			$this->db->group_by($groupby);
		}


		return $this->db->count_all_results();
	}


	/**
	 * this function will change status of is_active Column (if status 1 then set 0 and if status 0 then set 1 autometically from query)
	 * @param string $table table name  
	 * @param int $id   unique id for whr condition 
	 */
	/** all record function use to find  all record by table only  order wise **/
	public function changeStatus($table, $id)
	{
		$query = "UPDATE " . $table . " SET is_active = if(is_active='0','1','0' ) Where id=" . $id;
		return $this->db->query($query);
	}



	/**
	 * Create Pagination and Pagination Link of table record 
	 * get result based on pagination and condition 
	 * 
	 * @param string $baseUrl
	 * @param string $table
	 * @param array/string $where
	 * @param string/array $select  list of column for select , NOTE: add column alias during join used (array('*','table.col1 as column1','table.col2 as column2'))
	 * @param array $joins default empty array , default join type LEFT  array('table' => tablename,'condition'=>condition,'jointype'=> 'LEFT,RIGHT,OTHER..');
	 * @param array $likearray default empty array like array are join with OR (title LIKE %title% OR title1 LIKE %title1%)
	 * @param int $limit  default false
	 * @param int $offset default false
	 * @param string/array $orderby default id order by column
	 * @param string  $order default DESC ,  order = ASC or DESC
	 * @param string/array $groupby
	 * @return array result = record result , pageLink = page link (html of number of page link)
	 */
	public function createPagination($baseUrl, $table, $where = NULL, $select = '*', $joins = array(), $likearray = array(), $limit = FALSE, $offset = 0, $orderby = 'id', $order = 'DESC', $groupby = FALSE)
	{
		$this->load->library('pagination');
		$configParam = array();
		$configParam['base_url'] = $baseUrl;
		$configParam['total_rows'] = self::countRecord($table, $where, $select, $joins, $likearray, $groupby);
		$configParam['per_page'] = $limit;
		$configParam['reuse_query_string'] = TRUE;
		$configParam['num_links'] = 3;

		$configParam['full_tag_open'] = '<ul class="pagination pagination-rounded justify-content-end mb-0">';
		$configParam['full_tag_close'] = '</ul>';
		$configParam['first_link'] = 'First';
		$configParam['last_link'] = 'Last';
		$configParam['first_tag_open'] = '<li class="page-item"><span class="page-link">';
		$configParam['first_tag_close'] = '</span></li>';
		$configParam['prev_link'] = '&laquo';
		$configParam['prev_tag_open'] = '<li class="page-item"><span class="page-link">';
		$configParam['prev_tag_close'] = '</span></li>';
		$configParam['next_link'] = '&raquo';
		$configParam['next_tag_open'] = '<li class="page-item"><span class="page-link">';
		$configParam['next_tag_close'] = '</span></li>';
		$configParam['last_tag_open'] = '<li class="page-item"><span class="page-link">';
		$configParam['last_tag_close'] = '</span></li>';
		$configParam['cur_tag_open'] = '<li class="page-item active"><span class="page-link"><a href="javascript:void(0)">';
		$configParam['cur_tag_close'] = '</a></span></li>';
		$configParam['num_tag_open'] = '<li class="page-item"><span class="page-link">';
		$configParam['num_tag_close'] = '</span></li>';

		$this->pagination->initialize($configParam);
		$output = array(
			'page_link' => $this->pagination->create_links(),
			'totalRecord' => $configParam['total_rows'],
		);
		return $output;
	}


	public function doUpload($fileUpload, $path, $fileName = FALSE, $type = false)
	{
		$this->load->library('upload');
		if (!$type) {
			$type = implode('|', $this->imageExtensions);
		}
		//$configParam = array();
		$configParam['upload_path']          = './' . $path;
		$configParam['allowed_types']        = $type;
		$configParam['max_size'] = '10240';
		$configParam['overwrite'] = false;
		$configParam['remove_spaces'] = TRUE;
		if ($fileName) {
			$configParam['file_name'] = str_replace('.', '_', $fileName);
		}

		if (!is_dir($configParam['upload_path'])) {
			mkdir($configParam['upload_path']);
		}
		$this->upload->initialize($configParam);

		if ($this->upload->do_upload($fileUpload)) {
			$data = $this->upload->data();
			$file_name = $data['file_name'];
			$this->create_thumb($file_name, $path);

			return $file_name;
		} else {
			echo $this->upload->display_errors();
			return false;
		}
	}

	public function create_thumb($file, $path, $width = '', $height = '')
	{
		$this->load->library('image_lib');
		$upPath = './' . $path . 'thumb';
		if (!is_dir($upPath)) {
			mkdir($upPath, 0777);
		}
		$config['image_library'] = 'gd2';
		$config['source_image'] = './' . $path . $file;
		$config['create_thumb'] = FALSE;
		$config['maintain_ratio'] = FALSE;
		if ($width != '') {
			$config['width'] = $width;
		} else {
			$config['width'] = 60;
		}

		if ($height != '') {
			$config['height'] = $height;
		} else {
			$config['height'] = 60;
		}

		$config['new_image'] = $upPath . '/' . $file;

		$this->image_lib->initialize($config);

		$this->image_lib->resize();

		if (!$this->image_lib->resize()) {
			$data['error'] = $this->image_lib->display_errors();
		} else {
			$this->image_lib->clear();
		}
	}

	public function ImageCrop($file_data, $upload_path, $x1, $y2, $w, $h)
	{

		if (!file_exists($upload_path)) {
			@mkdir($upload_path);
		} else {
			@chdir($upload_path);
		}


		$config['image_library'] = 'gd2';
		$config['source_image'] = $upload_path . '/' . $file_data['file_name'];
		$config['x_axis'] = $x1;
		$config['y_axis'] = $y2;
		$config['maintain_ratio'] = FALSE;
		$config['width'] = $w;
		$config['height'] = $h;
		$config['quality'] = '90%';

		// echo "<pre>"; print_r($config);exit;
		$this->load->library('image_lib', $config);
		$this->image_lib->initialize($config);
		if (!$this->image_lib->crop()) {
			echo $this->image_lib->display_errors();
		}
	}

	/**
	 * resize/Crop image
	 * @param  $createImage
	 */
	public function resizeImage($fileName, $folder_path, $width, $height, $fileTmpName = '')
	{

		if (!file_exists($folder_path . $width . 'X' . $height)) {
			@mkdir($folder_path . $width . 'X' . $height, 0777);
		} else {
			@chdir($folder_path . $width . 'X' . $height, 0777);
		}

		if (file_exists($folder_path . $width . 'X' . $height . '/' . $fileName)) {
			unlink($folder_path . $width . 'X' . $height . '/' . $fileName);
		}


		$this->load->library('image_lib');
		$config['image_library'] = 'gd2';

		$config['maintain_ratio']	= TRUE;
		/* $config['quality']  	= '80%'; */
		$config['master_dim']  	= 'auto';
		$config['source_image'] = $fileTmpName;
		$config['new_image'] = $folder_path . $width . 'X' . $height . '/' . $fileName;
		$config['width'] = $width;
		$config['height'] = $height;

		$this->image_lib->initialize($config);
		$this->image_lib->resize();

		$this->image_lib->clear();
	}


	/**
	 * Send mail
	 * 
	 * @param string $to
	 * @param string $subject
	 * @param string $text
	 * @param string $template
	 * @param array $data
	 * @return boolean
	 */
	public function send_mail($to, $subject, $text, $template = false, $data = false)
	{
		try {
			if ($template && !empty($data)) {
				$text = $this->load->view($template, array('data' => $data), TRUE);
			}

			$config = array(
				'protocol' => $this->config->config['smtp_protocol'],
				'smtp_host' => $this->config->config['smtp_host'],
				'smtp_port' => $this->config->config['smtp_port'],
				'smtp_user' => $this->config->config['smtp_user'],
				'smtp_pass' => $this->config->config['smtp_password'],
				'mailtype' => 'html',
				'charset' => $this->config->config['charset']
			);
			$this->load->library('email', $config);
			$this->email->set_newline("\r\n");

			$this->email->from($this->config->config['smtp_set_from'], $this->config->config['smtp_from_name']);
			$this->email->reply_to($this->config->config['smtp_replay_to'], $this->config->config['replay_to_name']);

			$this->email->to($to);
			$this->email->subject($subject);
			$this->email->message($text);
			$result = $this->email->send();
			// echo $this->email->print_debugger();
			// exit();
			return $result;
		} catch (Exception $e) {
			echo '<pre>';
			print_r($e);
			echo '</pre>';
			exit;
		}
	}


	/**
	 * Convert Currency To CAD(C)
	 * $from_Currency (Currency like INR,USD)
	 * $to_currency  (Default Currency CAD)
	 * $amount  (Number of Amount)
	 */
	public function currencyConverter($from_Currency, $amount)
	{
		if ($from_Currency && $amount) {
			$from_Currency = urlencode($from_Currency);
			$to_Currency = urlencode('CAD');
			$get = file_get_contents("https://finance.google.com/finance/converter?a=1&from=$from_Currency&to=$to_Currency");
			$get = explode("<span class=bld>", $get);
			$get = explode("</span>", $get[1]);
			$converted_currency = preg_replace("/[^0-9\.]/", '', $get[0]);
			return $converted_currency * $amount;
		} else {
			return false;
		}
	}

	public function createAlias($tableName, $separator = '_')
	{
		$tableAlias = array();
		if (is_array($tableName)) {
			foreach ($tableName as $tableData) {
				$table_name = isset($tableData[0]) ? $tableData[0] : false;
				$tblAlias = isset($tableData[1]) ? $tableData[1] : $table_name;
				$aliasIgnore = isset($tableData[2]) ? $tableData[2] : array();
				$table_alias = isset($tableData[3]) ? $tableData[3] : $table_name;

				if ($table_name && $tblAlias) {
					$tablequery = $this->db->query('SELECT `COLUMN_NAME` FROM information_schema.columns WHERE TABLE_SCHEMA = "' . $this->db->database . '" AND TABLE_NAME = "' . $table_name . '"')->result();
					foreach ($tablequery as $row) {
						if (!in_array($row->COLUMN_NAME, $aliasIgnore)) {
							$tableAlias[] = $table_alias . '.' . $row->COLUMN_NAME . ' AS ' . $tblAlias . $separator . $row->COLUMN_NAME;
						}
					}
				}
			}
		}
		return $tableAlias;
	}

	public function dateToTimestamp($date)
	{
		return strtotime($date);
	}

	public function removeTimeFromDate($date, $no, $type)
	{
		//     	$date = (is_numeric($date)) ? $date : $this->dateToTimestamp($date);
		if ($no > 0) {
			$type = ($no > 1) ? $type . 's' : $type;
			$date = strtotime('-' . $no . ' ' . $type, $date);
		}
		return $date;
	}

	/**
	 * ref of timespan function of codeigniter 
	 * @param unknown_type $startDate
	 * @param unknown_type $endDate
	 * @return multitype:number
	 */
	public function getDateDiff($startDate, $endDate)
	{
		$dayInfo = array();

		if ($startDate && $endDate && is_numeric($startDate) && is_numeric($endDate)) {
			$startDate = ($endDate <= $startDate) ? 1 : $endDate - $startDate;
			////count year
			$years = floor($startDate / 31557600);
			$dayInfo['year'] = $years;

			//// count month
			$months = floor($startDate / 2629743);
			$dayInfo['month'] = $months;

			/// count week
			$weeks = floor($startDate / 604800);
			$dayInfo['week'] = $weeks;

			/// count day 
			$days = floor($startDate / 86400);
			$dayInfo['day'] = $days;

			/// count hour 
			$hours = floor($startDate / 3600);
			$dayInfo['hour'] = $hours;

			/// count minute
			$minutes = floor($startDate / 60);
			$dayInfo['minute'] = $minutes;
		}

		return $dayInfo;
	}

	public function getDateDiffByTime($startDate, $endDate, $skip = array())
	{
		$dayInfo = array();

		if ($startDate && $endDate && is_numeric($startDate) && is_numeric($endDate)) {
			$startDate = ($endDate <= $startDate) ? 1 : $endDate - $startDate;

			////count year
			if (!in_array('year', $skip)) {
				$years = floor($startDate / 31557600);
				$dayInfo['year'] = (int)$years;
				$startDate = ($years > 0) ? ($startDate - ($years * 31557600)) : $startDate;
			}

			//// count month
			if (!in_array('month', $skip)) {
				$months = floor($startDate / 2629743);
				$dayInfo['month'] = (int)$months;
				$startDate = ($months > 0) ? ($startDate - ($months * 2629743)) : $startDate;
			}

			/// count week
			if (!in_array('week', $skip)) {
				$weeks = floor($startDate / 604800);
				$dayInfo['week'] = (int)$weeks;
				$startDate = ($weeks > 0) ? ($startDate - ($weeks * 604800)) : $startDate;
			}

			/// count day
			if (!in_array('day', $skip)) {
				$days = floor($startDate / 86400);
				$dayInfo['day'] = (int)$days;
				$startDate = ($days > 0) ? ($startDate - ($days * 86400)) : $startDate;
			}

			/// count hour
			if (!in_array('hour', $skip)) {
				$hours = floor($startDate / 3600);
				$dayInfo['hour'] = (int)$hours;
				$startDate = ($hours > 0) ? ($startDate - ($hours * 3600)) : $startDate;
			}

			/// count minute
			if (!in_array('minute', $skip)) {
				$minutes = floor($startDate / 60);
				$dayInfo['minute'] = (int)$minutes;
				$startDate = ($minutes > 0) ? ($startDate - ($minutes * 60)) : $startDate;
			}
		}
		return $dayInfo;
	}


	/**
	 * For get row by id
	 * 
	 */

	public function getById($table, $where, $value)
	{
		$query = $this->db->get_where($table, $where)->row($value);
		return $query;
	}


	/**
	 * for create slug
	 * 
	 */

	public function create_slug($string)
	{
		$slug = trim($string);
		$slug = strtolower($slug);
		$slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $slug);

		return $slug;
	}

	function randColor($numColors)
	{
		$chars = "ABCDEF0123456789";
		$size = strlen($chars);
		$str = '';
		for ($i = 0; $i < $numColors; $i++) {
			for ($j = 0; $j < 6; $j++) {
				$str .= $chars[rand(0, $size - 1)];
			}
		}
		return $str;
	}


	/**
	 * Generate Unique String
	 * 
	 */
	public function generate_unique_string($num)
	{
		$randstr = '';
		srand((float) microtime(TRUE) * 1000000);
		//our array add all letters and numbers if you wish
		$chars = array(
			'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'p',
			'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '1', '2', '3', '4', '5',
			'6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K',
			'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
		);
		$length = count($chars) - 1;
		for ($rand = 0; $rand <= $num; $rand++) {
			$random = rand(0, count($chars) - 1);
			$randstr .= $chars[$random];
		}

		return $randstr;
	}


	function generateRandomString()
	{
		$randstr = '';
		srand((float) microtime(TRUE) * 1000000);
		//our array add all letters and numbers if you wish
		$chars = array(
			'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'p',
			'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '1', '2', '3', '4', '5',
			'6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K',
			'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
		);
		$length = count($chars) - 1;
		for ($rand = 0; $rand <= 10; $rand++) {
			$random = rand(0, count($chars) - 1);
			$randstr .= $chars[$random];
		}

		return $randstr;
	}

	public function generateSlug($text)
	{
		// replace non letter or digits by -
		$text = preg_replace('~[^\pL\d]+~u', '-', $text);

		// transliterate
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

		// remove unwanted characters
		$text = preg_replace('~[^-\w]+~', '', $text);

		// trim
		$text = trim($text, '-');

		// remove duplicate -
		$text = preg_replace('~-+~', '-', $text);

		// lowercase
		$text = strtolower($text);

		if (empty($text)) {
			return 'n-a';
		}

		return $text;
	}
}
