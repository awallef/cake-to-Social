<?php

App::uses('SocialAppModel', 'Social.Model');

/**
 * Vimeo Model
 *
 */
class Photoflickr extends SocialAppModel {
    
    public $useDbConfig = 'flickr';
    public $flickr = null;

    function __construct($id = false, $table = null, $ds = null) {
        parent::__construct($id, $table, $ds);
        $source = $this->getDataSource();
        $this->flickr = $source->flickr;
    }
    
    public function read($fields = null, $id = null) {
        
        // id check
        $this->id = (!empty($id) ) ? $id : $this->id;
        
        if (empty($this->id))
            return array();

        return $this->_getSingleSet($this->flickr->photos_getInfo($this->id));
    }
    
    public function find($type = 'first', $query = array()) {

        $response = array();

        if (empty($query['conditions']))
            return $response;

        // by Video.id
        if (array_key_exists('Photo.id', $query['conditions'])) {
            if( !is_array( $query['conditions']['Photo.id'] ) ){
                $response = $this->read(null, $query['conditions']['Photo.id']);
            }else{
                $response = array();
                foreach( $query['conditions']['Photo.id'] as $id  ){
                    $r = $this->read(null, $id);
                    if( !empty($r) ){
                        $response[] = $r;
                    }
                }
            }
        }

        // by User.id or name
        if (array_key_exists('Photoset.id', $query['conditions'])) {
            $response = $this->_photosetQuery($type, $query);
        }

        return $response;
    }
    
    /***
    *    ________                      .__               
    *    \_____  \  __ __   ___________|__| ____   ______
    *     /  / \  \|  |  \_/ __ \_  __ \  |/ __ \ /  ___/
    *    /   \_/.  \  |  /\  ___/|  | \/  \  ___/ \___ \ 
    *    \_____\ \_/____/  \___  >__|  |__|\___  >____  >
    *           \__>           \/              \/     \/ 
    */
    
    private function _photosetQuery($type = 'first', $query) {
        $response = $this->flickr->photosets_getPhotos($query['conditions']['Photoset.id']);
        switch( $type ){
            
            case 'first':
                if( !empty( $response['photoset'] ) ){
                    $response['Photo'] = $response['photoset']['photo'][0];
                    unset( $response['photoset'] );
                }
                break;
            
            case 'list':
                $resp = array();
                if( !empty( $response['photoset'] ) ){
                    foreach( $response['photoset']['photo'] as $set ){
                        $resp['id'] = $set['title'];
                    }
                }
                $response = $resp;
                break;
            
            case 'all':
                $resp = array();
                if( !empty( $response['photoset'] ) ){
                    foreach( $response['photoset']['photo'] as $set ){
                        $resp[]['Photo'] = $set;
                    }
                }
                $response = $resp;
                break;
        }
        
        return $response;
    }
    
    /***
    *      ___ ___         .__                              
    *     /   |   \   ____ |  | ______   ___________  ______
    *    /    ~    \_/ __ \|  | \____ \_/ __ \_  __ \/  ___/
    *    \    Y    /\  ___/|  |_|  |_> >  ___/|  | \/\___ \ 
    *     \___|_  /  \___  >____/   __/ \___  >__|  /____  >
    *           \/       \/     |__|        \/           \/ 
    */
    
    private function _getSingleSet($response) {
        $resp = array();
        if( !empty( $response ) ){
            $resp['Photo'] = $response;
        }
        return $resp;
    }

}