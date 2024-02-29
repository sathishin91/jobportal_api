<?php

class Sms
{
    protected $api_key;
    protected $sender;
    protected $base_URL;

    /**
     * Initialize the Sms environment.
     *
     * @param api_key   - API key generated from SMS Account
     * @param sender    - Sender ID assigned to the account
     * @param base_URL  - BASE URL of SMS Service
     */
    public function __construct($api_key, $sender, $base_URL)
    {
        $this->setApiKey($api_key);
        $this->setSender($sender);
        $this->setBaseURL($base_URL);
    }

    /**
     * set api_key.
     *
     * @param api_key - API key generated from SMS Account
     */
    public function setApiKey($api_key)
    {
        $this->api_key = $api_key;
    }

    /**
     * set sender.
     *
     * @param sender - Sender ID assigned to the account
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
        var_dump($this->sender);
    }

    /**
     * set base_URL.
     *
     * @param base_URL - BASE URL of SMS Service
     */
    public function setBaseURL($base_URL)
    {
        $this->base_URL = $base_URL;
    }

    /**
     * function for sending sms.
     *
     * @param to   - Receiver numbers(in comma ',' separtion)to which the message has to be sent
     * @param msg  - Message content
     * @param optional  - Optional parameters.
     *               time - Date and time for scheduling an SMS.
     *               unicode    - Specify that the message to be sent is in unicode format(0/1/auto).
     *               flash      - To specify that the message is to be sent in the flash format(0/1).
     *               dlrurl     - The encoded URL to receive delivery reports.
     *               custom     - Custom message ID
     *               format     - Format of the response(JSON).
     *               callback   - Callback function for JSONP response format.
     *               port       - Port number to which SMS has to be sent
     *
     * @return response - response  in requested format
     */
    public function sendSms($to, $msg, $optional = [])
    {
        if (!isset($this->base_URL)) {
            $response = ' Enter the base_URL';
        } elseif (!isset($this->api_key)) {
            $response = ' Enter the api_key ';
        } elseif (!isset($this->sender)) {
            $response = 'Enter the sender Id';
        } elseif (!isset($to)) {
            $response = 'Enter the Receiver numbers';
        } elseif (!isset($msg)) {
            $response = 'Enter the Receiver numbers';
        } else {
            $encoded_msg = utf8_encode($msg);
            $URL         = $this->base_URL . '&method=sms' . '&api_key=' . $this->api_key . '&sender=' . $this->sender . '&to=' . $to . '&message=' . $encoded_msg;
            $URL         = $this->addOptionalParameters($URL, $optional);
            $response    = $this->triggerURL($URL);
        }

        return $response;
    }

    /**
     * function for sending sms using xml api.
     *
     * @param  xml      -    XML data
     * @param  optional -    Optional parameters.
     *                  format     - Format of the response(JSON)..
     *                  callback   - Callback function for JSONP response format
     *
     * @return response - response in requested format
     */
    public function sendSmsUsingXmlApi($xml, $optional = [])
    {
        if (!isset($this->base_URL)) {
            $response = ' Enter the base_URL';
        } elseif (!isset($this->api_key)) {
            $response = ' Enter the api_key ';
        } elseif (!isset($xml)) {
            $response = 'Enter the XML data';
        } else {
            $encoded_xml = urlencode($xml);
            $URL         = $this->base_URL . '&method=sms.xml' . '&api_key=' . $this->api_key . '&xml=' . $encoded_xml;
            $URL         = $this->addOptionalParameters($URL, $optional);
            $response    = $this->triggerURL($URL);
        }

        return    $response;
    }

    /**
     * function for sending sms using json api.
     *
     * @param json          -    JSON data
     * @param optional      -    Optional parameters.
     *                       format     - Format of the response(JSON)
     *                       callback   - Callback function for JSONP response format
     *
     * @return response -    response in requested format
     */
    public function sendSmsUsingJsonApi($json, $optional = [])
    {
        if (!isset($this->base_URL)) {
            $response = ' Enter the base_URL';
        } elseif (!isset($this->api_key)) {
            $response = ' Enter the api_key ';
        } elseif (!isset($json)) {
            $response = 'Enter the  JSON data';
        } else {
            $encoded_json = urlencode($json);
            $URL          = $this->base_URL . '&method=sms.json' . '&api_key=' . $this->api_key . '&json=' . $encoded_json;
            $URL          = $this->addOptionalParameters($URL, $optional);
            $response     = $this->triggerURL($URL);
        }

        return $response;
    }

    /**
     * function for knowing sms staus.
     *
     * @param ids           -     Message IDs(should be , separated)
     * @param optional      -     Optional parameters.
     *                        format     - Format of the response(JSON).
     *                        numberinfo - Flag to query service provider and location data(0/1)
     *
     * @return response -     response in requested format
     */
    public function smsStatusPull($id, $optional = [])
    {
        if (!isset($this->base_URL)) {
            $response = ' Enter the base_URL';
        } elseif (!isset($this->api_key)) {
            $response = ' Enter the api_key ';
        } elseif (!isset($id)) {
            $response = 'Enter the Message IDs';
        } else {
            $URL      = $this->base_URL . '&method=sms.status' . '&api_key=' . $this->api_key . '&id=' . $id;
            $URL      = $this->addOptionalParameters($URL, $optional);
            $response = $this->triggerURL($URL);
        }

        return $response;
    }

    /**
     * function for sms stauts push.
     *
     * @param to        -  Receiver numbers(in comma ',' separtion)to which the message has to be sent
     * @param msg       - message content
     * @param dlr_url   - The encoded URL to receive delivery reports
     * @param optional  - Optional parameters.
     *                    format     - Format of the response(JSON)
     *
     * @return response - response in requested format
     */
    public function smsStatusPush($to, $msg, $dlr_url)
    {
        if (!isset($this->base_URL)) {
            $response = ' Enter the base_URL';
        } elseif (!isset($this->api_key)) {
            $response = ' Enter the api_key ';
        } elseif (!isset($this->sender)) {
            $response = ' Enter the senderID ';
        } elseif (!isset($to)) {
            $response = 'Enter the Receiver numbers';
        } elseif (!isset($msg)) {
            $response = 'Enter the message';
        } elseif (!isset($dlr_url)) {
            $response = 'Enter the dlr_url ';
        } else {
            $encoded_msg    = utf8_encode($msg);
            $encode_dlr_url = urlencode($dlr_url);
            $URL            = $this->base_URL . 'method=sms' . '&api_key=' . $this->api_key . '&sender=' . $this->sender . '&to=' . $to . '&message=' . $encoded_msg . '&dlr_url=' . $encode_dlr_url;
            $response       = $this->triggerURL($URL);
        }

        return $response;
    }

    /**
     * function for sendng sms to optin group.
     *
     * @param name      - name of optin group
     * @param msg       - message content
     * @param optional  - Optional parameters.
     *                    time - Date and time for scheduling an SMS.
     *                    unicode    - Specify that the message to be sent is in unicode format(0/1/auto).
     *                    flash      - To specify that the message is to be sent in the flash format(0/1).
     *                    format     - Format of the response(JSON).
     *                    callback   - Callback function for JSONP response format
     *
     * @return response -  response in requested format
     */
    public function smsToOptinGroup($msg, $name = '', $id = '', $optional = [])
    {
        if (!isset($this->base_URL)) {
            $response = ' Enter the base_URL';
        } elseif (!isset($this->api_key)) {
            $response = ' Enter the api_key ';
        } elseif (!isset($this->sender)) {
            $response = ' Enter the senderID ';
        } elseif (!isset($id)) {
            $response = 'Enter the ID';
        } elseif (!isset($msg)) {
            $response = 'Enter the message';
        } elseif (!isset($name)) {
            $response = 'Enter the name ';
        } else {
            $encoded_msg = utf8_encode($msg);
            $URL         = $this->base_URL . 'method=optin' . '&api_key=' . $this->api_key . '&sender=' . $this->sender . '&message=' . $encoded_msg . '&name=' . $name;
            $URL         = $this->addOptionalParameters($URL, $optional);
            if ($id != null) {
                $URL = $URL . '&id=' . $id;
            }
            if ($name != null) {
                $URL = $URL . '&name=' . $name;
            } else {
                return 'wrong id/name';
            }
            $response = $this->triggerURL($URL);
        }

        return $response;
    }

    /**
     * function for adding contacts to the group.
     *
     * @param number    - mobile number of the contact
     * @param name      - group name
     * @param optional  - Optional parameters.
     *                    fullname - name of the contact to be added name of the contact.
     *                    email    - email of the contact.
     *                    action.  - Flag to specify the action(add/delete).
     *                    format   - Format of the response(JSON)
     *
     * @return response -  response in requested format
     */
    public function addContactsToGroup($name, $number, $optional = [])
    {
        if (!isset($this->base_URL)) {
            $response = ' Enter the base_URL';
        } elseif (!isset($this->api_key)) {
            $response = ' Enter the api_key ';
        } elseif (!isset($this->sender)) {
            $response = ' Enter the senderID ';
        } elseif (!isset($number)) {
            $response = 'Enter the number';
        } elseif (!isset($name)) {
            $response = 'Enter the name ';
        } else {
            $URL      = $this->base_URL . 'method=groups.register' . '&api_key=' . $this->api_key . '&sender=' . $this->sender . '&number=' . $number . '&name=' . $name;
            $URL      = $this->addOptionalParameters($URL, $optional);
            $response = $this->triggerURL($URL);
        }

        return $response;
    }

    /**
     * funtion for sending sms to group.
     *
     * @param name      - group name
     * @param msg       - message content
     * @param optional  - Optional parameters
     * @param optional  - Optional parameters.
     *                    time - Date and time for scheduling an SMS.
     *                    unicode    - Specify that the message to be sent is in unicode format(0/1/auto).
     *                    flash      - To specify that the message is to be sent in the flash format(0/1).
     *                    format     - Format of the response(JSON)..
     *                    callback   - Callback function for JSONP response format
     *
     * @return response - response in requested format
     */
    public function sendMessageToGroup($msg, $name, $group_id, $optional = [])
    {
        if (!isset($this->base_URL)) {
            $response = ' Enter the base_URL';
        } elseif (!isset($this->api_key)) {
            $response = ' Enter the api_key ';
        } elseif (!isset($this->sender)) {
            $response = ' Enter the senderID ';
        } elseif (!isset($group_id)) {
            $response = 'Enter the group_id';
        } elseif (!isset($name)) {
            $response = 'Enter the name ';
        } elseif (!isset($msg)) {
            $response = 'Enter the message ';
        } else {
            $encoded_msg = utf8_encode($msg);
            $URL         = $this->base_URL . '&method=groups' . '&api_key=' . $this->api_key . '&name=' . $name . '&id=' . $group_id . '&sender=' . $this->sender . '&message=' . $encoded_msg;
            $URL         = $this->addOptionalParameters($URL, $optional);
            $response    = $this->triggerURL($URL);
        }

        return $response;
    }

    /**
     * function for modifying the scheduled sms.
     *
     * @param group_id  - group id that has to be deleted
     * @param time      - Time to which the slot needs to be re-scheuled
     * @param optional  - Optional parameters.
     *                    format     - Format of the response(JSON)
     *
     * @return response - response in requested format
     */
    public function editSchedule($time, $group_id, $optional = [])
    {
        if (!isset($this->base_URL)) {
            $response = ' Enter the base_URL';
        } elseif (!isset($this->api_key)) {
            $response = ' Enter the api_key ';
        } elseif (!isset($group_id)) {
            $response = 'Enter the group_id';
        } elseif (!isset($time)) {
            $response = ' Enter the time ';
        } else {
            $URL      = $this->base_URL . '&method=sms.schedule' . '&api_key=' . $this->api_key . '&groupid=' . $group_id . '&time=' . $time . '&task=modify';
            $URL      = $this->addOptionalParameters($URL, $optional);
            $response = $this->triggerURL($URL);
        }

        return $response;
    }

    /**
     * function for deleting the scheduled sms.
     *
     * @param group_id  - group id that has to be deleted
     * @param optional  -  Optional parameters.
     *                    format     - Format of the response(JSON)
     *
     * @return response - response in requested format
     */
    public function deleteScheduledSms($group_id, $optional = [])
    {
        if (!isset($this->base_URL)) {
            $response = ' Enter the base_URL';
        } elseif (!isset($this->api_key)) {
            $response = ' Enter the api_key ';
        } elseif (!isset($group_id)) {
            $response = 'Enter the group_id';
        } else {
            $URL      = $this->base_URL . '&method=sms.schedule' . '&api_key=' . $this->api_key . '&groupid=' . $group_id;
            $URL      = $this->addOptionalParameters($URL, $optional);
            $response = $this->triggerURL($URL);
        }

        return $response;
    }

    /**
     * function for checking the credits available in account.
     *
     * @param  optional - Optional parameters.
     *                    format     - Format of the response(JSON)
     *
     * @return response - response in requested format
     */
    public function creditAvailabilityCheck($optional = [])
    {
        if (!isset($this->base_URL)) {
            $response = ' Enter the base_URL';
        } elseif (!isset($this->api_key)) {
            $response = ' Enter the api_key ';
        } else {
            $URL      = $this->base_URL . '&method=account.credits' . '&api_key=' . $this->api_key;
            $URL      = $this->addOptionalParameters($URL, $optional);
            $response = $this->triggerURL($URL);
        }

        return $response;
    }

    /**
     * function for SI Lookup.
     *
     * @param to        -  Receiver numbers(in comma ',' separtion)to which the message has to be sent
     * @param optional  - Optional parameters.
     *                    format     - Format of the response(JSON)
     *
     * @return response - response in requested format
     */
    public function SILookup($to, $optional = [])
    {
        if (!isset($this->base_URL)) {
            $response = ' Enter the base_URL';
        } elseif (!isset($this->api_key)) {
            $response = ' Enter the api_key ';
        } elseif (!isset($to)) {
            $response = 'Enter the Receiver numbers';
        } else {
            $URL      = $this->base_URL . '&method=hlr' . '&api_key=' . $this->api_key . '&to=' . $to;
            $URL      = $this->addOptionalParameters($URL, $optional);
            $response = $this->triggerURL($URL);
        }

        return $response;
    }

    /**
     * functino for creating txtly.
     *
     * @param txtly_URL - URL that requires to be shortened and tracked
     * @param optional  - Optional parameters.
     *                    format     - Format of the response(JSON).
     *                    token      - Customized word unique for each txtly.
     *                    title      - A significant title to the txtly.
     *                    advanced   - Advanced analytics gives an option to track who (Recipient mobile numbers) visited.
     *                    track      - Location Track gives the city and state details of URL visitor(0/1).
     *                    attach     - Media file that requires to be compressed to a short link
     *
     * @return response - response in requested format
     */
    public function createTxtly($txtly_URL, $optional = [])
    {
        if (!isset($this->base_URL)) {
            $response = ' Enter the base_URL';
        } elseif (!isset($this->api_key)) {
            $response = ' Enter the api_key ';
        } elseif (!isset($txtly_URL)) {
            $response = 'Enter the txtly_URL';
        } else {
            $URL      = $this->base_URL . '&method=txtly.create' . '&api_key=' . $this->api_key . '&url=' . $txtly_URL;
            $URL      = $this->addOptionalParameters($URL, $optional);
            $response = $this->triggerURL($URL);
        }

        return $response;
    }

    /**
     * function for deleting the txtly.
     *
     * @param id        - Id of the txtly(in comma ',' separtion)
     * @param optional  - Optional parameters.
     *                    format     - Format of the response(JSON)
     *
     * @return response - response in requested format
     */
    public function deleteTxtly($id, $optional = [])
    {
        if (!isset($this->base_URL)) {
            $response = ' Enter the base_URL';
        } elseif (!isset($this->api_key)) {
            $response = ' Enter the api_key ';
        } elseif (!isset($id)) {
            $response = 'Enter the id';
        } else {
            $URL      = $this->base_URL . '&method=txtly' . '&api_key=' . $this->api_key . '&task=delete' . '&id=' . $id . '&app=1';
            $URL      = $this->addOptionalParameters($URL, $optional);
            $response = $this->triggerURL($URL);
        }

        return $response;
    }

    /**
     * function for getting textly report.
     *
     * @param  optional - Optional parameters.
     *                   format     - Format of the response(JSON)
     *
     * @return response - response in requested format
     */
    public function txtlyReportExtract($optional = [])
    {
        if (!isset($this->base_URL)) {
            $response = ' Enter the base_URL';
        } elseif (!isset($this->api_key)) {
            $response = ' Enter the api_key ';
        } else {
            $URL      = $this->base_URL . '&method=txtly&api_key=' . $this->api_key . '&app=1';
            $URL      = $this->addOptionalParameters($URL, $optional);
            $response = $this->triggerURL($URL);
        }

        return $response;
    }

    /**
     * funcition for getting log about individual txtly.
     *
     * @param  id       - Id of the txtly(in comma ',' separtion)
     * @param  optional - Optional parameters.
     *                    format     - Format of the response(JSON)
     *
     * @return response - response in requested format
     */
    public function pullLogForIndividualtxtl($id, $optional = [])
    {
        if (!isset($this->base_URL)) {
            $response = ' Enter the base_URL';
        } elseif (!isset($this->api_key)) {
            $response = ' Enter the api_key ';
        } elseif (!isset($id)) {
            $response = 'Enter the id';
        } else {
            $URL      = $this->base_URL . '&method=txtly.logs' . '&api_key=' . $this->api_key . '&id=' . $id . '&app=1';
            $URL      = $this->addOptionalParameters($URL, $optional);
            $response = $this->triggerURL($URL);
        }

        return $response;
    }

    /**
     * @param   URL -  URL
     *
     * @return response - return response back to the caller in the  cURL session response
     */
    protected function triggerURL($URL)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $URL);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        $err      = curl_error($curl);
        curl_close($curl);

        return $response;
    }

    /**
     * function for adding optional parameters.
     *
     * @param URL -URL without optional parameters
     * @param optional - optional parameters
     */
    public function addOptionalParameters($URL, $optional = [])
    {
        if (isset($optional['dlr_url'])) {
            $URL = $URL . '&dlr_url=' . $optional['dlr_url'];
        }
        if (isset($optional['custom'])) {
            $URL = $URL . '&custom=' . $optional['custom'];
        }
        if (isset($optional['time'])) {
            $URL = $URL . '&time=' . $optional['time'];
        }
        if (isset($optional['unicode'])) {
            $URL = $URL . '&unicode=' . $optional['unicode'];
        }
        if (isset($optional['flash'])) {
            $URL = $URL . '&flash=' . $optional['flash'];
        }
        if (isset($optional['format'])) {
            $URL = $URL . '&format=' . $optional['format'];
        }
        if (isset($optional['callback'])) {
            $URL = $URL . '&callback=' . $optional['callback'];
        }
        if (isset($optional['port'])) {
            $URL = $URL . '&port=' . $optional['port'];
        }
        if (isset($optional['campaign'])) {
            $URL = $URL . '&campaign=' . $optional['campaign'];
        }
        if (isset($optional['numberinfo'])) {
            $URL = $URL . '&numberinfo=' . $optional['numberinfo'];
        }
        if (isset($optional['fullname '])) {
            $URL = $URL . '&fullname =' . $optional['fullname '];
        }
        if (isset($optional['email'])) {
            $URL = $URL . '&email=' . $optional['email'];
        }
        if (isset($optional['action'])) {
            $URL = $URL . '&action=' . $optional['action'];
        }
        if (isset($optional['token'])) {
            $URL = $URL . '&token=' . $optional['token'];
        }
        if (isset($optional['title'])) {
            $URL = $URL . '&title' . $optional['title'];
        }
        if (isset($optional['advanced'])) {
            $URL = $URL . '&advanced=' . $optional['advanced'];
        }
        if (isset($optional['track'])) {
            $URL = $URL . '&track=' . $optional['track'];
        }
        if (isset($optional['attach '])) {
            $URL = $URL . '&attach =' . $optional['attach'];
        }
        if (isset($optional['action'])) {
            $URL = $URL . '&action =' . $optional['action'];
        }

        return  $URL;
    }
}
