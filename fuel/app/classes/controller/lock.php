<?php
class Controller_Lock extends Controller_Template{
    public function action_index(){
        if (Session::get('authenticated')){
            Response::redirect('task/index');
        }
        $view = View::forge('lock/index', array('error' => Session::get_flash('lock_error') ?: ''));
        $this->template->content = $view;
    }

    public function action_unlock(){
        Config::load('lock', true);

        $password = (string) Input::post('password');
        $hash = Config::get('lock.password_hash');

        if ($hash === null or ! password_verify($password, $hash)){
            Session::set_flash('lock_error', '合言葉が違います');
            Response::redirect('lock/index');
        }

        Session::set('authenticated', true);
        Response::redirect('task/index');
    }

    public function action_logout(){
        if (Input::method() !== 'POST'){
            Response::redirect('lock/index');
        }

        Session::destroy();
        Response::redirect('lock/index');
    }
}