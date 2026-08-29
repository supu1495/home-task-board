<?php
namespace Model;

class SubTask{
    public static function find_by_task_ids($task_ids){
        if (empty($task_ids)){
            return array();
        }
        $query = \DB::select('sub_task.task_id', 'sub_task.id', 'sub_task.title', 'sub_task.done');
        $query->from('sub_task');
        $query->where('sub_task.deleted_at', null);
        $query->where('sub_task.task_id', 'IN', $task_ids);
        $query->order_by('sub_task.id', 'asc');
        return $query->execute()->as_array();
    }
    
    public static function create($values){
        return \DB::insert('sub_task')->set($values)->execute();
    }

    public static function toggle_done($id){
        return \DB::update('sub_task')->set(array('done' => \DB::expr('1 - done')))->where('id', $id)->where('deleted_at', null)->execute();
    }
    public static function delete($id){
        return \DB::update('sub_task')->set(array('deleted_at' => \DB::expr('NOW()')))->where('id', $id)->where('deleted_at', null)->execute();
    }
}