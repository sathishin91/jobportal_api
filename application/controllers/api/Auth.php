<?php
defined('BASEPATH') or exit('No direct script access allowed');
include_once './vendor/autoload.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class Auth extends CI_Controller
{
    // public $responseData = array('code' => 200, 'status' => 'success', 'message' => 'You have registered successfully'); // set API response array
    public function __construct()
    {
        parent::__construct();
        $this->load->model(["CommonModel", "ApiCommonModel",]);
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


    function getToken()
    {
        $secretKey = "seekk!@#$%2023";

        // Payload data (e.g., user ID, username, etc.)
        $payloadData = [
            'iss' => 'localhost',
            'aud' => 'localhost',
            'iat' => time(),
            'exp' => time() + 3600, // Token expiration time (1 hour from now)
        ];

        // Create the token
        $jwtToken = JWT::encode($payloadData, $secretKey, 'HS256');
        if ($jwtToken) {
            $this->responseData['token']  = $jwtToken;
        } else {
            $this->responseData['msg']    = "no token";
        }

        self::setOutPut();
    }
}
