<?php
namespace Model;

class Tag{
    public static function find_all(){
        $query = \DB::select('tag.id', 'tag.name', 'tag.color');
        $query->from('tag');
        $query->order_by('tag.id', 'asc');
        return $query->execute()->as_array();
    }
}