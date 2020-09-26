<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_helper
{
    public static $logged_user = null;
    /*public static $mobile_verification_code_expires = 2000;//60*5--5minutes
    public static $mobile_verification_cookie_expires = 864000;//60*60*24*10--10days
    public static $mobile_verification_cookie_prefix = 'login_mobile_verification_2018_19_';//60*60*24*10--10days*/
    function __construct($id)
    {
        $CI = & get_instance();
        $this->username_password_same=false;
        //user
        $result=Query_helper::get_info($CI->config->item('table_login_setup_user'),'*',array('id ='.$id),1);
        if($result && (md5($result['user_name'])==$result['password']))
        {
            $this->username_password_same=true;
        }
        //user info
        $result=Query_helper::get_info($CI->config->item('table_login_setup_user_info'),'*',array('user_id ='.$id,'revision =1'),1);
        if ($result)
        {
            foreach ($result as $key => $value)
            {
                $this->$key = $value;
            }
        }
    }

    public static function get_user()
    {
        $CI = & get_instance();
        if (User_helper::$logged_user) {
            return User_helper::$logged_user;
        }
        else
        {
            if($CI->session->userdata("user_id")!="")
            {
                $user = $CI->db->get_where($CI->config->item('table_login_setup_user'), array('id' => $CI->session->userdata('user_id'),'status'=>$CI->config->item('system_status_active')))->row();
                if($user)
                {
                    User_helper::$logged_user = new User_helper($CI->session->userdata('user_id'));
                    return User_helper::$logged_user;
                }
                return null;
            }
            else
            {
                return null;
            }

        }
    }

}