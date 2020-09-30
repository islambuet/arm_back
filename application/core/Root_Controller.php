<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

abstract class Root_Controller extends CI_Controller
{
    public $permissions;
    function __construct()
    {
        parent::__construct();
        Configuration_helper::load_config();
        $this->check_off_line();
        $this->permissions = Module_task_helper::get_permission(get_class($this));
    }
    public function json_return($array)
    {
        header('Content-type: application/json');
        echo json_encode($array);
        exit();
    }
    public function check_off_line(){
        if(Configuration_helper::is_site_off_line()){
            $controller = strtolower($this->router->class);
            $method = strtolower($this->router->method);
            if(!(
            ($controller == 'login') ||
            (($controller == 'user') && ($method=='initialize'))
            )){
                $user = User_helper::get_user();
                if($user){
                    if(!(($user['user_group_id']==1) || ($user['user_group_id']==2))){
                        $this->json_return(array('error_type'=>'SITE_OFF_LINE'));
                    }
                } else {
                    $this->json_return(array('error_type'=>'SITE_OFF_LINE'));
                }
            }
        }
    }
}
