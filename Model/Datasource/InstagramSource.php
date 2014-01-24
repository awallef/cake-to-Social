<?php

App::uses('SocialDataSource', 'Social.Model/Datasource');

class InstagramSource extends SocialDataSource {
    
    public $cachePrefix = 'instgrm_';
    
    public $uri = 'https://api.instagram.com/v1/';
    
    public function _getParams( $params = array() ){
        return array_merge($params, array('access_token' => $this->config['token']) );
    }
}