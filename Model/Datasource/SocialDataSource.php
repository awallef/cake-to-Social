<?php

App::uses('HttpSocket', 'Network/Http');

class SocialDataSource extends DataSource {
    
    public $isConnected = false;
    
    public $cachePrefix = 'social_';
    
    public $uri = '';
    
    public function __construct($config) {
        parent::__construct($config);
        $this->_configureCache();
    }
    
    public function _configureCache(){
        
        if ($this->config['cache_enabled']) {
            Cache::config($this->config['cache_config_name'], array(
                'engine' => 'File',
                'duration' => $this->config['cache_duration'],
                'path' => CACHE . $this->config['cache_folder'] . DS,
                'prefix' => $this->cachePrefix
            ));
        }
    }
    
    public function query($method, $params = array(), &$model = null) {
        
         if ($this->config['cache_enabled']) {
             $hash = md5(serialize(array(
                 'method' => $method,
                 'params' => $params
             )));
             $response = Cache::read($hash, $this->config['cache_config_name'] );
             if (!$response) {
                $response = $this->_query( $method, $params );
                Cache::write($hash, $response, $this->config['cache_config_name'] );
             }
             
         }else{
            $response = $this->_query($method, $params);
         }
        
        return $response;
    }
    
    public function _query( $method, $params = array() ){
        if( !$this->isConnected ){
            $this->_createConnection();
            $this->isConnected = true;
        }
        return $this->_request($method, $params);
    }
    
    public function _createConnection(){
        $this->Http = new HttpSocket();
    }

    public function _request($method, $params) {
        
        if( !empty($method) && !empty($params) ){
            foreach( $params as $key => $param ){
                if( strrpos($key,'}') !== false ){
                    $method = str_replace($key, $param, $method);
                    unset($params[$key]);
                }
            }
        }
        if(empty($method)){
            $method = '';
        }
        $params = $this->_getParams( $params );
        $uri = $this->_getUri( $method, $params);
        
        $response = $this->_call($uri, $params);
        return $response;
    }
    
    public function _getUri( $method, $params ){
        return $this->uri . $method;
    }
    
    public function _getParams( $params = array() ){
        return $params;
    }
    
    public function _parseResponse($response){
        return json_decode($response);
    }
    
    public function _call($url, $params = array()) {
        try {
            $response = $this->Http->get($url, $params);
        } catch (Exception $e) {
            return $this->_error(-32300, $e->getMessage());
        }
        if (!$this->Http->response['status']['code']) {
            return $this->_error(-32300, __('Transport error - could not open socket', true));
        }
        if ($this->Http->response['status']['code'] != 200) {
            return $this->_error(-32300, __('Transport error - HTTP status code was not 200', true));
        }
        return $this->_parseResponse($response->body);
    }

    protected function _error($number, $text) {
        $this->errno = $number;
        $this->error = $text;
        return array(
            'errno' => $number,
            'error' => $text
        );
    }

}