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
        $ajax['user'] = User_helper::get_user();
        //$ajax['tasks']=$this->get_users_tasks($user);
        //$ajax['tasks']=$this->get_users_tasks(1);
        $this->json_return($ajax);
    }
    private function get_users_tasks($user_group_id) // user parameter
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
    }
    public function get_user(){

    }

}
