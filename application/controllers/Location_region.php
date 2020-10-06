<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Location_region  extends Root_Controller {
    private $message;
    public function __construct()
    {
        parent::__construct();
        $this->message='';
    }
    public function initialize()
    {
        $ajax = array();
        $ajax['error_type']='UNAUTHORIZED';
        $user = User_helper::get_user();
        if($user){
            if($this->permissions['action_0']==1){
                $ajax['error_type']='';
                $ajax['permissions']=$this->permissions;
                $ajax['default_item']=array();
                $ajax['hidden_columns']=array();
                
                $table_fields = $this->db->field_data(TABLE_LOGIN_LOCATION_REGION);
                foreach ($table_fields as $field)
                {
                    $ajax['default_item'][$field->name]=$field->default;
                }
                if($ajax['permissions']['action_8']==1)
                {
                    $result=Query_helper::get_info(TABLE_SYSTEM_USER_HIDDEN_COLUMNS,'*',array('controller="'.get_class($this).'"','method="list"','user_id='.$user['id']),1);
                    if($result && $result['columns'])
                    {
                        $ajax['hidden_columns']=json_decode($result['columns'],true);
                    }
                }
            }
        }
        $this->json_return($ajax);
    }
    public function get_items()
    {
        $ajax = array();
        $ajax['error_type']='UNAUTHORIZED';
        $user = User_helper::get_user();
        if($user){
            if($this->permissions['action_0']==1){
                $ajax['error_type']='';
                $ajax['items']=Query_helper::get_info(TABLE_LOGIN_LOCATION_REGION,'*',array('status!="'.SYSTEM_STATUS_DELETE.'"'));
            }
        }
        $this->json_return($ajax);
    }
    public function get_item()
    {
        $ajax = array();
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
                $ajax['item']=Query_helper::get_info(TABLE_LOGIN_LOCATION_REGION,'*',array('id ='.$item_id),1);
            }
        }
        $this->json_return($ajax);
    }
    public function save_item()
    {
        $ajax = array();
        $ajax['error_type']='UNAUTHORIZED';
        $user = User_helper::get_user();
        if($user){
            if($this->permissions['action_1']==1 || $this->permissions['action_2']==1){ // Permission check

                if(!$this->check_validation()) // Form validation
                {
                    $ajax['error_type']='ERROR_DATA';
                    $ajax['error_message']=$this->message;
                    $this->json_return($ajax);
                }
                Encrypt_decrypt_helper::csrf_check();

                $item_id=$this->input->post('item_id');
                $data=$this->input->post('item');
                $time = time();
                $this->db->trans_start();  //DB Transaction Handle START
                if($item_id>0)
                {
                    $data['user_updated'] = $user['id'];
                    $data['date_updated'] = $time;
                    Query_helper::update(TABLE_LOGIN_LOCATION_REGION,$data,array("id = ".$item_id));
                }
                else
                {
                    $data['user_created'] = $user['id'];
                    $data['date_created'] = time();
                    Query_helper::add(TABLE_LOGIN_LOCATION_REGION,$data);
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
        $this->form_validation->set_rules('item[name]', 'Name', 'required');
        $this->form_validation->set_rules('item[ordering]', 'Ordering','required');
        $this->form_validation->set_rules('item[status]', 'Status', 'required');
        $this->form_validation->set_error_delimiters(' ** ',' ');
        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
    }
} 