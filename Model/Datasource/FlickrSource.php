<?php

class FlickrSource extends DataSource {
    
    public $flickr = null;
    
    public function __construct($config = null) {
        parent::__construct($config);
        
        //App::import('Social.Vendor', 'phpflickr/phpFlickr');
        require_once APP . 'Plugin/Social/Vendor/phpflickr/phpFlickr.php';
        $this->flickr = new phpFlickr( $this->config['api_key'] );
        if ($this->config['cache_enabled']) {
            Cache::config($this->config['cache_config_name'], array(
                'engine' => 'File',
                'duration' => $this->config['cache_duration'],
                'path' => CACHE . $this->config['cache_folder'] . DS,
                'prefix' => 'flickr_'
            ));
            
            $this->flickr->enableCache('custom', array(
                array($this, 'custom_cache_get'),
                array($this, 'custom_cache_set')
            ), $this->config['cache_duration'] );
        }
    }
    
    public function query($method, $params = array(), &$model = null) {
        return unserialize($this->flickr->request($method, $params));
    }
    
    public function custom_cache_get( $reqhash ){
        return Cache::read($reqhash, $this->config['cache_config_name'] );
    }
    
    public function custom_cache_set( $reqhash, $response, $cache_expire ){
        Cache::write($reqhash, $response, $this->config['cache_config_name'] );
    }

}

?>