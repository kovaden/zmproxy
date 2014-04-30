<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Logout extends CI_Controller {

    public function index() {
        // in case you did not autoload the library
        $this->load->library('auth');
        $this->load->model('user_model');

        $this->auth->logout();

        $this->load->helper('form');

        if ($this->input->is_ajax_request()) {
            $this->load->view('parts/loginform');
        } else {
            $data['logged'] = false;

            $user = $this->user_model->get_anonymous_user();
            if (sizeof($user)) {
                $uid = (int) $user['id'];
            } else {
                $uid = 0;
            }
            $data['cams'] = $this->user_model->get_cam_list($uid);

            $data['left'] = 'parts/common-left';
            $data['error'] = $this->session->flashdata('error');
            $data['title'] = 'Зарегистрируйтесь для просмотра сайта';
            $data['content'] = 'loginform';
            //$data['left'] = 'parts/common-left';
            $this->load->view('template', $data);
        }
    }

}

/* End of file logout.php */
/* Location: ./application/controllers/logout.php */