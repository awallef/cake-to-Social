#Ultime CakePHP Social Plugin
##Overview
this plugin for `cakePHP 2.x` offers models to connect your favourite social data.

I ll add more models. Stay tuned 0_o

##Social site covered
* Vimeo
* Flickr
* Youtube
* Tumblr
* Facebook
 
##Coming soon

* Instrgam
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
	public $vimeo = array(
        'datasource' => 'Social.VimeoSource',
        'database' => 'vimeodb',
        'cache_enabled' => true,
        'cache_config_name' => 'vimeo',
        'cache_duration' => '+1 week',
        'cache_folder' => 'social'
    );
    public $flickr = array(
        'datasource' => 'Social.FlickrSource',
        'database' => 'flickrdb',
        'api_key' => 'api-secret',
        'cache_enabled' => true,
        'cache_config_name' => 'flickr',
        'cache_duration' => '+1 week',
        'cache_folder' => 'social'
    );
    public $facebook = array(
        'datasource' => 'Social.FacebookSource',
        'database' => 'facebookdb',
        'app_id' => 'app-id',
        'app_secret' => 'app-secret',
        'fb_id' => 'page-id or fb-id',
        'cache_enabled' => true,
        'cache_config_name' => 'facebook',
        'cache_duration' => '+1 day',
        'cache_folder' => 'social'
    );
    public $tumblr = array(
        'datasource' => 'Social.TumblrSource',
        'database' => 'tumblrdb',
        'screen_name' => 'screenname',
        'cache_enabled' => true,
        'cache_config_name' => 'tumblr',
        'cache_duration' => '+1 week',
        'cache_folder' => 'social'
    );
    
    public $youtube = array(
        'datasource' => 'Social.YoutubeSource',
        'database' => 'youtubedb',
        'cache_enabled' => true,
        'cache_config_name' => 'youtube',
        'cache_duration' => '+1 week',
        'cache_folder' => 'social'
    );

if you enable caching, them create a folder in your tmp/cache dir.

##Load
add this in your bootstrap.php

	CakePlugin::load('Social', array('bootstrap' => false, 'routes' => false));

##Usage
Use the plugin's models in your WhateverController.php

	<?php

	App::uses('AppController', 'Controller');

	class WhateverController extends AppController {

    	public $uses = array('Social.Facebook','Social.Flickr','Social.Tumblr','Social.Vimeo','Social.Youtube');
    
    	public function facebook() {

        	$data = $this->Facebook->query('feed');
        	$this->set('data', $data); 
        	$this->render('/Common/data');
    	}
    
    	public function flickr() {

        	$data = $this->Flickr->query('flickr.photosets.getList',array('user_id' => '54944466@N03'));
        	$this->set('data', $data);
        	$this->render('/Common/data');
    }
    
    	public function tumblr() {
        
        	$data = $this->Tumblr->paginate();
        	$this->set('data', $data);
        	$this->render('/Common/data');
    	}
    
    	public function vimeo() {

        	$data = $this->Vimeo->query('10042745',array('videos'));
        	$this->set('data', $data);
        	$this->render('/Common/data');
    	}
    
    	public function youtube() {

        	$data = $this->Youtube->query('videos',array(
            	'categories' => array(
                	'Education',
                	'Howto'
            	),
            	'keywords' => array(
                	'all'
            	),
            	'q' => 'hello'
        	));
        	$this->set('data', $data);
        	$this->render('/Common/data');
    	}

	}


##Licence
This plugin is under MIT Licence