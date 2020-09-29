<?php
defined('BASEPATH') OR exit('No direct script access allowed');
//CONST ENCRYPTED_DECRYPTED_METHOD = "AES-128-ECB";
//CONST ENCRYPTED_DECRYPTED_KEY = "123";
class Encrypt_decrypt_helper
{
    public static $ENCRYPTED_DECRYPTED_METHOD="AES-128-ECB";
    public static $ENCRYPTED_DECRYPTED_KEY="123456";
    public static function get_encrypt($string){
        return openssl_encrypt($string,Encrypt_decrypt_helper::$ENCRYPTED_DECRYPTED_METHOD,Encrypt_decrypt_helper::$ENCRYPTED_DECRYPTED_KEY);
    }
    public static function get_decrypt($string){
        return openssl_decrypt($string,Encrypt_decrypt_helper::$ENCRYPTED_DECRYPTED_METHOD,Encrypt_decrypt_helper::$ENCRYPTED_DECRYPTED_KEY);
    }
    public static function get_token($token)
    {
        $CI = & get_instance();
        $user=User_helper::get_user();
        $response = array();
        for($i=0; $i<Module_task_helper::$MAX_MODULE_ACTIONS; $i++){
            $permissions['action_'.$i]=0;
        }
        if($user){
            $CI->load->helper('encrypt_decrypt');
            $token_csrf_generated = Encrypt_decrypt_helper::get_encrypt('CSRF_'.$time.'_'.$user['id']);
            $data_session = array(
                'time_start'=> $time,
                'csrf_new'=> $token_csrf_generated,
                'csrf_old'=> $result_session['csrf_new'],
            );
            Query_helper::update(TABLE_LOGIN_USER_SESSIONS,$data_session,array("id = ".$result_session['id']),false);
        }
        return $response;
    }
}
