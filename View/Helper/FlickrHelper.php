<?php

class FlickrHelper extends AppHelper {

    public function photoURL( $pic ){
        return 'http://farm' . $pic['farm'] . '.static.flickr.com/' . $pic['server'] . DS . $pic['id'] . '_' . $pic['secret'] . '_b.jpg';
    }
    
    public function thumbURL( $pic ){
        return 'http://farm' . $pic['farm'] . '.static.flickr.com/' . $pic['server'] . DS . $pic['id'] . '_' . $pic['secret'] . '_q.jpg';
    }

}
