<?php
namespace Model;

class Tag{
    public static function find_all(){
        $query = \DB::select('tag.id', 'tag.name', 'tag.color');
        $query->from('tag');
        $query->order_by('tag.id', 'asc');
        return $query->execute()->as_array();
    }
    
    public static function find_by_id($id){
        $query = \DB::select('tag.id', 'tag.name', 'tag.color');
        $query->from('tag');
        $query->where('tag.id', $id);
        return $query->execute()->current();
    }

    public static function create($values){
        return \DB::insert('tag')->set($values)->execute();
    }

    public static function update($id, $values){
        return \DB::update('tag')->set($values)->where('id', $id)->execute();
    }

    public static function delete($id){
        return \DB::delete('tag')->where('id', $id)->execute();
    }
}