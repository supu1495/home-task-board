<?php
namespace Model;

class Task{
    public static function find_all(){
        $query = \DB::select('task.id', 'task.title', 'task.start_date', 'task.deadline', array('tag.name', 'tag_name'), 'task.memo', 'task.done', array('tag.color', 'tag_color'));
        $query->from('task');
        $query->join('tag', 'LEFT')->on('task.tag_id', '=', 'tag.id');
        $query->where('task.deleted_at', null);
        $query->order_by('task.deadline', 'asc');
        return $query->execute()->as_array();
    }
    public static function find_by_id($id){
        $query = \DB::select('task.id', 'task.title', 'task.start_date', 'task.deadline', array('tag.name', 'tag_name'), 'task.memo', 'task.done', array('tag.color', 'tag_color'), 'task.tag_id');
        $query->from('task');
        $query->join('tag', 'LEFT')->on('task.tag_id', '=', 'tag.id');
        $query->where('task.deleted_at', null);
        $query->where('task.id', $id);
        return $query->execute()->current();
    }
    public static function create($values){
        return \DB::insert('task')->set($values)->execute();
    }
    public static function update($id, $values){
        return \DB::update('task')->set($values)->where('id', $id)->execute();
    }
    public static function delete($id){
        return \DB::update('task')->set(array('deleted_at' => \DB::expr('NOW()')))->where('id', $id)->where('deleted_at', null)->execute();
    }
}