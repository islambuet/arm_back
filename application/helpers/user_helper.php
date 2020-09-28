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
        $result=Query_helper::get_info(TABLE_LOGIN_SETUP_USER,'*',array('id ='.$id),1);
        if($result && (md5($result['user_name'])==$result['password'])) {
            $this->username_password_same=true;
        } else {
            foreach ($result as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    public static function get_user()
    {
        $time = time();
        $response['error_type']='';
        $CI = & get_instance();
        if (User_helper::$logged_user) {
            return User_helper::$logged_user;
        }
        else
        {
            $token_auth = $CI->input->post('token_auth');
            if($token_auth){
                $result = Query_helper::get_info(TABLE_LOGIN_USER_SESSIONS,array('*'),array('token_auth ="' .$token_auth.'"'),1);
                if($result){
                    if(($time-$result['time_start'])>Configuration_helper::get_session_expire() ){
                        $response['error_type'] = 'SESSION_EXPIRE';
                        return $response;
                    }
                    $user = $CI->db->get_where(TABLE_LOGIN_SETUP_USER, array('id' => $result['user_id'],'status'=>SYSTEM_STATUS_ACTIVE))->row();
                    if($user){
                        $CI->load->helper('encrypt_decrypt');
                        $token_csrf_generated = Encrypt_decrypt_helper::get_encrypt('CSRF_'.$time.'_'.$result['user_id']);
                        $data_session = array(
                            'time_start'=> $time,
                            'csrf_new'=> $token_csrf_generated,
                            'csrf_old'=> $result['csrf_new'],
                        );
                        Query_helper::update(TABLE_LOGIN_USER_SESSIONS,$data_session,array("id = ".$result['id']),false);

                        User_helper::$logged_user = new User_helper($result['user_id']);
                        return User_helper::$logged_user;
                    } else {
                        $response['error_type'] = 'USER_NOT_FOUND';
                        return $response;
                    }
                } else {
                    $response['error_type'] = 'SESSION_NOT_FOUND';
                    return $response;
                }
            } else {
                $response['error_type'] = 'TOKEN_AUTH_NOT_FOUND';
                return $response;
            }
        }
    }
    // get new csrf

}