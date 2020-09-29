<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Users extends Root_Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    public function index()
    {
        echo SYSTEM_STATUS_ACTIVE;
    }
    public function get_tasks() {
        //$ajax['error_type']='';
        $user = User_helper::get_user();
        $tasks=Module_task_helper::get_users_tasks($user);
        $ajax['error_type'] = '';
        $ajax['user']=array();
        $ajax['user']['tasks']=array();
        if($tasks){
            $ajax['error_type'] = '';
            $ajax['user']=$user;
            $ajax['user']['tasks']=$tasks;
        }
        $this->json_return($ajax);
    }
    public function get_user(){
        $user = User_helper::get_user();
        if($user){
            $ajax['error_type']='';
            $ajax['user']['id'] = $user['id'];
            $ajax['user']['name_en'] = $user['name_en'];
            $ajax['user']['name_bn'] = $user['name_bn'];
            $this->json_return($ajax);
        } else {
            $ajax['error_type']='NOT_FOUND';
        }
        $this->json_return($ajax);
    }
    public function get_permission(){
        $permission = Module_task_helper::get_permission('Sys_module_task');
        echo '<pre>';
        print_r($permission);
        echo '</pre>';
    }
    /*private function get_users_tasks($user_group_id) // user parameter
    {
        // if user == null return blank array
        $user_group=Query_helper::get_info(TABLE_SYSTEM_USER_GROUP,'*',array('id ='.$user_group_id),1);
        $role_data=array();
        if(strlen($user_group['action_0'])>1)
        {
            $role_data=explode(',',trim($user_group['action_0'],','));
        }
        $this->db->from(TABLE_SYSTEM_TASK);
        $this->db->order_by('ordering');
        $results=$this->db->get()->result_array();
        $children=array();
        foreach($results as $result)
        {
            if($result['type']=='TASK')
            {
                if(in_array($result['id'],$role_data))
                {
                    $children[$result['parent']][$result['id']]=$result;
                }
            }
            else
            {
                $children[$result['parent']][$result['id']]=$result;
            }
        }
        $tree=array();
        if(isset($children[0]))
        {
            $tree = $this->get_user_sub_tasks($children, $children[0]);
        }
        return $tree;
    }
    function get_user_sub_tasks(&$list, $parent)
    {
        $tree = array();
        foreach ($parent as $key=>$element)
        {
            //$tree[] = $element;
            if(isset($list[$element['id']]))
            {
                $children=$this->get_user_sub_tasks($list, $list[$element['id']]);
                if($children)
                {
                    $element['children'] = $children;
                    $tree[] = $element;
                }
            }
            else
            {
                if($element['type']=='TASK')
                {
                    $tree[] = $element;
                }
            }
        }

        return $tree;
    }*/

}
