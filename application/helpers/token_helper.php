<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Token_helper
{
    function __construct($id)
    {
        $CI = & get_instance();
    }

    public static function get_token_csrf($token_csrf)
    {
        $time = time();
        $CI = & get_instance();
        if($token_csrf){

        } else {
            return null;
        }
    }

}