<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Configuration_helper
{
    public static $config = array();
    public static function load_config()
    {
        $results = Query_helper::get_info(TABLE_LOGIN_SETUP_SYSTEM_CONFIGURES,'*',array('status = "'.SYSTEM_STATUS_ACTIVE.'"'));
        foreach($results as $result){
            Configuration_helper::$config[$result['purpose']]=$result['config_value'];
        }
    }
    /* START LOGIN PURPOSE*/
    public static function get_mobile_verification_status()
    {
        return isset(Configuration_helper::$config['MOBILE_VERIFICATION'])?Configuration_helper::$config['MOBILE_VERIFICATION']:0;
    }
    public static function get_max_wrong_password()
    {
        return isset(Configuration_helper::$config['LOGIN_MAX_WRONG_PASSWORD'])?Configuration_helper::$config['LOGIN_MAX_WRONG_PASSWORD']:2;
    }
    public static function get_otp_limit_last_number()
    {
        return isset(Configuration_helper::$config['LOGIN_OTP_LIMIT_LAST_NUMBER'])?Configuration_helper::$config['LOGIN_OTP_LIMIT_LAST_NUMBER']:5;
    }
    public static function get_number_of_otp_check()
    {
        return isset(Configuration_helper::$config['LOGIN_NUMBER_OF_OTP_CHECK'])?Configuration_helper::$config['LOGIN_NUMBER_OF_OTP_CHECK']:5;
    }
    public static function get_otp_expire()
    {
        return isset(Configuration_helper::$config['LOGIN_OTP_EXPIRES'])?Configuration_helper::$config['LOGIN_OTP_EXPIRES']:2000;
    }
    /* END LOGIN PURPOSE*/

}
