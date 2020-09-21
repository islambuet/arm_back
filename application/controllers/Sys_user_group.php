<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sys_user_group extends Root_Controller
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
        $table_fields = $this->db->field_data(TABLE_SYSTEM_USER_GROUP);
        $ajax["default_item"]=array();
        //$ajax['hidden_columns']=array('status');
        foreach ($table_fields as $field)
        {
            $ajax["default_item"][$field->name]=$field->default;
        }
        $ajax['hidden_columns']=array();
        if($ajax['permissions']['action8']==1)
        {
            $result=Query_helper::get_info(TABLE_SYSTEM_USER_HIDDEN_COLUMNS,'*',array('controller="Sys_user_group"','method="list"','user_id=1'),1);
            if($result && $result['columns'])
            {
                $ajax['hidden_columns']=json_decode($result['columns'],true);
            }
            //$ajax['hidden_columns']=array('status');
        }
        $ajax['max_modules_tasks_level']=1;
        $ajax['modules_tasks']=array();
        $modules_tasks=Module_task_helper::get_modules_tasks_table_tree();
        if($modules_tasks)
        {
            $ajax['max_modules_tasks_level']=$modules_tasks['max_level'];
            $ajax['modules_tasks']=$modules_tasks['tree'];
        }
        $ajax['max_module_task_action']=Module_task_helper::$MAX_MODULE_ACTIONS;

        $this->json_return($ajax);
    }
    public function get_items()
    {
        $ajax['error_type']='';
        $ajax['items']=Query_helper::get_info(TABLE_SYSTEM_USER_GROUP,'*',array('status!="'.SYSTEM_STATUS_DELETE.'"'),0,0,array('ordering ASC'));
        $this->json_return($ajax);
    }
    public function get_item()
    {
        $ajax['error_type']='';
        $item_id=$this->input->post('item_id');
        $ajax['item']=Query_helper::get_info(TABLE_SYSTEM_USER_GROUP,'*',array('id ='.$item_id),1);
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
            Query_helper::update(TABLE_SYSTEM_USER_GROUP,$data,array("id = ".$item_id));
        }
        else
        {
            $data['user_created'] = $user->id;
            $data['date_created'] = time();
            Query_helper::add(TABLE_SYSTEM_USER_GROUP,$data);
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
    public function save_role()
    {
        //verify
        //auth token
        $user=new stdClass();
        $user->id=1;
        //permissions
        //save token
        $ajax['error_type']='';
        $item_id=$this->input->post('item_id');
        $ajax['data']=$this->input->post();
        /*$data=$this->input->post('item');
        $time = time();
        $this->db->trans_start();  //DB Transaction Handle START
        if($item_id>0)
        {
            $data['user_updated'] = $user->id;;
            $data['date_updated'] = $time;
            Query_helper::update(TABLE_SYSTEM_USER_GROUP,$data,array("id = ".$item_id));
        }
        else
        {
            $data['user_created'] = $user->id;
            $data['date_created'] = time();
            Query_helper::add(TABLE_SYSTEM_USER_GROUP,$data);
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
        }*/
        $this->json_return($ajax);
    }


}
