<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class System_helper
{
    public static function get_time($str)
    {
        $time=strtotime($str);
        if($time===false)
        {
            return 0;
        }
        else
        {
            return $time;
        }
    }
    public static function history_save($table_name,$item_id,$value_old=array(),$value_new=array(),$remarks=array())
    {
        $CI =& get_instance();
        $time=time();
        $user = User_helper::get_user();
        $data = Array(
            'controller'=>$CI->router->class,
            'method'=>$CI->router->method,
            'remarks'=>json_encode($remarks),
            'item_id'=>$item_id,
            'value_old'=>json_encode($value_old),
            'value_new'=>json_encode($value_new),
            'date_created'=>$time,
            'date_created_string'=>date('d-M-Y h:i:s A',$time),
            'user_created'=>$user?$user->id:-1
        );
        Query_helper::add($table_name,$data,false);
    }
}
