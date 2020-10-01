<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: Md Maraj Hossain
 * Date: 9/29/20
 * Time: 3:07 PM
 */

class Setup_system_configures  extends Root_Controller {
    public function __construct()
    {
        parent::__construct();
    }
    public function initialize()
    {
        $ajax['error_type']='UNAUTHORIZED';
        $user = User_helper::get_user();
        if($user){
            if($this->permissions['action_0']==1){
                $ajax['error_type']='';
                $ajax['permissions']=$this->permissions;
                $table_fields = $this->db->field_data(TABLE_LOGIN_SETUP_SYSTEM_CONFIGURES);
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
                $ajax['items']=Query_helper::get_info(TABLE_LOGIN_SETUP_SYSTEM_CONFIGURES,'*',array('status!="'.SYSTEM_STATUS_DELETE.'"'));
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
                $ajax['item']=Query_helper::get_info(TABLE_LOGIN_SETUP_SYSTEM_CONFIGURES,'*',array('id ='.$item_id),1);
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
                Encrypt_decrypt_helper::csrf_check();
                $item_id=$this->input->post('item_id');
                $data=$this->input->post('item');
                $time = time();
                $this->db->trans_start();  //DB Transaction Handle START
                if($item_id>0)
                {
                    $data['user_updated'] = $user->id;;
                    $data['date_updated'] = $time;
                    Query_helper::update(TABLE_LOGIN_SETUP_SYSTEM_CONFIGURES,$data,array("id = ".$item_id));
                }
                else
                {
                    $data['user_created'] = $user->id;
                    $data['date_created'] = time();
                    Query_helper::add(TABLE_LOGIN_SETUP_SYSTEM_CONFIGURES,$data);
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
} 