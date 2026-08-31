<?php
class Controller_Task extends Controller_Template{
    public function action_index(){
        $tasks = \Model\Task::find_all();
        $tasks = $this->attach_subtasks($tasks);
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
        $view = View::forge('task/index', array('tasks' => $tasks, 'tags' => $tags, 'form' => $form));
        $view->set_safe('tasks_json', json_encode($tasks, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT));
        $this->template->content = $view;
    }

    public function action_edit($id){
        $tasks = \Model\Task::find_all();
        $tasks = $this->attach_subtasks($tasks);
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
        $form['subtasks'] = \Model\SubTask::find_by_task_ids(array($id));
        $view = View::forge('task/index', array('tasks' => $tasks, 'tags' => $tags, 'form' => $form));
        $view->set_safe('tasks_json', json_encode($tasks, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT));
        $this->template->content = $view;
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

    public function action_subtask_create(){
        $task_id = Input::post('task_id');
        $values = array(
            'task_id' => $task_id,
            'title' => Input::post('title'),
        );
        \Model\SubTask::create($values);
        Response::redirect('task/edit/'.$task_id);
    }

    public function action_subtask_toggle(){
        $id = Input::post('id');
        if (! ctype_digit($id)){
            return $this->json_response(array('error' => 'id is bad'), 400);
        }
        if (\Model\SubTask::toggle_done($id) === 0){
            return $this->json_response(array('error' => 'id not found'), 404);
        }
        $subtask = \Model\SubTask::find_by_id($id);
        return $this->json_response(array('id' => (int)$id, 'done' => (int)$subtask['done']));
    }

    public function action_subtask_delete(){
        $id = Input::post('id');
        $task_id = Input::post('task_id');
        \Model\SubTask::delete($id);
        Response::redirect('task/edit/'.$task_id);
    }

    public function action_toggle(){
        $id = Input::post('id');
        if (! ctype_digit($id)){
            return $this->json_response(array('error' => 'id is bad'), 400);
        }
        if (\Model\Task::toggle_done($id) === 0){
            return $this->json_response(array('error' => 'id not found'), 404);
        }
        $task = \Model\Task::find_by_id($id);
        return $this->json_response(array('id' => (int)$id, 'done' => (int)$task['done']));
    }

    private function attach_subtasks($tasks){
        $ids = array_column($tasks, 'id');
        $subtasks = \Model\SubTask::find_by_task_ids($ids);
        $counts = array();
        foreach (\Model\SubTask::count_by_task_ids($ids) as $row){
            $counts[$row['task_id']] = $row;
        }
        $grouped = array();
        foreach ($subtasks as $subtask){
            $grouped[$subtask['task_id']][] = $subtask;
        }
        foreach ($tasks as $key => $task){
            $rows = isset($grouped[$task['id']]) ? $grouped[$task['id']] : array();
            $count = isset($counts[$task['id']]) ? $counts[$task['id']] : null;
            foreach ($rows as $i => $subtask){
                $rows[$i]['id'] = (int) $subtask['id'];
                $rows[$i]['done'] = (int) $subtask['done'];
            }
            $tasks[$key]['id'] = (int) $task['id'];
            $tasks[$key]['done'] = (int) $task['done'];
            $tasks[$key]['subtasks'] = $rows;
            $tasks[$key]['total_count'] = $count ? (int)$count['total_count'] : 0;
            $tasks[$key]['done_count'] = $count ? (int)$count['done_count'] : 0;
        }
        return $tasks;
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
            $tasks[$key]['percent'] = $row['total_count'] ? round(($row['done_count']/$row['total_count'])*100) : 0;
            foreach ($defaults as $column => $default){
                if ($row[$column] === null){
                    $tasks[$key][$column] = $default;
                }
            }
        }
        return $tasks;
    }

    private function json_response($data, $status = 200){
        return new Response(json_encode($data), $status, array('Content-Type' => 'application/json'));
    }
}