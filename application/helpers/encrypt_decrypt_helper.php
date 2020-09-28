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
    // get csrf
}
