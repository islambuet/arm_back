<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sys_module_task extends Root_Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    public function index()
    {
        echo SYSTEM_STATUS_ACTIVE;
    }
    public function initialize()
    {
        $ajax['error_type']='';
        $ajax['permissions']=array('action0'=>1,'action1'=>1,'action2'=>1,'action3'=>1,'action4'=>1,'action5'=>1,'action6'=>1,'action7'=>1,'action8'=>1);
        $ajax["default_item"] = Array(
            'id' => 0,
            'name_en' => 'English',
            'name_bn' => 'Bangla',
            'type' => '',
            'parent' => 0,
            'controller' => '',
            'ordering' => 99,
            'status' => SYSTEM_STATUS_ACTIVE,
            'status_notification' => '',
        );
        $ajax['types']=array('MODULE','TASK','TASK_GROUP');
        //$ajax['modules_tasks']=Module_task_helper::get_modules_tasks_table_tree();
        $this->json_return($ajax);
    }
    public function get_items()
    {
        $ajax['error_type']='';
        $modules_tasks=Module_task_helper::get_modules_tasks_table_tree();
        if($modules_tasks)
        {
            $ajax['max_level']=$modules_tasks['max_level'];
            $ajax['modules_tasks']=$modules_tasks['tree'];
        }
        $this->json_return($ajax);
    }
    public function get_item()
    {
        $ajax['error_type']='';
        $item_id=$this->input->post('item_id');
        $ajax['item']=Query_helper::get_info(TABLE_SYSTEM_TASK,'*',array('id ='.$item_id),1);
        $this->json_return($ajax);
    }
    public function save_item()
    {
        //verify
        //auth token
        $user=new stdClass();
        $user->id=1;
        //permissions
        //save token
        $ajax['error_type']='';
        $item_id=$this->input->post('item_id');
        $data=$this->input->post('item');
        $time = time();
        $this->db->trans_start();  //DB Transaction Handle START
        if($item_id>0)
        {
            $data['user_updated'] = $user->id;;
            $data['date_updated'] = $time;
            Query_helper::update(TABLE_SYSTEM_TASK,$data,array("id = ".$item_id));
        }
        else
        {
            $data['user_created'] = $user->id;
            $data['date_created'] = time();
            Query_helper::add(TABLE_SYSTEM_TASK,$data);
        }
        //update token
        //$ajax[token_save]=new token
        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            $ajax['error_type']='';
        }
        else
        {
            $ajax['error_type']='Save Error';
        }
        $this->json_return($ajax);
    }


}
