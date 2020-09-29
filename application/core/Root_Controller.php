<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

abstract class Root_Controller extends CI_Controller
{
    public $permissions;
    function __construct()
    {
        parent::__construct();
        Configuration_helper::load_config();
        $this->permissions = Module_task_helper::get_permission(get_class($this));
    }
    public function json_return($array)
    {
        header('Content-type: application/json');
        echo json_encode($array);
        exit();
    }
}
