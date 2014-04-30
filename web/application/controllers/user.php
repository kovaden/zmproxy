<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author irishka
 */
class User extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->library('auth');
        $this->load->model('user_model');

        if (!$this->auth->loggedin()) {
            redirect('login');
        }
    }

    public function profile() {
        $id = $this->auth->userid();
        $uid = $this->input->get('id');
        if (!$uid) {
            $uid = $id;
        }
//        echo $id;
//        echo $uid;
        if (!$this->user_model->isadmin($id) && $id != $uid) {
            $this->session->set_flashdata('error', 'Посмотрите сведения о себе');
            $uid = $id;
        }
        $data['title'] = 'Данные пользователя';
        $userdata = $this->user_model->get($uid);
        if (sizeof($userdata)) {
            $data['user'] = $userdata;
            $data['content'] = 'admin/profile';
        } else {
            $data['user'] = array();
            $this->session->set_flashdata('error', 'Ошиблись пользователем');
            $data['content'] = 'admin/err';
        }

        $this->_showDesign($data);
    }

 
 

    private function _showDesign($data) {
        $data['logged'] = 'parts/logged';
        $data['error'] = $this->session->flashdata('error');
//        $data['fio'] = $this->user_model->get_fio($this->auth->userid());
        $data['uid'] = $this->auth->userid();
        $data['fields'] = array(
            'username' => 'Имя пользователя',
            'lastname' => 'ФИО',
            'firstname' => '',
        );

        $this->load->view('template', $data);
    }

}

?>
