<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends Root_Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    public function index()
    {
        echo SYSTEM_STATUS_ACTIVE;
    }
    public function initialize() {
        //$ajax['error_type']='';
        $user = User_helper::get_user();
        if($user){
            $response['error_type'] = '';
            $response['user'] = User_helper::get_user_response_info($user);
            $response['user']['tasks'] = Module_task_helper::get_users_tasks($user);
        } else {
            $response['error_type'] = 'USER_NOT_FOUND';
        }
        $this->json_return($response);
    }

}
