<?php

/*
* Version 1.0
* tcpalitigatorlist.com API SDK
*/

class TCPA_API {

    protected $types = [];
    private $api_username = '';
    private $api_password = '';
    private $response_format = 'array';
    private $response_type = 'regular'; // boolean

    const ALL_TYPES = 'all';
    const TCPA_TYPE = 'tcpa';
    const DNC_TYPE = 'dnc';
    const DNC_FED_TYPE = 'dnc_fed';

    public function __construct($config = []) {
        $this->setConfig($config);
    }

    public function setConfig($config)
    {
        if ( !isset($config['api_username']) || !isset($config['api_password']) ){
            throw new Exception('Missing credentials');
        }
        $this->api_username = $config['api_username'];
        $this->api_password = $config['api_password'];

        if ( isset($config['response_format']) ){
            $this->response_format = $config['response_format'];
        }

        if ( isset($config['response_type']) ){
            $this->response_type = $config['response_type'];
        }

        if ( isset($config['check_types']) ){
            $this->setTypes($config['check_types']);
        }
    }

    public function setTypes($types)
    {
        if(empty($types)) {
            $this->types = [self::ALL_TYPES];
        } else {
            $this->types = $types;
        }
    }

//  Determines which type of scrub to use based on the phones count.
//  You can't use the small list scrub if you have more then 3000 phone numbers.
    public function mass_scrub($numbers, $fields = []) {
        if(count($numbers) < 3000)
            return $this->request_small_list_numbers($numbers, $fields);
        return $this->request_big_list_numbers($numbers, $fields);
    }

    public function request_single_number($number, $fields = []) {
        $fields['phone_number'] = $number;
        $fields['type'] = $fields['type'] ?? $this->types[0];
        $fields['return'] = $fields['return'] ?? ($this->response_type == 'boolean' ? 'boolean' : 'regular');

        $ch = $this->get_curl('/phone', $fields);

        $output = curl_exec($ch);
        curl_close($ch);

        if ( $this->response_format == 'json' ){
            return $output;
        } else return json_decode($output,true);
    }

    private function get_default_types_if_empty($types = []) {
        if(!array($types) || empty($types)) {
            return $this->types;
        }
        return $types;
    }

//  Sends phone numbers and returns their scrub statuses immediately
    public function request_small_list_numbers($numbers, $fields = []) {
        if(isset($fields['check_types'])) {
            $types = $fields['check_types'];
        } else {
            $types = [];
        }

        $types = $this->get_default_types_if_empty($types);

        $ch = $this->get_curl('/phones', array_merge([
            'type' => json_encode($types),
            'phones' => json_encode($numbers),
            'small_list' => 'true'
        ], $fields));

        $output = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($output,true);

        $this->is_valid_response($response);

        if ( $this->response_format == 'json' ){
            return $output;
        } else return $response;
    }

//  Sends phone numbers and returns their scrub statuses in time
//  1. Sends phone numbers
//  2. Get the job key
//  3. Scans the job status with the key we got each 6 seconds
//  4. When the result of the job is finished, it returns the scrub statuses
    public function request_big_list_numbers($numbers, $fields = []) {
        if(isset($fields['check_types'])) {
            $types = $fields['check_types'];
        } else {
            $types = [];
        }

        $types = $this->get_default_types_if_empty($types);

        $job_key = $this->request_job_key(array_merge([
            'type' => json_encode($types),
            'phones' => json_encode($numbers)
        ], $fields));

        $ch = $this->get_curl('/phones/get/', ['key' => $job_key]);
        $interval = 6;
        $requests_limit = 100;
        $requests = -1;

        while(true) {
            $requests++;

            if($requests == $requests_limit)
                throw new Exception('Too many requests were sent');

            sleep($interval);

            $output = curl_exec($ch);
            $response_array = json_decode($output,true);

            $this->is_valid_response($response_array);

            if(isset($response_array['status'])) {
                //Result not ready yet
                continue;
            }

            break;
        }

        curl_close($ch);

        return json_decode($output,true);
    }

    protected function is_valid_response($response = null) {
        if(
            $response === null ||
            !is_array($response)
        ) {
            throw new Exception('TCPA returned incorrect response');
        } else if(
            isset($response['status']) &&
            $response['status'] == 'error'
        ) {
            throw new Exception($response['status']);
        } else if(
            isset($response['match']) ||
            isset($response['clean']) ||
            isset($response['status'])
        ) {
            return true;
        } else if(isset($response['error'])) {
            throw new Exception($response['error']);
        } else {
            throw new Exception("TCPA verification can`t be used at the moment");
        }
    }

    protected function request_job_key($fields) {
        $ch = $this->get_curl('/phones', $fields);

        $output = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($output,true);

        $this->is_valid_response($response);

        return $response['job_key'];
    }

    protected function get_curl($endpoint, $fields = []) {
        $api_endpoint = 'https://api.tcpalitigatorlist.com/scrub' . $endpoint;
        $api_username = $this->api_username;
        $api_password = $this->api_password;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,$api_endpoint);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_USERPWD, "$api_username:$api_password");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        return $ch;
    }

}
