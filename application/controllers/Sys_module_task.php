<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sys_module_task extends Root_Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    /*public function index()
    {
        echo SYSTEM_STATUS_ACTIVE;
    }*/
    public function initialize()
    {
        $ajax['error_type']='UNAUTHORIZED';
        $user = User_helper::get_user();
        if($user){
            if($this->permissions['action_0']==1){
                $ajax['error_type']='';
                $ajax['permissions']=$this->permissions;
                $table_fields = $this->db->field_data(TABLE_SYSTEM_TASK);
                $ajax["default_item"]=array();
                $ajax['hidden_columns']=array();
                foreach ($table_fields as $field)
                {
                    $ajax["default_item"][$field->name]=$field->default;
                }
                if($ajax['permissions']['action_8']==1)
                {
                    $result=Query_helper::get_info(TABLE_SYSTEM_USER_HIDDEN_COLUMNS,'*',array('controller="'.get_class($this).'"','method="list"','user_id='.$user['id']),1);
                    if($result && $result['columns'])
                    {
                        $ajax['hidden_columns']=json_decode($result['columns'],true);
                    }
                    //$ajax['hidden_columns']=array('status');
                }
                $ajax['types']=array('MODULE','TASK','TASK_GROUP');
                //$ajax['modules_tasks']=Module_task_helper::get_modules_tasks_table_tree();
            }
        }
        $this->json_return($ajax);
    }
    public function get_items()
    {
        $ajax['error_type']='UNAUTHORIZED';
        $user = User_helper::get_user();
        if($user){
            if($this->permissions['action_0']==1){
                $ajax['error_type']='';
                $modules_tasks=Module_task_helper::get_modules_tasks_table_tree();
                if($modules_tasks)
                {
                    $ajax['max_level']=$modules_tasks['max_level'];
                    $ajax['modules_tasks']=$modules_tasks['tree'];
                }
            }
        }
        $this->json_return($ajax);
    }
    public function get_item()
    {
        $ajax['error_type']='UNAUTHORIZED';
        $item_id=$this->input->post('item_id');
        if(!($item_id)>0){
            $ajax['error_type']='ID_NOT_FOUND';
            $this->json_return($ajax);
        }
        $user = User_helper::get_user();
        if($user){
            if($this->permissions['action_1']==1 || $this->permissions['action_2']==1){
                $ajax['error_type']='';
                $ajax['item']=Query_helper::get_info(TABLE_SYSTEM_TASK,'*',array('id ='.$item_id),1);
            }
        }
        $this->json_return($ajax);
    }
    public function save_item()
    {
        $ajax['error_type']='UNAUTHORIZED';
        $user = User_helper::get_user();
        if($user){
            if($this->permissions['action_1']==1 || $this->permissions['action_2']==1){
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
                    $ajax['error_type']='SAVE_ERROR';
                }
            }
        }
        $this->json_return($ajax);



        /*//verify
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
        $this->json_return($ajax);*/
    }


}
