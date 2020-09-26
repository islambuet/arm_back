<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Setup_locations extends Root_Controller
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
        $user_group=Query_helper::get_info(TABLE_SYSTEM_USER_GROUP,'*',array('id =1'),1);
        $task_location=Query_helper::get_info(TABLE_SYSTEM_TASK,array('id'),array('controller ="Setup_locations"'),1);
        $sub_tasks=Query_helper::get_info(TABLE_SYSTEM_TASK,array(),array('parent ='.$task_location['id']),0,0,array('ordering ASC'));
        $ajax['permissions']=array();
        foreach($sub_tasks as $task)
        {
            if(strpos($user_group['action_0'],','.$task['id'].',')!==false)
            {
                $permissions=array();
                for($i=0;$i<Module_task_helper::$MAX_MODULE_ACTIONS;$i++)
                {
                    $permissions['action_'.$i]=(strpos($user_group['action_'.$i],','.$task['id'].',')!==false)?1:0;
                }

                $ajax['permissions'][$task['controller']]=$permissions;
            }
        }
        $this->json_return($ajax);
    }
    public function get_items_region()
    {
        $ajax['error_type']='';
        $ajax['items']=Query_helper::get_info(TABLE_LOGIN_LOCATION_REGION,'*',array('status!="'.SYSTEM_STATUS_DELETE.'"'),0,0,array('ordering ASC'));
        $this->json_return($ajax);
    }
    public function get_items_area()
    {
        $ajax['error_type']='';
        $ajax['items']=Query_helper::get_info(TABLE_LOGIN_LOCATION_AREA,'*',array('status!="'.SYSTEM_STATUS_DELETE.'"'),0,0,array('ordering ASC'));
        $this->json_return($ajax);
    }
    public function get_items_territory()
    {
        $ajax['error_type']='';
        $ajax['items']=Query_helper::get_info(TABLE_LOGIN_LOCATION_TERRITORY,'*',array('status!="'.SYSTEM_STATUS_DELETE.'"'),0,0,array('ordering ASC'));
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
        $user_group=Query_helper::get_info(TABLE_SYSTEM_USER_GROUP,'*',array('id ='.$item_id),1);//validation
        $tasks=$this->input->post('tasks');
        foreach($tasks as $task)
        {
            if(isset($task['actions']))
            {
                $task['actions'][0]=1;
            }
            else
            {
                $task['actions'][0]=0;
            }
            for($i=0;$i<Module_task_helper::$MAX_MODULE_ACTIONS;$i++)
            {
                $user_group['action_'.$i]=str_replace(','.$task['task_id'].',',',' ,$user_group['action_'.$i]);//remove the task from action
                if(isset($task['actions'][$i]))
                {
                    if(($task['actions'][$i])==1)
                    {
                        $user_group['action_'.$i].=$task['task_id'].',';//add the task into action
                    }
                }

            }

        }
        $time = time();
        $this->db->trans_start();  //DB Transaction Handle START
        $data['user_updated'] = $user->id;;
        $data['date_updated'] = $time;
        Query_helper::update(TABLE_SYSTEM_USER_GROUP,$user_group,array("id = ".$item_id));
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
