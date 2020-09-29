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
        /*$this->username_password_same=false;
        $result=Query_helper::get_info(TABLE_LOGIN_SETUP_USER,'*',array('id ='.$id),1);
        if($result && (md5($result['mobile_no'])==$result['password'])) {
            $this->username_password_same=true;
        } else {
            foreach ($result as $key => $value) {
                $this->$key = $value;
            }
        }*/
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
                $result_session = Query_helper::get_info(TABLE_LOGIN_USER_SESSIONS,array('*'),array('token_auth ="' .$token_auth.'"'),1);
                if($result_session){
                    if(($time-$result_session['time_start'])>Configuration_helper::get_session_expire() ){
                        // $response['error_type'] = 'SESSION_EXPIRE';
                        return null;
                    }
                    $actions = array();
                    for($i=0; $i<Module_task_helper::$MAX_MODULE_ACTIONS; $i++){
                        $actions[]='user_group.action_'.$i;
                    }
                    $action = implode(',', $actions);
                    //$user = $CI->db->get_where(TABLE_LOGIN_SETUP_USER, array('id' => $result_session['user_id'],'status'=>SYSTEM_STATUS_ACTIVE))->row();
                    $CI->db->from(TABLE_LOGIN_SETUP_USER.' user');
                    $CI->db->join(TABLE_SYSTEM_USER_GROUP.' user_group', 'user_group.id = user.user_group_id');
                    $CI->db->select("user.*");
                    if($action){$CI->db->select($action);}
                    $CI->db->where('user.id',$result_session['user_id']);
                    $user = $CI->db->get()->row_array();
                    if($user){
                        $CI->load->helper('encrypt_decrypt');
                        //$token_csrf_generated = Encrypt_decrypt_helper::get_encrypt('CSRF_'.$time.'_'.$user['id']);
                        $data_session = array(
                            'time_start'=> $time,
                            //'csrf_new'=> $token_csrf_generated,
                            //'csrf_old'=> $result_session['csrf_new'],
                        );
                        Query_helper::update(TABLE_LOGIN_USER_SESSIONS,$data_session,array("id = ".$result_session['id']),false);
                        User_helper::$logged_user = $user;
                        return User_helper::$logged_user;
                    } else {
                        return null;
                    }
                } else {
                    return null;
                }
            } else {
                return null;
            }
        }
    }
    public static function get_user_response_info($user){
        $CI = & get_instance();
        $CI->load->helper('upload');
        $response_user = array();
        $response_user['id'] = $user['id'];
        $response_user['name_en'] = $user['name_en'];
        $response_user['name_bn'] = $user['name_bn'];
        $response_user['info'] = array(
            'id'=>$user['id'],
            'profile_picture'=>Upload_helper::$IMAGE_LOGIN_BASE_URL.$user['image_location']
        );
        return $response_user;
    }

}