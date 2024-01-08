<?php
class UserModel extends CommonModel
{
	public $table = 'user';
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * get contry list
     * @param $contry_id
     * @param $list
     */
    public function getContry($contry_id=FALSE,$list=FALSE)
    {
    	$contryData = array();
    	if($contry_id && $contry_id!='' && $list)
    	{
    		$contryData = SELF::select_rec('country', 'id,name',array('id'=>$contry_id))->row();
    	}
    	else
    	{
    		$contryData = SELF::select_rec('country', 'id,name')->result();
    		if(isset($contryData))
    		{
    			$contryList = array();
    			foreach ($contryData as $contryInfo){
    				$contryList[$contryInfo->id] = $contryInfo->name;
    			}
    			$contryData = $contryList;
    		}
    	}
		return $contryData;    	
    }
    
    /**
     * get state list
     * @param $contry_id
     * @param $state_id
     * @param $list
     */
	public function getState($contry_id = FALSE,$state_id=FALSE,$list=FALSE)
    {
    	$stateData = array();
    	if($state_id && $state_id!='' && $list)
    	{
    		$stateData = SELF::select_rec('state', 'id,name',array('id'=>$state_id))->row();
    	}
    	else
    	{
    		$stateData = SELF::select_rec('state', 'id,name',array('country_id'=>$contry_id))->result();
    		if(isset($stateData))
    		{
    			$stateList = array();
    			foreach ($stateData as $stateInfo){
    				$stateList[$stateInfo->id] = $stateInfo->name;
    			}
    			$stateData = $stateList;
    		}
    	}
		return $stateData;    	
    }
    
    /**
     * get city list
     * @param $state_id
     * @param $city_id
     * @param $list
     */
	public function getCity($state_id = FALSE,$city_id=FALSE,$list=FALSE)
    {
    	$cityData = array();
    	if($city_id && $city_id!='' && $list)
    	{
    		$cityData = SELF::select_rec('city', 'id,name',array('id'=>$city_id))->row();
    	}
    	else
    	{
    		$cityData = SELF::select_rec('city', 'id,name',array('state_id'=>$state_id))->result();
    		if(isset($cityData))
    		{
    			$cityList = array();
    			foreach ($cityData as $cityInfo)
    			{
    				$cityList[$cityInfo->id] = $cityInfo->name;
    			}
    			$cityData = $cityList;
    		}
    	}
    	
		return $cityData;    	
    }


    /**
    * Get country name
    *
    * @param $country_id int
    */
    public function getCountryName($country_id = NULL){

        if ($country_id) {
            return SELF::select_rec('country', 'id,name',array('id'=>$country_id))->row_array();
        }else{
            return '';
        }
    }

    /**
    * Get country name
    *
    * @param $state_id int
    */
    public function getStateName($state_id = NULL){

        if ($state_id) {
            return SELF::select_rec('state', 'id,name',array('id'=>$state_id))->row_array();
        }else{
            return '';
        }
    }

    /**
    * Get country name
    *
    * @param $city_id int
    */
    public function getCityName($city_id = NULL){

        if ($city_id) {
            return SELF::select_rec('city', 'id,name',array('id'=>$city_id))->row_array();
        }else{
            return '';
        }
    }
}