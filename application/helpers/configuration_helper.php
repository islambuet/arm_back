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
    public static function get_mobile_verification_status()
    {
        return isset(Configuration_helper::$config['MOBILE_VERIFICATION'])?Configuration_helper::$config['MOBILE_VERIFICATION']:0;
    }
    public static function get_max_wrong_password()
    {
        return isset(Configuration_helper::$config['LOGIN_MAX_WRONG_PASSWORD'])?Configuration_helper::$config['LOGIN_MAX_WRONG_PASSWORD']:0;
    }

}
