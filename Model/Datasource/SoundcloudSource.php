<?php

App::uses('HttpSocket', 'Network/Http');

class SoundcloudSource extends DataSource {
    
    public $soundcloud = null;
    
    public function __construct($config) {
        parent::__construct($config);
        $this->Http = new HttpSocket();
        
        //App::import('Social.Vendor', 'soundcloud/Services/Soundcloud');
        require_once APP . 'Plugin/Social/Vendor/soundcloud/Services/Soundcloud.php';
        $this->soundcloud = new Services_Soundcloud($this->config['app_id'],$this->config['app_secret']);
        
        if ($this->config['cache_enabled']) {
            Cache::config($this->config['cache_config_name'], array(
                'engine' => 'File',
                'duration' => $this->config['cache_duration'],
                'path' => CACHE . $this->config['cache_folder'] . DS,
                'prefix' => 'sc_'
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
        
        $response = $this->_call('https://api.soundcloud.com/' . $method, $params);
        if( !$response )
            return $response;
        else 
            return json_decode($response);
    }

    protected function _call($url, $params = array()) {
        
        try {
            $response = $this->soundcloud->get( $url, $params );
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