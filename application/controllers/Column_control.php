<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Column_control extends Root_Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    public function save()
    {
        $ajax['error_type']='UNAUTHORIZED';
        $user = User_helper::get_user();
        $controller=$this->input->post('controller');
        $method=$this->input->post('method');
        $permission = Module_task_helper::get_permission($controller);
        if($user){
            if($permission['action_1']==1){
                Encrypt_decrypt_helper::csrf_check();
                $data=array();
                $hidden_columns=json_encode($this->input->post('hidden_columns'));//need verification
                $data['columns']=$hidden_columns;
                $item=Query_helper::get_info(TABLE_SYSTEM_USER_HIDDEN_COLUMNS,'*',array('user_id='.$user['id'],'controller="'.$controller.'"','method="'.$method.'"'),1);
                $time = time();
                $this->db->trans_start();  //DB Transaction Handle START
                if($item)
                {   $data['user_updated'] = $user['id'];;
                    $data['date_updated'] = $time;
                    Query_helper::update(TABLE_SYSTEM_USER_HIDDEN_COLUMNS,$data,array("id = ".$item['id']));
                }
                else
                {
                    $data['user_id']=$user['id'];
                    $data['controller']=$controller;
                    $data['method']=$method;
                    $data['user_created'] = $user['id'];
                    $data['date_created'] = time();
                    Query_helper::add(TABLE_SYSTEM_USER_HIDDEN_COLUMNS,$data);
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
