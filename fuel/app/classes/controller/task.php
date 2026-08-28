<?php
class Controller_Task extends Controller_Template{
    public function action_index(){
        $tasks = \Model\Task::find_all();
        $defaults = array(
            'memo' => '',
            'start_date' => '',
            'deadline' => '未設定',
            'tag_name' => '',
            'tag_color' => '',
        );

        foreach ($tasks as $key => $task){
            foreach ($defaults as $column => $default){
                if ($task[$column] === null){
                    $tasks[$key][$column] = $default;
                }
            }
        }

        $this->template->content = View::forge('task/index', array('tasks' => $tasks));
    }
}