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
    public static function csrf_update()
    {
        $CI = & get_instance();
        $time=time();
        $user=User_helper::get_user();
        $data = array();
        $data['csrf_old'] = $user['csrf_new'];
        $data['csrf_new'] =  Encrypt_decrypt_helper::get_encrypt('CSRF_'.$time.'_'.$user['id']);;
        $CI->load->helper('encrypt_decrypt');
        Query_helper::update(TABLE_LOGIN_USER_SESSIONS,$data,array("id = ".$user['csrf_id']),false);
        return $data['csrf_new'];
    }
    public static function csrf_check(){
        $CI = & get_instance();
        $time=time();
        $user=User_helper::get_user();
        $csrf = $CI->input->post('token_csrf');
//        echo '<pre>';
//        print_r($csrf);
//        echo '</pre>';
//        die();
        if($csrf!=$user['csrf_new']){
            $ajax['token_csrf']=$user['csrf_new'];
            if($csrf==$user['csrf_old']){
                $ajax['error_type'] = 'SAVE_ALREADY';
            } else {
                $ajax['error_type'] = 'TOKEN_INVALID';
            }

            $CI->json_return($ajax);
        }
    }
}
