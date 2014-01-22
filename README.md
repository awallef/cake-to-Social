#Ultime CakePHP Social Plugin
##Overview
this plugin for `cakePHP 2.x` offers models to connect your favourite social data.

I ll add more models. Stay tuned 0_o

##Social site covered
* Vimeo
* Flickr
 
##Coming soon

* Youtube
* Tumblr
* Facebook
* Soundcloud

##Download
Download the project and `rename it Social`

	app/Plugin/Social


##Git submodule
	git submodule add https://github.com/awallef/cake-to-Social.git app/Plugin/Social
	
	git submodule init
	git submodule update

##Config
You need to add a few lines in database.php
####Vimeo
	public $vimeo = array(
            'datasource' => 'Social.VimeoSource',
            'database' => 'vimeodb',
            'cache_enabled' => true,
            'cache_config_name' => 'vimeo',
            'cache_duration' => '+1 week',
            'cache_folder' => 'social'
        );

####Flickr
	public $flickr = array(
            'datasource' => 'Social.FlickrSource',
            'database' => 'flickrdb',
            'api_key' => 'your-api-key',
            'cache_enabled' => true,
            'cache_config_name' => 'flickr',
            'cache_duration' => '+1 week',
            'cache_folder' => 'social'
        );

##Load
add this in your bootstrap.php

	CakePlugin::load('Social', array('bootstrap' => false, 'routes' => false));

##Usage
Use the plugin's models in your controllers. In your WhateverController.php

####Vimeo
The find method accepts first, list and all. You need to pass a User.id or a Video.id
	
	public $uses = array('Social.Videovimeo');
	
	public function view( $id ){
		
		// the id refers to a video
		$video = $this->Videovimeo->read(null, $id );
        
        $this->set('video', $video );
	}
	
	
	public function index(){
        
        $videos = $this->Videovimeo->find('all', array(
            'conditions' => array(
                'User.id' => '10000000'
            )
        ));
        
        $this->set('videos', $videos );
    }

####Flickr
There is two models for flickr. Photoset and Photo. Both accept find and read methods
	
	public $uses = array('Social.Photosetflickr','Social.Photoflickr');
	
	public function index(){
        
        $photosetlist = $this->Photosetflickr->find('all', array(
            'conditions' => array(
                'User.id' => '54944466@N03'
            )
        ));
        
        $photos = array();
        foreach( $photosetlist as $list ){
            $p = $this->Photoflickr->find('all', array(
                'conditions' => array(
                    'Photoset.id' => $list['Photoset']['id']
                )
            ));
            
            if( !empty($p) ){
                $photos = array_merge($photos, $p );
            }
        }
        
        return $photos;
    }
##Licence
This plugin is under MIT Licence