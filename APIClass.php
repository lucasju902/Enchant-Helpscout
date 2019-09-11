<?php

class APIClass
{
    private $_protocol;
    /**
     * Create client
     *
     * @param string $host
     * @param int $port
     * @param string $protocol
     */
    public function __construct($protocol = 'https')
    {
        $this->_protocol = $protocol;
    }
    /**
     * Perform GET API request
     *
     * @param string $request
     * @return string
     */
    public function get_request($request_url,$token)
    {
        $curl = curl_init("$this->_protocol://$request_url");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->_getHeaders($token));
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
    /**
     * Perform API request
     *
     * @param string $request
     * @return string
     */
    public function post_request($request_url,$request,$token = '')
    {
        $curl = curl_init("$this->_protocol://$request_url");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->_getHeaders($token));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    public function post_request_header_response($request_url,$request,$token = '')
    {
        $curl = curl_init("$this->_protocol://$request_url");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->_getHeaders($token));
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
        $result = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headers = substr($result, 0, $header_size);
        curl_close($curl);
        return $headers;
    }
    /**
     * Retrieve list of headers needed for request
     *
     * @return array
     */
    private function _getHeaders($token)
    {
        $headers = array(
            "Content-Type: application/json",
        );
        if ($token != '') {
            array_push($headers, "Accept: application/json");
            array_push($headers, "Authorization: bearer ".$token);
        }
        return $headers;
    }
}