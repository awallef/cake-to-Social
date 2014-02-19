<?php

App::uses('HttpSocket', 'Network/Http');

class TwitterSource extends DataSource {
    
    public $twitter = null;
    
    public function __construct($config) {
        parent::__construct($config);
        $this->Http = new HttpSocket();
        
        //App::import('Social.Vendor', 'twitter-api-php/TwitterAPIExchange');
        require_once APP . 'Plugin/Social/Vendor/twitter-api-php/TwitterAPIExchange.php';
        $this->twitter = new TwitterAPIExchange(array(
            'oauth_access_token' => $this->config['oauth_access_token'],
            'oauth_access_token_secret' => $this->config['oauth_access_token_secret'],
            'consumer_key' => $this->config['consumer_key'],
            'consumer_secret' => $this->config['consumer_secret']
        ));
        
        if ($this->config['cache_enabled']) {
            Cache::config($this->config['cache_config_name'], array(
                'engine' => 'File',
                'duration' => $this->config['cache_duration'],
                'path' => CACHE . $this->config['cache_folder'] . DS,
                'prefix' => 'tw_'
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
        
        if( !empty($method) && !empty($params) ){
            foreach( $params as $key => $param ){
                if( strrpos($key,'}') !== false ){
                    $method = str_replace($key, $param, $method);
                    unset($params[$key]);
                }
            }
        }
        
        $response = $this->_call('https://api.twitter.com/1.1/' . $method.'.json', $params);
        if( !$response )
            return $response;
        else 
            return json_decode ($response);
    }

    protected function _call($url, $params = array()) {
        
        $getfield = ( empty($params) )? null : '?'.http_build_query($params);
        
        try {
            if( $getfield ){
                $response = $this->twitter->setGetfield($getfield)
                ->buildOauth($url, 'GET')
                ->performRequest();
            }else{
                $response = $this->twitter->buildOauth($url, 'GET')
                ->performRequest();
            }
        } catch (Exception $e) {
            return $this->_error(-32300, $e->getMessage());
        }
        return $response;
    }

    protected function _error($number, $text) {
        $this->errno = $number;
        $this->error = $text;
        return false;
    }

}