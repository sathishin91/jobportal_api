<?php
defined('BASEPATH') or exit('No direct script access allowed');

/* Send an SMS using this aplication. You can run this file 3 different ways:
     *
     *    Download a local server like WAMP, MAMP or XAMPP. Point the web root
     *    directory to the folder containing this file, and load
     *    localhost:8888/client.php in a web browser.
   */

// include the Sms class
include_once 'Sms.php';
class Sms extends CI_Controller
{
    public $responseData = array('code' => 200, 'status' => 'success', 'message' => 'Your have registered successfully'); // set API response array
    public $customerRoleId    = null;
    public $DIR   = 'assets/images/';
    public function __construct()
    {
        parent::__construct();
        $this->load->model(["CommonModel", "ApiCommonModel", "UserModel"]);
        Header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
        Header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
        Header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed

    }
    public function call()
    {
        //instantiate a new Sms Rest Client with argument api,senderID,base_URL.
        $sms     = new Sms('xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', '<Sender_ID>', '<SMS_provider_URL>');
        $dlr_url = '<Delivered_Trigger_URL>?sent={sent}&delivered={delivered}&msgid={msgid}&sid={sid}&status={status}&reference={reference}&custom1={custom1}&custom2={custom2}';

        $obj = $sms->sendSms('<Mobile_Number>', 'hello', [
            //'dlr_url' => $dlr_url,
            //'time'    => '2017-06-11 11:17:55 AM',
            //'unicode' => '1',
            //'flash' => '1',
            //'format'  => 'json',
            //'port'    => '8223',
        ]);

        /*$xml="<?xml version='1.0' encoding='UTF-8'?><xmlapi>
            	<sender>RRRRRR</sender>
            	<message>xml test</message>
            	<unicode>1</unicode>
            	<flash>1</flash>
            	<campaign>xml test</campaign>
            	<dlrURL><![CDATA[http://example.php?sent={sent}&delivered={delivered}&msgid={msgid}&sid={sid}&status={status}&reference={reference}&custom1={custom1}&custom2={custom2}&credits={credits}]]></dlrURL>
            	<sms><to><Mobile_Number></to><custom>22</custom><custom1>99</custom1><custom2>988</custom2></sms>
            	<sms><to><Mobile_Number></to><custom>229</custom><custom1>995</custom1><custom2>98</custom2></sms>
            </xmlapi>";
            $obj = $sms->sendSmsUsingXmlApi($xml,['formate'=>'json']);*/

        /*$json = "{\"message\": \"test json\",
             \"sms\": [{
             	\"to\": \"<Mobile_Number>\",
             	\"msgid\": \"1\",\"message\": \"test json\",
             	\"custom1\": \"11\",
             	\"custom2\": \"22\",
             	\"sender\": \"RRRRRR\"
             	 },
             	 {
             	 	\"to\": \"<Mobile_Number>\",
             	 	 \"msgid\": \"2\",
             	 	 \"custom1\": \"1\",
             	 	 \"custom2\": \"2\"   }],
             	 	 \"unicode\": 1,
             	 	 \"flash\": 1,
             	 	 \"dlrurl\": \"<Delivered_Trigger_URL>?referenceid={reference}%26status={status}%26delivered={delivered}%26messageid={messageid}%26customid1={custom1}%26customid2={custom2}%26senttime={senttime}%26reference={reference}%26message={message}\"
             }";
            $obj = $sms->sendSmsUsingJsonApi($json,['formate'=>'json']);*/

        //$obj = $sms->smsStatusPull("xxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx:1",['formate'=>'json']);

        //$obj = $sms->smsStatusPush("<Mobile_Number>","hi......",$dlr_url);

        /*$obj = $sms->smsToOptinGroup("hello","tipusultan",['time'    => '2017-06-11 11:17:55 AM',
                'unicode' => '1',
                'flash' => '1',
                'formate'=>'json']);
            */

        //$obj = $sms->addContactsToGroup("RRRRRR","<Mobile_Number>",['fullname'=>'abc','formate'=>'json']);

        //$obj = $sms->sendMessageToGroup("helloo testing","<Sender ID>","<Mobile_Number>");

        //$obj = $sms->editSchedule("2017-09-23 11:17:55 AM","<Mobile_Number>",['formate'=>'json']);

        //$obj = $sms->deleteScheduledSms("<Mobile_Number>",['formate'=>'json']);

        //$obj = $sms->creditAvailabilityCheck(['formate'=>'json']);

        //$obj = $sms->SILookup('<Mobile_Number>', ['format' => 'json']);

        //$obj = $sms->createtxtly("https://in.yahoo.com",['format' => 'json']);

        //$obj = $sms->deletetxtly("205",['format' => 'json']);

        //$obj = $sms->txtlyReportExtract(['format' => 'json']);
        //$obj = $sms->pullLogForIndividualtxtl("223",['format' => 'json']);

        //$obj = $sms->smsStatusPull('msg-Id');

        echo '<pre>';
        print_r($obj);
    }
}
$main = new Sms();
$main->call();
