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
}
