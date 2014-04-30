<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Login extends CI_Controller {

    private $_logged = false;
    private $_error = 'Неверное сочетание имени пользователя и пароля';
    private $_ajax = false;      // called by ajax
    private $_show_func;

    public function __construct() {
        parent::__construct();
        $this->load->library('auth');
        $this->load->helper('form');
        $this->load->model('user_model');
        $this->load->model('cams_model');



        /*
         * ajax: show form on the top of the page,
         * otherwise show a whole page
         */
        if ($this->input->is_ajax_request()) {
            $this->_ajax = true;
            $this->_show_func = '_showPart';
        } else {
            $this->_show_func = '_showDesign';
        }

        if ($this->session->flashdata('error')) {
            $this->_ajax = false;
            $this->_show_func = '_showDesign';
        }


        // user is already logged in
        if ($this->auth->loggedin()) {
            $this->_logged = true;
        }
    }

    public function _remap() {

        if ($this->_logged) {
            $this->_login();
        } else {
            $this->index();
        }
    }

    private function _login() {
        // if user is logged in, just show him the menu
        $func = $this->_show_func;
        $this->$func();
    }

    public function index() {

        if (sizeof($_POST)) {
            $username = $this->input->post('username') ? $this->input->post('username', true) : false;
            $password = $this->input->post('password') ? $this->input->post('password', true) : false;
            $remember = $this->input->post('remember') ? TRUE : FALSE;


            // get user from database
            $this->load->model('user_model');
            $user = $this->user_model->get('username', $username);
            if ($user) {
                // compare passwords
                if ($this->user_model->check_password($password, $user['password'])) {
                    // mark user as logged in
                    $this->auth->login($user['id'], $remember);
                    $this->_logged = true;
                    redirect('webcam');
                } else {
                    $this->session->set_flashdata('error', $this->_error);
                    $this->_logged = false;
                    $this->_ajax = false;
                }
            } else {
                $this->session->set_flashdata('error', $this->_error);
                $this->_ajax = false;
                $this->logged = false;
            }
        }


        $func = $this->_show_func;
        $this->$func();
    }

    private function _showPart() {
        if ($this->_logged) {
            $this->load->view('parts/logged');
        } else {
            $this->load->view('parts/loginform');
        }
    }

    private function _showDesign() {
        $data['logged'] = false;
        
        // cams list
        
        $data['error'] = $this->session->flashdata('error');
        if ($this->_logged) {
            $data['fio'] = $this->user_model->get_fio($this->auth->userid());
            $data['title'] = 'Добро пожаловать!';
            $data['content'] = 'first';
            $uid = $this->auth->userid();
            if ($this->user_model->isadmin($uid)) {
                $data['adminmenu'] = 'admin/menu';
                $data['cams'] = $this->cams_model->get_list();
            } else {
                $data['cams'] = $this->user_model->get_cam_list($uid);
            }
        } else {
            $data['title'] = 'Зарегистрируйтесь для просмотра сайта';
            $data['content'] = 'loginform';
            $user = $this->user_model->get_anonymous_user();
            if (sizeof($user)) {
                $uid = $user['id'];
            } else {
                $uid = 0;
            }
            $data['cams'] = $this->user_model->get_cam_list($uid);
        }
        $data['left'] = 'parts/common-left';
        $this->load->view('template', $data);
    }

}

/* End of file login.php */
/* Location: ./application/controllers/login.php */