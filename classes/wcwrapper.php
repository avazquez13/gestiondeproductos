<?php

/*
 * Copyright © 2016 Wise Solutions S.A.
 * All rights reserved.
 *
 * This software is the confidential property and proprietary information of
 * Wise Solutions S.A.
 */
 
class wcwrapper {
    const VERSION = '2.0.1';
    public $http;

    public function __construct($url, $consumerKey, $consumerSecret, $options = []) {
        $this->http = new HttpClient($url, $consumerKey, $consumerSecret, $options);
    }

    public function post($endpoint, $data) {
        return $this->http->request($endpoint, 'POST', $data);
    }

    public function put($endpoint, $data) {
        return $this->http->request($endpoint, 'PUT', $data);
    }

    public function get($endpoint, $parameters = []) {
        return $this->http->request($endpoint, 'GET', [], $parameters);
    }

    public function delete($endpoint, $parameters = []) {
        return $this->http->request($endpoint, 'DELETE', [], $parameters);
    }

    public function options($endpoint) {
        return $this->http->request($endpoint, 'OPTIONS', [], []);
    }
}
?> 