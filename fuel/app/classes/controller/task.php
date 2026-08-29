<?php
class Controller_Task extends Controller_Template{
    public function action_index(){
        $tasks = \Model\Task::find_all();
        $tasks = $this->format_tasks($tasks);
        $tags = \Model\Tag::find_all();

        $form = array(
            'id' => '',
            'title' => '',
            'start_date' => '',
            'deadline' => '',
            'tag_id' => '',
            'memo' => ''
        );

        $this->template->content = View::forge('task/index', array('tasks' => $tasks, 'tags' => $tags, 'form' => $form));
    }

    public function action_edit($id){
        $tasks = \Model\Task::find_all();
        $tasks = $this->format_tasks($tasks);
        $tags = \Model\Tag::find_all();
        $task = \Model\Task::find_by_id($id);

        if ( ! $task){
            Response::redirect('task/index');
        }
        
        $form = array(
            'id' => $task['id'],
            'title' => $task['title'],
            'start_date' => $task['start_date'],
            'deadline' => $task['deadline'],
            'tag_id' => $task['tag_id'],
            'memo' => $task['memo'],
        );

        foreach ($form as $column => $value){
            if ($form[$column] === null){
                $form[$column] = '';
            }
        }
        $this->template->content = View::forge('task/index', array('tasks' => $tasks, 'tags' => $tags, 'form' => $form));
    }

    public function action_create(){
        $values = array(
            'title' => Input::post('title'),
            'start_date' => Input::post('start_date'),
            'deadline' => Input::post('deadline'),
            'tag_id' => Input::post('tag_id'),
            'memo' => Input::post('memo'),
        );

        foreach (array('start_date', 'deadline', 'tag_id', 'memo') as $column){
            if ($values[$column] === ''){
                    $values[$column] = null;
            }
        }
        \Model\Task::create($values);
        Response::redirect('task/index');
    }

    public function action_update(){
        $id = Input::post('id');
        $values = array(
            'title' => Input::post('title'),
            'start_date' => Input::post('start_date'),
            'deadline' => Input::post('deadline'),
            'tag_id' => Input::post('tag_id'),
            'memo' => Input::post('memo'),
        );

        foreach (array('start_date', 'deadline', 'tag_id', 'memo') as $column){
            if ($values[$column] === ''){
                    $values[$column] = null;
            }
        }
        \Model\Task::update($id, $values);
        Response::redirect('task/index');
    }

    public function action_delete(){
        $id = Input::post('id');
        \Model\Task::delete($id);
        Response::redirect('task/index');
    }

    private function format_tasks($tasks){
        $limit = date('Y-m-d', strtotime('+3 days'));

        $defaults = array(
            'memo' => '',
            'start_date' => '',
            'deadline' => '未設定',
            'tag_name' => '',
            'tag_color' => '',
        );
        foreach ($tasks as $key => $row){
            $tasks[$key]['soon'] = ($row['deadline'] !== NULL && $row['deadline'] <= $limit);
            foreach ($defaults as $column => $default){
                if ($row[$column] === null){
                    $tasks[$key][$column] = $default;
                }
            }
        }
        return $tasks;
    }
}