<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class news extends CI_Controller {

    private $_logged = false;

    public function __construct() {
        parent::__construct();
        $this->load->library('auth');
        $this->load->model('user_model');
        // load the model
        $this->load->model('news_model');
        $this->load->helper('form_helper');

        if ($this->auth->loggedin()) {
            $this->_logged = true;
            $this->uid = $this->auth->userid();
        } else {
            $user = $this->user_model->get_anonymous_user();
            if (sizeof($user)) {
                $this->uid =  $user['id'];
            } else {
                $this->uid = 0;
            }
        }

        $this->is_admin = $this->user_model->isadmin($this->uid);
    }

    public function index() {
        $data['newslist'] = $this->news_model->get_list();
        $this->load->view('parts/firstpage-left', $data);
    }

    public function show() {
        $id = (int) $this->uri->segment(3);
        $data['news'] = array();
        if ($id) {
            $data['news'] = $this->news_model->get($id);
            $data['title'] = (isset($data['news']['header']) && strlen($data['news']['header'])) ? $data['news']['header'] : 'Истринские новости';
        } 
        if (!sizeof($data['news'])) {
            $data['title'] = $data['news']['header'] = 'Нет такой новости';
        }
            
        
        
        $data['content'] = 'shownews';
        $this->showpage($data);
    }

    private function showpage($data) {
        if ($this->_logged) {
            $data['logged'] = 'parts/logged';
            $data['fio'] = $this->user_model->get_fio($this->uid);
        } else {
            $data['logged'] = 'parts/loginform';
        }
        if ($this->is_admin) {
            $data['adminmenu'] = 'admin/menu';
        }

        $this->load->view('template', $data);
    }

}

/* End of file install.php */
/* Location: ./application/controllers/install.php */