<?php

App::uses('HttpSocket', 'Network/Http');

class FacebookSource extends DataSource {
    
    public function __construct($config) {
        parent::__construct($config);
        $this->Http = new HttpSocket();
        
        if ($this->config['cache_enabled']) {
            Cache::config($this->config['cache_config_name'], array(
                'engine' => 'File',
                'duration' => $this->config['cache_duration'],
                'path' => CACHE . $this->config['cache_folder'] . DS,
                'prefix' => 'fb_'
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
                $response = $this->_request($method, $params);
                Cache::write($hash, $response, $this->config['cache_config_name'] );
             }
             
         }else{
            $response = $this->_request($method, $params);
         }
        
        return $response;
    }

    protected function _request($method, $params) {
        
        if(!$method)
            $method = '';
        
        $authQuery = array(
            'type' => 'client_cred',
            'client_id' => $this->config['app_id'],
            'client_secret' => $this->config['app_secret']
        );
        
        $authToken = $this->_call('https://graph.facebook.com/oauth/access_token', $authQuery);
        if( !$authToken )
            return $authToken;
        
        $authToken = explode('=', $authToken);
        
        $response = $this->_call('https://graph.facebook.com/'.$this->config['fb_id'].'/'.$method, array_merge( array( $authToken[0] => $authToken[1]), $params ) );
        if( !$response )
            return $response;
        else 
            return json_decode($response);
    }

    protected function _call($url, $params = array()) {
        try {
            $response = $this->Http->get($url, $params);
        } catch (Exception $e) {
            return $this->_error(-32300, $e->getMessage());
        }
        if (!$this->Http->response['status']['code']) {
            return $this->_error(-32300, __('Transport error - could not open socket', true));
        }
        //debug($response);
        if ($this->Http->response['status']['code'] != 200) {
            return $this->_error(-32300, __('Transport error - HTTP status code was not 200', true));
        }
        
        return $response->body;
    }

    protected function _error($number, $text) {
        $this->errno = $number;
        $this->error = $text;
        return false;
    }

}