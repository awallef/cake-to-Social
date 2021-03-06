#Ultime CakePHP Social Plugin
##Overview
this plugin for `cakePHP 2.x` offers models to connect your favourite social data.

I ll add more models. Stay tuned 0_o

##Social site covered
* Facebook
* Flickr
* Instrgam
* Soundcloud
* Tumblr
* Twitter
* Vimeo
* Youtube
 
##Coming soon

* Tripadvisor

##Download
Download the project and `rename it Social`

	app/Plugin/Social


##Git submodule
	git submodule add https://github.com/awallef/cake-to-Social.git app/Plugin/Social
	
	git submodule init
	git submodule update

##Config
You need to add a few lines in database.php. ( Add only the service setting you need! )
	
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
    
    public $flickr = array(
        'datasource' => 'Social.FlickrSource',
        'database' => 'flickrdb',
        'api_key' => 'api-key',
        'cache_enabled' => true,
        'cache_config_name' => 'flickr',
        'cache_duration' => '+1 week',
        'cache_folder' => 'social'
    );
    
    public $instagram = array(
        'datasource' => 'Social.InstagramSource',
        'database' => 'instagramdb',
        'token' => 'token',
        'cache_enabled' => true,
        'cache_config_name' => 'instgram',
        'cache_duration' => '+1 week',
        'cache_folder' => 'social'
    );
    
    public $soundcloud = array(
        'datasource' => 'Social.SoundcloudSource',
        'database' => 'soundclouddb',
        'app_id' => 'api-id',
        'app_secret' => 'api-secret',
        'cache_enabled' => true,
        'cache_config_name' => 'soundcloud',
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
    
    public $twitter = array(
        'datasource' => 'Social.TwitterSource',
        'database' => 'twitterdb',
        'oauth_access_token' => 'oauth_access_token',
        'oauth_access_token_secret' => 'oauth_access_token_secret',
        'consumer_key' => 'consumer_key',
        'consumer_secret' => 'consumer_secret',
        'cache_enabled' => true,
        'cache_config_name' => 'twitter',
        'cache_duration' => '+1 day',
        'cache_folder' => 'social'
    );
    
    public $vimeo = array(
        'datasource' => 'Social.VimeoSource',
        'database' => 'vimeodb',
        'cache_enabled' => true,
        'cache_config_name' => 'vimeo',
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


##Load
add this in your bootstrap.php

	CakePlugin::load('Social', array('bootstrap' => false, 'routes' => false));

##Usage
Use the plugin's models in your WhateverController.php

	<?php

	App::uses('AppController', 'Controller');

	class WhateverController extends AppController {

    	public $uses = array(
        	'Social.Facebook',
        	'Social.Flickr',
        	'Social.Instagram',
        	'Social.Soundcloud',
        	'Social.Tumblr',
        	'Social.Twitter',
        	'Social.Vimeo',
        	'Social.Youtube'
    	);
    
    	public function facebook() {

        	$data = $this->Facebook->query('feed');
        	$this->set('data', $data); 
        	$this->render('/Common/data');
    	}
    
    	public function flickr() {

        	$photosetlist = $this->Flickr->query(
        		'flickr.photosets.getList',
        		array('user_id' => '54944466@N03'
        	));
        	$photos = array();
        	foreach( $photosetlist['photosets']['photoset'] as $list ){
            	$p = $this->Flickr->query(
            		'flickr.photosets.getPhotos',
            		array('photoset_id' => $list['id']
            	));
            
            	if( !empty($p['photoset']['photo']) ){
                	$photos = array_merge($photos, $p['photoset']['photo'] );
            	}
        	}
        	$this->set('photos', $photos);
    	}
    	
    	public function instagram() {
        
        	// pass args 
        	$data = $this->Instagram->query('users/search',array(
        		'q' => 'toto'
        	));
        	debug( $data );
        
        	// when method needs a var, do as below
        	$data = $this->Instagram->query('users/{user-id}/media/recent',array(
            	'{user-id}' => '289149816'
        	));
        	$this->set('data', $data);
        	$this->render('/Common/data');
    	}
    
    	public function soundcloud() {
        	
        	$data = $this->Soundcloud->query('users/{user-id}/favorites',array(
            	'{user-id}' => 'user9964656',
            	'limit' => 10
        	));
        	$this->set('data', $data);
        	$this->render('/Common/data');
    	}
    
    	public function tumblr() {
        
        	$data = $this->Tumblr->paginate();
        	$this->set('data', $data);
        	$this->render('/Common/data');
    	}
    	
    	public function twitter() {
        
        	// pass args 
        	$data = $this->Twitter->query('followers/ids',array(
            	'screen_name' => 'awallef'
        	));
        	debug( $data );
        
        	// when method needs a var, do as below
        	$data = $this->Twitter->query('statuses/show/{id}',array(
            	'{id}' => '85377105348661250'
        	));
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
                	'Education', // 'Education|Howto' => OR 
                	'Howto' // 'Education','Howto' => AND
            	),
            	'keywords' => array(
                	'all' // same logic as categories
            	),
            	'q' => 'hello'
        	));
        	$this->set('data', $data);
        	$this->render('/Common/data');
    	}

	}

##Helpers
Find some helpers, more to come soon

####flickr
add Social.Flickr in your controller then in your view file:

	<?php 
	foreach( $photos as $image ){
    	echo $this->Html->image(
            $this->Flickr->thumbURL( $image),
            // or $this->Flickr->photoURL( $image)
            
            array('alt' => $image['title'])
    	);
	}
	?>


##Licence
This plugin is under MIT Licence