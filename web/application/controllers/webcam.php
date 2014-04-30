<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Webcam extends CI_Controller {

    private $_logged = false;
    private $uid = 0;
    private $is_admin = false;

    public function __construct() {
        parent::__construct();

        $this->load->library('auth');
        $this->load->model('user_model');
        $this->load->model('cams_model');
        $this->load->model('news_model');

        $this->load->helper('form');

        if ($this->auth->loggedin()) {
            $this->_logged = true;
            $this->uid = $this->auth->userid();
            $this->is_admin = $this->user_model->isadmin($this->uid);
        } else {
            $user = $this->user_model->get_anonymous_user();
            if (sizeof($user)) {
                $this->uid = $user['id'];
            } else {
                $this->uid = 0;
            }
            $this->is_admin = false;
        }

        
    }

//    public function _remap($method) {
//
//        $this->$method();
//    }

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

    public function index() {
        $uid = $this->uid;

        // get cam list for user
        if ($this->is_admin) {
            $cam_list = $this->cams_model->get_list();
        } else {
            $cam_list = $this->user_model->get_cam_list($uid);
        }
        if (sizeof($cam_list) == 0) {
          
            $data['user_error'] = 'Список камер не назначен';
        }
        $data['cam_list'] = $cam_list;

        $css_additional = '';
        foreach ($cam_list as $cam) {
            $css_additional .= sprintf('#cam%s {background: url(%s/cam%s.jpg) 0 0 no-repeat}%s', $cam['id'], base_url('img/thumbnails'), $cam['id'], "\n");
        }

        $data['css'] = $css_additional;
        $data['title'] = 'Первая страница';
        $data['content'] = 'first';
        $data['cam_addr'] = $this->config->item('proxy_host');

        if (!$this->_logged) {
            $data['news_list'] = $this->news_model->get_list(5);
            $data['left'] = 'parts/firstpage-left';
        }

        header("Refresh: 600"); // 10 min
        $this->showpage($data);
    }

    public function camera() {
        $cam_id = (int) $this->uri->segment(3);
        $data = array('content' => 'err', 'title' => 'error!!');
        if ($cam_id) {
            if ($this->user_model->isadmin($this->uid)) {
                $cam = $this->cams_model->get($cam_id);
            } else {
                $cam = $this->user_model->get_camera($this->uid, $cam_id);
            }
            if (sizeof($cam)) {
                $data['title'] = $cam['name'];
                $data['descr'] = $cam['descr'];
                $data['cam_id'] = $cam_id;
                $data['content'] = 'webcam';
            }
            if (isset($cam['down']) && $cam['down']) {
                $data['cam_url'] = base_url('img/down.jpg');
            } else {
                $data['cam_url'] = $this->config->item('proxy_host') . $cam_id;
            }
        }


        if ($this->user_model->isadmin($this->uid)) {
            $data['cams'] = $this->cams_model->get_list();
        } else {
            $data['cams'] = $this->user_model->get_cam_list($this->uid);
        }
        $data['left'] = 'parts/common-left';
        header("Refresh: 600"); // 10 min
        $this->showpage($data);
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */