<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Module_task_helper
{
    public static $MAX_MODULE_ACTIONS=9;
    public static function get_modules_tasks_table_tree()
    {
        $CI=& get_instance();
        $CI->db->from(TABLE_SYSTEM_TASK);
        $CI->db->order_by('ordering');
        $results=$CI->db->get()->result_array();

        $children=array();
        foreach($results as $result)
        {
            $children[$result['parent']]['ids'][$result['id']]=$result['id'];
            $children[$result['parent']]['modules'][$result['id']]=$result;
        }

        $level0=$children[0]['modules'];
        $tree=array();
        $max_level=1;
        foreach ($level0 as $module)
        {
            Module_task_helper::get_sub_modules_tasks_tree($module,'','',1,$max_level,$tree,$children);
        }
        return array('max_level'=>$max_level,'tree'=>$tree);
    }
    public static function get_sub_modules_tasks_tree($module,$parent_class,$prefix,$level,&$max_level,&$tree,$children)
    {
        if($level>$max_level)
        {
            $max_level=$level;
        }
        $tree[]=array('parent_class'=>$parent_class,'prefix'=>$prefix,'level'=>$level,'module_task'=>$module);
        $subs=array();
        if(isset($children[$module['id']]))
        {
            $subs=$children[$module['id']]['modules'];
        }
        if(sizeof($subs)>0)
        {
            foreach($subs as $sub)
            {
                Module_task_helper::get_sub_modules_tasks_tree($sub,$parent_class.' parent_'.$module['id'],$prefix.'- ',$level+1,$max_level,$tree,$children);
            }
        }
    }
    public static function get_users_tasks($user)
    {
        if(!$user){
            return array();
        }
        $CI = & get_instance();
        $role_data=array();
        if(strlen($user['action_0'])>1)
        {
            $role_data=explode(',',trim($user['action_0'],','));
        }
        $CI->db->from(TABLE_SYSTEM_TASK);
        $CI->db->order_by('ordering');
        $results=$CI->db->get()->result_array();
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
            $tree = Module_task_helper::get_user_sub_tasks($children, $children[0]);
        }
        return $tree;
    }
    public static function get_user_sub_tasks(&$list, $parent)
    {
        // $CI = & get_instance();
        $tree = array();
        foreach ($parent as $key=>$element)
        {
            //$tree[] = $element;
            if(isset($list[$element['id']]))
            {
                $children=Module_task_helper::get_user_sub_tasks($list, $list[$element['id']]);
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
}
