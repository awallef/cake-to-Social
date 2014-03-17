<?php

App::uses('SocialDataSource', 'Social.Model/Datasource');

class FacebookSource extends SocialDataSource {
    
    public $checkResponseStatus = false;
    
    public $cachePrefix = 'fb_';
    
    public $uri = 'https://graph.facebook.com/';
    
    public $authToken = false;
    
    public function _createConnection(){
        $this->Http = new HttpSocket();
        
        $authQuery = array(
            'type' => 'client_cred',
            'client_id' => $this->config['app_id'],
            'client_secret' => $this->config['app_secret']
        );
        
        $this->authToken = $this->_call('https://graph.facebook.com/oauth/access_token', $authQuery);
        return ( !$this->authToken )? $this->authToken : true;  
    }
    
    public function _getUri( $method, $params ){
        return $this->uri .$this->config['fb_id'].'/'.$method;
    }
    
    public function _getParams( $params = array() ){
        $authToken = explode('=', $this->authToken);
        return array_merge( array( $authToken[0] => $authToken[1]), $params );
    }
    
    public function _parseResponse($response){
        return ( !$this->isConnected )? $response : json_decode($response);
    }
}