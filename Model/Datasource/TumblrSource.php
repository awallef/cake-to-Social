<?php

/**
 * SimpleTwitter - A simple twitter datasource for cakephp
 * @author Skyler Lewis (aka alairock) 2012
 * @link http://sixteenink.com
 * @link http://github.com/alairock
 * */
App::uses('HttpSocket', 'Network/Http');

class TumblrSource extends DataSource {
    
    protected $count = 0;
    
    protected $_schema = array(
        'id' => array('type' => 'integer', 'key' => 'primary'),
        'url' => array('type' => 'string'),
        'type' => array('type' => 'string'),
        'unix-timestamp' => array('type' => 'string'),
        'format' => array('type' => 'string'),
        'capiton' => array('type' => 'string')
    );
    
    protected $_queryFields = array(
        'type',
        'search',
        'id',
        'tagged',
        'filter'
    );
    
    protected $_post_types = array(
        
        'photo' => array(
            'photo-caption',
            'width',
            'height',
            'photo-url-1280',
            'photo-url-500',
            'photo-url-400',
            'photo-url-250',
            'photo-url-100',
            'photo-url-75'
        ),
        
        'video' => array(
            'video-caption',
            'video-source',
            'video-player',
            'video-player-500',
            'video-player-250'
        ),
        
        'quote' => array(
            'quote-text',
            'quote-source'
        ),
        
        'link' => array(
            'link-text',
            'link-url',
            'link-description'
        ),
        
        'conversation' => array(
            'conversation-title',
            'conversation-text',
            'conversation'
        ),
        
        'audio' => array(
            'id3-artist',
            'id3-album',
            'id3-year',
            'id3-track',
            'id3-title',
            'audio-caption',
            'audio-player',
            'audio-plays'
        ),
        
        'regular' => array(
            'regular-title',
            'regular-body'
        )
    );
    
    public function __construct($config) {
        parent::__construct($config);
        $this->sourceUrl = 'http://'.$this->config['screen_name'] .'.tumblr.com/api/read/json'; // 'http://demo.tumblr.com/api/read/json';
        $this->Http = new HttpSocket();
        if ($this->config['cache_enabled']) {
            Cache::config($this->config['cache_config_name'], array(
                'engine' => 'File',
                'duration' => $this->config['cache_duration'],
                'path' => CACHE . $this->config['cache_folder'] . DS,
                'prefix' => 'tumblr_'
            ));
        }
    }

    public function listSources() {
        return null;
    }

    public function describe($Model) {
        return $this->_schema;
    }
    
    public function calculate(Model $model, $func, $params = array()) {
        return 'COUNT';
    }
    
    public function query($method, $params = array(), &$model = null ) {
        
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
    
    protected function _request($method, $params = array(), &$model = null ) {
        
        //debug( $queryData );
        
        $query = array();
        
        if( !empty( $params['fields'] ) ){
            if ($params['fields'] === 'COUNT') {
                return array(array(array('count' => $this->count)));
            }
        }
        
        if( !empty( $params['offset'] ) ){
            $query['start'] = $params['offset'];
        }
        
        if( !empty( $params['limit'] ) ){
            $query['num'] = $params['limit'];
        }
        
        $this->_createQuery($query, $params);
        
        //debug($query);
        
        
        $results = $this->Http->get($this->sourceUrl, $query);
        //debug( $results );
        $results = substr($results->body, 22);
        $results = substr($results, 0, -2);
        $results = json_decode($results, true);
        //debug( $results );
        $this->count = $results['posts-total'];
        
        $infos = array();
        $infos['title'] = $results['tumblelog']['title'];
        $infos['name'] = $results['tumblelog']['name'];
        $infos['description'] = $results['tumblelog']['description'];
        $infos['posts-start'] = $results['posts-start'] - 1;
        
        $posts = array();
        
        foreach( $results['posts'] as $key => $post ){
            
            $infos['posts-start'] = $infos['posts-start'] + 1; 
            
            $posts[$key] = array();
            $posts[$key]['Tumblr'] = array(
                'id' => $post['id'],
                'num' => $infos['posts-start'],
                'url' => $post['url'],
                'type' => $post['type'],
                'format' => $post['format'],
                'unix-timestamp' => $post['unix-timestamp'],
                'slug' => $post['slug'],
                
                'title' => $infos['title'],
                'name' => $infos['name'],
                'description' => $infos['description'],
                
            );
            
            $posts[$key]['Tumblr']['tags'] = ( !empty( $post[$key]['tags'] ) )? $post[$key]['tags'] : array();
            
            $this->_createAssocModel( $posts, $post, $key );
            
           
        }
        unset( $results );
        return $posts;
    }
    
    private function _createQuery( &$query, $queryData ){
        if( !empty( $queryData['conditions'] ) ){
            foreach( $this->_queryFields as $field ){
                if(!empty( $queryData['conditions'][$field] )) $query[ $field ] = $queryData['conditions'][$field];
            }
        }
    }
    
    private function _createAssocModel( &$posts, $post, $key  ){
        
        $type = $posts[$key]['Tumblr']['type'];
        $modelName = Inflector::camelize($type);
        
        
        $posts[$key][$modelName] = array();
        foreach( $this->_post_types[ $type ] as $field ){
            if(!empty( $post[$field] )) $posts[$key][$modelName][ $field ] = $post[$field];
        }
        
    }
    
}