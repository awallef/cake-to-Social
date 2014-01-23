<?php

/**
 * Vimeo Datasource 0.1  
 *  
 * Vimeo datasource to communicate with the Vimeo Simple API (Advanced on the way...)  
 * Also utilizes the Vimeo oEmbed API for generating embed code. 
 *  
 * Licensed under The MIT License  
 * Redistributions of files must retain the above copyright notice.  
 *  
 */
App::uses('HttpSocket', 'Network/Http');

class VimeoSource extends DataSource {

    protected $count = 0;
    var $description = 'Vimeo Simple API';
    var $Http = null;
    var $allowedRequests = array(
        'user' => array(
            'info',
            'videos',
            'likes',
            'appears_in',
            'all_clips',
            'subscriptions',
            'albums',
            'channels',
            'groups',
            'contacts_clips',
            'contacts_like'
        ),
        'activity' => array(
            'user_did',
            'happened_to_user',
            'contacts_did',
            'happened_to_contacts',
            'everyone_did'
        ),
        'group' => array(
            'videos',
            'users',
            'info'
        ),
        'channel' => array(
            'videos',
            'info'
        ),
        'album' => array(
            'videos',
            'info'
        )
    );

    /**
     * Constructor sets configuration and instantiates HttpSocket 
     *  
     * @param array config Optional.  
     * @see http://www.vimeo.com/api/docs/simple-api 
     */
    function __construct($config = null) {
        parent::__construct($config);
        $this->Http = & new HttpSocket();

        if ($this->config['cache_enabled']) {
            Cache::config($this->config['cache_config_name'], array(
                'engine' => 'File',
                'duration' => $this->config['cache_duration'],
                'path' => CACHE . $this->config['cache_folder'] . DS,
                'prefix' => 'vimeo_'
            ));
        }
    }
    
    public function query($method, $params = array(), &$model = null) {
        $url = $method;
        foreach( $params as $param )
            $url.= "/" . $param;
        return $this->__vimeoApiRequest($url);
    }

    /**
     * Internal function to make the requests to the Vimeo Simple API 
     *  
     * @param string data Required. 
     * @see http://www.vimeo.com/api/docs/simple-api 
     */
    function __vimeoApiRequest($data = null) {
        if (!empty($data)) {
            
            $url = "http://vimeo.com/api/v2/{$data}.php";
            if ($this->config['cache_enabled']) {
                
                $md5Url = md5($url);
                $result = Cache::read($md5Url, $this->config['cache_config_name'] );
                if (!$result) {
                    $result = $this->Http->get($url, null);
                    $result = unserialize($result);
                    Cache::write($md5Url, $result, $this->config['cache_config_name'] );
                }
                return $result;
                
            } else {
                $result = $this->Http->get($url, null);
                return unserialize($result);
            }
        }
        return false;
    }

}

?>