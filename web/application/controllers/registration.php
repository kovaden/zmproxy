<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Registration extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // load the model
        $this->load->model('user_model');
        $this->load->helper('form_helper');
        $this->load->model('cams_model');
        $this->load->model('news_model');
    }

    public function index() {

//        $data['registration'] = true;

        $data['error'] = array();
        $data['message'] = '';
        if (sizeof($_POST)) {
            $user = array();

            $user['username'] = $this->input->post('username') ? $this->input->post('username', true) : false;
            $user['name'] = $this->input->post('name') ? $this->input->post('name', true) : false;
            $user['lastname'] = $this->input->post('lastname') ? $this->input->post('lastname', true) : false;
            $user['password'] = $this->input->post('password') ? $this->input->post('password', true) : false;
            $user['confirm_password'] = $this->input->post('confirm_password') ? $this->input->post('confirm_password', true) : false;
            $user['email'] = $this->input->post('email') ? $this->input->post('email', true) : false;
            $user['info'] = $this->input->post('info') ? $this->input->post('info', true) : false;


            // not enough data: 
            if (!$user['username'] && !$user['password'] && !$user['confirm_password'] && !$user['email']) {
                $data['error']['all'] = 'Заполните ВСЕ поля, пожалуйста';
                $data['user'] = $user;
                $this->_showpage($data);
                return;
            }

            // if we have such user already:
            $user_exists = $this->user_model->get('username', $user['username']);
            if ($user_exists) {
                $data['error']['username'] = 'Уже есть такой вариант';
            }

            $email_exists = $this->user_model->get('email', $user['email']);
            if ($email_exists) {
                $data['error']['email'] = 'Уже есть такой вариант';
            }


            // password is too short: 
            if (strlen($user['password']) < 5) {
                $data['message'] = 'Парольчик-то у вас коротковат. Хотя мы предупреждали!';
            }

            // passwords are different:
            if ($user['password'] != $user['confirm_password']) {
                $data['error']['confirm_password'] = 'Попробуйте ввести два одинаковых пароля. Это менее секретно, но поможет.';
            }

            if (sizeof($data['error'])) {
                // shit happens
                $data['user'] = $user;

                $this->_showpage($data);
                return;
            } else {
                // rock'n'roll!
                unset($user['confirm_password']);
                $id = $this->user_model->insert($user);
                if ($id) {
                    $this->session->set_flashdata('success', $id);
                    if ($data['message']) {
                        $this->session->set_flashdata('message', $data['message']);
                    }
                    redirect(site_url('registration/success'), 'refresh');
                } else {
                    $data['error']['all'] = 'Что-то всё совсем не так как надо, вас не запомнили. Попробуйте ещё разочек, но попозже: у нас тут какая-то ошибка.';
                    $this->_showpage();
                    return;
                }
            }
        } else {
            if ($this->session->flashdata('error')) {
                $data['error']['all'] = $this->session->flashdata('error');
            }
            $this->_showpage($data);
        }
    }

    public function success() {
        if (!((int) $this->session->flashdata('success'))) {
            $this->session->set_flashdata('error', 'Сначала заполните эту вот скучную форму');
            redirect(site_url('registration'), 'refresh');
        } else {
            $data['content'] = 'registration_success';
            if (strlen($this->session->flashdata('message'))) {
                $data['message'] = $this->session->flashdata('message');
            } else {
                $data['message'] = 'Мы вас запомнили!';
            }
            $this->_showpage($data);
        }
    }

    private function _showpage($data = array()) {

        $data['handler'] = 'registration';
        $data['logged'] = false;
        $data['news_list'] = $this->news_model->get_list(5);
        $data['left'] = 'parts/firstpage-left';
        if (isset($data['error'])) {
            $data['error']['all'] = $this->session->flashdata('error');
        }
        $data['title'] = 'Регистрация';
        if (!isset($data['content'])) {
            $data['content'] = 'registration';
        }
        $this->load->view('template', $data);
    }

}

/* End of file install.php */
/* Location: ./application/controllers/install.php */