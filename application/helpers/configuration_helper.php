<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
1	LOGIN_MAX_WRONG_PASSWORD -- Maximum allowed wrong password.2 means account will suspend 3rd time	-- 3
2	MOBILE_VERIFICATION	-- 1 for active 0 for off	-- 0
3	LOGIN_OTP_LIMIT_LAST_NUMBER	-- table last number of opt limit query -- 5
4	LOGIN_NUMBER_OF_OTP_CHECK	-- number of opt checking compare with otp table 	-- 5
5	LOGIN_OTP_EXPIRES	-- 60*5--5minutes	-- 2000
6	LOGIN_OTP_INTERVAL	-- next time opt send (config value second)	-- 100
7	LOGIN_SESSION_EXPIRES -- session expire time (config value second) -- 10000
8	SITE_OFF_LINE -- Configuration for keeping the Site Offline or, Not.	-- 1232
*/
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
    public static function get_otp_interval()
    {
        return isset(Configuration_helper::$config['LOGIN_OTP_INTERVAL'])?Configuration_helper::$config['LOGIN_OTP_INTERVAL']:10;
    }
    public static function get_session_expire()
    {
        return isset(Configuration_helper::$config['LOGIN_SESSION_EXPIRES'])?Configuration_helper::$config['LOGIN_SESSION_EXPIRES']:10;
    }
    public static function is_site_off_line()
    {
        return isset(Configuration_helper::$config['SITE_OFF_LINE'])&&(Configuration_helper::$config['SITE_OFF_LINE']==1)?true:false;
    }
    /* END LOGIN PURPOSE*/

}
