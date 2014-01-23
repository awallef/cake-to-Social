<?php
/**
 * doc http://gdata.youtube.com/demo/index.html
 * 
 * 
 * 
 */


App::uses('HttpSocket', 'Network/Http');

class YoutubeSource extends DataSource {

    public function __construct($config) {
        parent::__construct($config);
        $this->Http = new HttpSocket();

        if ($this->config['cache_enabled']) {
            Cache::config($this->config['cache_config_name'], array(
                'engine' => 'File',
                'duration' => $this->config['cache_duration'],
                'path' => CACHE . $this->config['cache_folder'] . DS,
                'prefix' => 'yt_'
            ));
        }
    }

    public function query($method, $params = array(), &$model = null) {

        if ($this->config['cache_enabled']) {

            $hash = md5(serialize(array(
                        'method' => $method,
                        'params' => $params
                    )));

            $response = Cache::read($hash, $this->config['cache_config_name']);
            if (!$response) {
                $response = $this->_request($method, $params);
                Cache::write($hash, $response, $this->config['cache_config_name']);
            }
        } else {
            $response = $this->_request($method, $params);
        }

        return $response;
    }

    protected function _request($method, $params = array()) {
        
        $url = 'http://gdata.youtube.com/feeds/api/'.$method.'/';
        
        // recompose for category
        $categories = '';
        if( !empty( $params['categories'] ) ){
            foreach( $params['categories'] as $cat ){
                $categories .= '/'.urlencode('{http://gdata.youtube.com/schemas/2007/categories.cat}'). $cat;
            }
            unset( $params['categories'] );
        }
        
        // recompose for keywords
        $keywords = '';
        if( !empty( $params['keywords'] ) ){
            foreach( $params['keywords'] as $cat ){
                $keywords .= '/'.urlencode('{http://gdata.youtube.com/schemas/2007/keywords.cat}'). $cat;
            }
            unset( $params['keywords'] );
        }
        
        if( !empty($categories) ||  !empty($keywords) ){
            $url .= '-'.$categories. $keywords;
        }        
        
        // force to json
        $params['alt'] = 'json';
        
        $response = $this->_call($url, $params);
        if (!$response)
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