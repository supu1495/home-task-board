<?php
class Controller_Task extends Controller_Template{
    public function before()
    {
        parent::before();
        if (Session::get('authenticated')){
            return;
        }
        if (Input::is_ajax()){
            $response = new Response(
                json_encode(array('error' => 'unauthorized')),
                401,
                array('Content-Type' => 'application/json')
            );
            $response->send(true);
            exit;
        }
        if (Input::method() === 'POST' and ! Security::check_token()){
            if (Input::is_ajax()){
                $response = new Response(
                    json_encode(array('error' => 'invalid token')),
                    403,
                    array('Content-Type' => 'application/json')
                );
                $response->send(true);
                exit;
            }
            Session::set_flash('message', '不正なリクエストです。もう一度お試しください。');
            Response::redirect('task/index');
        }
    }
    public function action_index(){
        $tasks = $this->build_tasks();
        $tags = $this->format_tags(\Model\Tag::find_all());
        $filter_tag_id = Cookie::get('filter_tag_id');
        $filter_tag_id = ctype_digit((string) $filter_tag_id) ? (int) $filter_tag_id : '';

        $form = array(
            'id' => '',
            'title' => '',
            'start_date' => '',
            'deadline' => '',
            'tag_id' => '',
            'memo' => ''
        );

        $old = Session::get_flash('old') ?: array();
        foreach ($old as $column => $value){
            if (array_key_exists($column, $form)){
                $form[$column] = $value;
            }
        }

        $view = View::forge('task/index', array('tags' => $tags, 'form' => $form, 'flash' => Session::get_flash('message') ?: '', 'filter_tag_id' => $filter_tag_id, 'errors' => Session::get_flash('errors') ?: array()));
        $view->set_safe('tasks_json', json_encode($tasks, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT));
        $view->set_safe('tags_json', json_encode($tags, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT));
        $this->template->content = $view;
    }

    public function action_edit($id){
        $tasks = $this->build_tasks();
        $tags = $this->format_tags(\Model\Tag::find_all());
        $task = \Model\Task::find_by_id($id);
        $filter_tag_id = Cookie::get('filter_tag_id');
        $filter_tag_id = ctype_digit((string) $filter_tag_id) ? (int) $filter_tag_id : '';

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

        $old = Session::get_flash('old') ?: array();
        foreach ($old as $column => $value){
            if (array_key_exists($column, $form)){
                $form[$column] = $value;
            }
        }

        foreach ($form as $column => $value){
            if ($form[$column] === null){
                $form[$column] = '';
            }
        }
        $form['subtasks'] = \Model\SubTask::find_by_task_ids(array($id));
        $view = View::forge('task/index', array('tags' => $tags, 'form' => $form, 'flash' => Session::get_flash('message') ?: '', 'filter_tag_id' => $filter_tag_id, 'errors' => Session::get_flash('errors') ?: array()));
        $view->set_safe('tasks_json', json_encode($tasks, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT));
        $view->set_safe('tags_json', json_encode($tags, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT));
        $this->template->content = $view;
    }

    public function action_create(){
        $values = array(
            'title'      => trim((string) Input::post('title')),
            'start_date' => (string) Input::post('start_date'),
            'deadline'   => (string) Input::post('deadline'),
            'tag_id'     => (string) Input::post('tag_id'),
            'memo'       => trim((string) Input::post('memo')),
        );

        $errors = $this->validate_task($values);
        if ($errors){
            Session::set_flash('errors', $errors);
            Session::set_flash('old', $values);
            Response::redirect('task/index');
        }

        foreach (array('start_date', 'deadline', 'tag_id', 'memo') as $column){
            if ($values[$column] === ''){
                $values[$column] = null;
            }
        }
        \Model\Task::create($values);
        Session::set_flash('message', 'タスクを登録しました');
        Response::redirect('task/index');
    }

    public function action_update(){
        $id = Input::post('id');
        if ( ! ctype_digit($id)){
            Response::redirect('task/index');
        }

        $values = array(
            'title'      => trim((string) Input::post('title')),
            'start_date' => (string) Input::post('start_date'),
            'deadline'   => (string) Input::post('deadline'),
            'tag_id'     => (string) Input::post('tag_id'),
            'memo'       => trim((string) Input::post('memo')),
        );

        $errors = $this->validate_task($values);
        if ($errors){
            Session::set_flash('errors', $errors);
            Session::set_flash('old', $values);
            Response::redirect('task/edit/'.$id);
        }

        foreach (array('start_date', 'deadline', 'tag_id', 'memo') as $column){
            if ($values[$column] === ''){
                $values[$column] = null;
            }
        }
        \Model\Task::update($id, $values);
        Session::set_flash('message', 'タスクを更新しました');
        Response::redirect('task/index');
    }

    public function action_delete(){
        $id = Input::post('id');
        \Model\Task::delete($id);
        Session::set_flash('message', 'タスクを削除しました');
        Response::redirect('task/index');
    }

    public function action_subtask_create(){
        $task_id = Input::post('task_id');
        $values = array(
            'task_id' => $task_id,
            'title' => Input::post('title'),
        );
        $title = trim((string) Input::post('title'));
        if ($title === '' or mb_strlen($title) > 50){
            Session::set_flash('errors', array('サブタスク名は1〜50文字で入力してください'));
            Response::redirect('task/edit/'.$task_id);
        }
        \Model\SubTask::create($values);
        Session::set_flash('message', 'サブタスクを追加しました');
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
        $task_id = $subtask['task_id'];
        $state = $this->task_state($task_id);
        $done = ($state['total_count'] > 0 && $state['done_count'] === $state['total_count']) ? 1 : 0;
        \Model\Task::set_done($task_id, $done);
        return $this->json_response($this->task_state($task_id));
    }

    public function action_subtask_delete(){
        $id = Input::post('id');
        $task_id = Input::post('task_id');
        \Model\SubTask::delete($id);
        Session::set_flash('message', 'サブタスクを削除しました');
        Response::redirect('task/edit/'.$task_id);
    }

    public function action_tag_create(){
        $name  = trim((string) Input::post('name'));
        $color = trim((string) Input::post('color'));

        if ($name === '' || mb_strlen($name) > 20){
            return $this->json_response(array('error' => 'name is bad'), 400);
        }
        if ($color !== '' && ! preg_match('/\A#[0-9a-fA-F]{6}\z/', $color)){
            return $this->json_response(array('error' => 'color is bad'), 400);
        }

        \Model\Tag::create(array(
            'name'  => $name,
            'color' => ($color === '' ? null : $color),
        ));
        return $this->json_response($this->board_state());
    }

    public function action_tag_update(){
        $id    = Input::post('id');
        $name  = trim((string) Input::post('name'));
        $color = trim((string) Input::post('color'));

        if ( ! ctype_digit($id)){
            return $this->json_response(array('error' => 'id is bad'), 400);
        }
        if ($name === '' || mb_strlen($name) > 20){
            return $this->json_response(array('error' => 'name is bad'), 400);
        }
        if ($color !== '' && ! preg_match('/\A#[0-9a-fA-F]{6}\z/', $color)){
            return $this->json_response(array('error' => 'color is bad'), 400);
        }
        if ( ! \Model\Tag::find_by_id($id)){
            return $this->json_response(array('error' => 'id not found'), 404);
        }

        \Model\Tag::update($id, array(
            'name'  => $name,
            'color' => ($color === '' ? null : $color),
        ));
        return $this->json_response($this->board_state());
    }

    public function action_tag_delete(){
        $id = Input::post('id');
        if ( ! ctype_digit($id)){
            return $this->json_response(array('error' => 'id is bad'), 400);
        }
        if (\Model\Tag::delete($id) === 0){
            return $this->json_response(array('error' => 'id not found'), 404);
        }
        return $this->json_response($this->board_state());
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
        if ((int) $task['done'] === 1){
            \Model\SubTask::set_done_by_task_id($id, 1);
        }
        return $this->json_response($this->task_state($id));
    }

    public function action_filter(){
        $tag_id = Input::post('tag_id');

        if ($tag_id === '' || $tag_id === null){
            Cookie::delete('filter_tag_id');
            return $this->json_response(array('tag_id' => null));
        }
        if ( ! ctype_digit($tag_id)){
            return $this->json_response(array('error' => 'tag_id is bad'), 400);
        }
        Cookie::set('filter_tag_id', $tag_id);
        return $this->json_response(array('tag_id' => (int) $tag_id));
    }

    private function validate_task($values){
        $errors = array();

        if ($values['title'] === ''){
            $errors[] = 'タイトルを入力してください';
        } elseif (mb_strlen($values['title']) > 50){
            $errors[] = 'タイトルは50文字以内で入力してください';
        }

        foreach (array('start_date' => '開始日', 'deadline' => '締切') as $column => $label){
            if ($values[$column] !== '' and ! $this->is_date($values[$column])){
                $errors[] = $label.'は正しい日付を入力してください';
            }
        }

        if ($values['start_date'] !== '' and $values['deadline'] !== ''
            and $values['start_date'] > $values['deadline']){
            $errors[] = '締切は開始日以降の日付を入力してください';
        }

        if ($values['tag_id'] !== ''){
            if ( ! ctype_digit($values['tag_id']) or ! \Model\Tag::find_by_id($values['tag_id'])){
                $errors[] = 'タグの指定が正しくありません';
            }
        }

        if (mb_strlen($values['memo']) > 255){
            $errors[] = 'メモは255文字以内で入力してください';
        }

        return $errors;
    }

    private function is_date($date){
        $parts = explode('-', $date);
        if (count($parts) !== 3){
            return false;
        }
        if ( ! ctype_digit($parts[0].$parts[1].$parts[2])){
            return false;
        }
        return checkdate((int) $parts[1], (int) $parts[2], (int) $parts[0]);
    }

    private function build_tasks(){
        $tasks = \Model\Task::find_all();
        $tasks = $this->attach_subtasks($tasks);
        return $this->format_tasks($tasks);
    }

    private function board_state(){
        return array(
            'tags'  => $this->format_tags(\Model\Tag::find_all()),
            'tasks' => $this->build_tasks(),
        );
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
            $tasks[$key]['tag_id'] = $task['tag_id'] === null ? null : (int) $task['tag_id'];
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

    private function format_tags($tags){
        foreach ($tags as $key => $tag){
            $tags[$key]['id'] = (int) $tag['id'];
            $tags[$key]['color'] = $tag['color'] === null ? '' : $tag['color'];
        }
        return $tags;
    }

    private function json_response($data, $status = 200){
        $data['csrf_token'] = Security::fetch_token();
        return new Response(json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), $status, array('Content-Type' => 'application/json'));
    }

    private function task_state($task_id){
        $task = \Model\Task::find_by_id($task_id);
        $rows = \Model\SubTask::find_by_task_ids(array($task_id));
        $subtasks = array();
        foreach ($rows as $row){
            $subtasks[] = array('id' => (int) $row['id'], 'done' => (int) $row['done']);
        }
        $counts = \Model\SubTask::count_by_task_ids(array($task_id));
        $total_count = empty($counts) ? 0 : (int) $counts[0]['total_count'];
        $done_count  = empty($counts) ? 0 : (int) $counts[0]['done_count'];

        return array(
            'id'          => (int) $task_id,
            'done'        => (int) $task['done'],
            'done_count'  => $done_count,
            'total_count' => $total_count,
            'subtasks'    => $subtasks,
        );
    }
}