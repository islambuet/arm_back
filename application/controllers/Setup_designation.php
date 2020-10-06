<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: Md Maraj Hossain
 * Date: 9/29/20
 * Time: 3:07 PM
 */

class Setup_designation  extends Root_Controller {
    public $message;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
    }
    public function initialize()
    {
        $ajax['error_type']='UNAUTHORIZED';
        $user = User_helper::get_user();
        if($user){
            if($this->permissions['action_0']==1){
                $ajax['error_type']='';
                $ajax['permissions']=$this->permissions;
                $table_fields = $this->db->field_data(TABLE_LOGIN_SETUP_DESIGNATION);
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
                $ajax['designations']=$this->get_designation_table_tree();
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
                $ajax['items']=$this->get_designation_table_tree();
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
            if($this->permissions['action_2']==1){
                $ajax['error_type']='';
                $item=Query_helper::get_info(TABLE_LOGIN_SETUP_DESIGNATION,'*',array('id ='.$item_id),1);
                if(!$item){
                    // should be use hack table
                    $ajax['error_type']='INVALID_TRY';
                    $this->json_return($ajax);
                }
                if($item['status']==SYSTEM_STATUS_DELETE){
                    // should be use hack table
                    $ajax['error_type']='INVALID_TRY';
                    $this->json_return($ajax);
                }
                $ajax['item']=$item;
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
                if(!$this->check_validation())
                {
                    $ajax['error_type']='ERROR_DATA';
                    $ajax['error_message']=$this->message;
                    $this->json_return($ajax);
                }
                if($item_id>0)
                {
                    $item=Query_helper::get_info(TABLE_LOGIN_SETUP_DESIGNATION,'*',array('id ='.$item_id),1);
                    if(!$item){
                        // should be use hack table
                        $ajax['error_type']='INVALID_TRY';
                        $this->json_return($ajax);
                    }
                    if($item['status']==SYSTEM_STATUS_DELETE){
                        // should be use hack table
                        $ajax['error_type']='INVALID_TRY';
                        $this->json_return($ajax);
                    }
                }

                Encrypt_decrypt_helper::csrf_check();

                $this->db->trans_start();  //DB Transaction Handle START
                if($item_id>0)
                {
                    $data['user_updated'] = $user['id'];
                    $data['date_updated'] = $time;
                    Query_helper::update(TABLE_LOGIN_SETUP_DESIGNATION,$data,array("id = ".$item_id));
                }
                else
                {
                    $data['user_created'] = $user['id'];
                    $data['date_created'] = time();
                    Query_helper::add(TABLE_LOGIN_SETUP_DESIGNATION,$data);
                }
                $token_csrf = Encrypt_decrypt_helper::csrf_update();
                $this->db->trans_complete();   //DB Transaction Handle END
                if ($this->db->trans_status() === TRUE)
                {
                    $ajax['error_type']='';
                    $ajax['token_csrf']=$token_csrf;
                }
                else
                {
                    $ajax['error_type']='SAVE_ERROR';
                }
            }
        }
        $this->json_return($ajax);
    }
    private function check_validation()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('item[name]','Name','required');
        $this->form_validation->set_rules('item[parent]','Parent','required');
        $this->form_validation->set_rules('item[status]','Status','required');
        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
    }
    private function get_designation_table_tree()
    {
        $this->db->from(TABLE_LOGIN_SETUP_DESIGNATION);
        $this->db->order_by('ordering');
        $results=$this->db->get()->result_array();
        $children=array();
        foreach($results as $result)
        {
            $children[$result['parent']]['ids'][$result['id']]=$result['id'];
            $children[$result['parent']]['designations'][$result['id']]=$result;
        }
        $level0=$children[0]['designations'];
        $tree=array();
        foreach ($level0 as $designation)
        {
            $this->get_sub_designation_tree($designation,'',$tree,$children);
        }
        return $tree;
    }

    private function get_sub_designation_tree($designation,$prefix,&$tree,$children)
    {
        //$tree[]=array('prefix'=>$prefix,'designation'=>$designation);
        $designation['name'] = $prefix.$designation['name'];
        $tree[]=$designation;
        $subs=array();
        if(isset($children[$designation['id']]))
        {
            $subs=$children[$designation['id']]['designations'];
        }
        if(sizeof($subs)>0)
        {
            foreach($subs as $sub)
            {
                $this->get_sub_designation_tree($sub,$prefix.'- ',$tree,$children);
            }
        }
    }
} 