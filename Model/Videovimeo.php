<?php

App::uses('SocialAppModel', 'Social.Model');

/**
 * Vimeo Model
 *
 */
class Videovimeo extends SocialAppModel {
    
    public $useDbConfig = 'vimeo';
    public $vimeoSource = null;

    public function __construct($id = false, $table = null, $ds = null) {
        parent::__construct($id, $table, $ds);
        $this->vimeoSource = $this->getDataSource();
    }
    
    public function read($fields = null, $id = null) {

        // id check
        $this->id = (!empty($id) ) ? $id : $this->id;
        
        if (empty($this->id))
            return array();

        return $this->_getSingleVideo($this->vimeoSource->video($this->id));
    }
    
    public function find($type = 'first', $query = array()) {

        $response = array();

        if (empty($query['conditions']))
            return $response;

        // by Video.id
        if (array_key_exists('Video.id', $query['conditions'])) {
            if( !is_array( $query['conditions']['Video.id'] ) ){
                $response = $this->read(null, $query['conditions']['Video.id']);
            }
        }

        // by User.id or name
        if (array_key_exists('User.id', $query['conditions']) || array_key_exists('User.name', $query['conditions'])) {
            $response = $this->_userQuery($type, $query);
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
    
    private function _userQuery($type = 'first', $query) {
        if (array_key_exists('User.id', $query['conditions'])) {
            $user = $query['conditions']['User.id'];
        } else {
            $user = $query['conditions']['User.name'];
        }
        
        $response = $this->vimeoSource->userRequest($user, 'videos');
        
        switch( $type ){
            
            case 'first':
                $response = $this->_getSingleVideo( $response );
                break;
            
            case 'list':
                $response = $this->_getListVideos( $response );
                break;
            
            case 'all':
                $response = $this->_getMultipleVideos( $response );
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
    
    private function _getSingleVideo($response) {
        if (!$response) {
            $this->id = null;
            $response = array();
        } else {
            $response = array(
                'Video' => $response[0]
            );
        }

        return $response;
    }
    
    private function _getListVideos($response) {
        if (!$response) {
            $this->id = null;
            $response = array();
        } else {
            $final = array();
            foreach ($response as $key => $video) {
                $final[ $video['id'] ] = $video['title'];
            }
            $response = $final;
        }

        return $response;
    }

    private function _getMultipleVideos($response) {
        if (!$response) {
            $this->id = null;
            $response = array();
        } else {
            foreach ($response as $key => &$video) {
                $video = array(
                    'Video' => $video
                );
            }
        }

        return $response;
    }

}