<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Admin extends CI_Controller {

    private $is_admin = 0;
    private $uid = 0;

    public function __construct() {
        parent::__construct();

        // in case you did not autoload the library
        $this->load->library('auth');
        $this->load->model('user_model');
        $this->load->model('cams_model');
        $this->load->model('news_model');
        $this->load->helper('form_helper');

        $this->uid = $this->auth->userid();
        $this->is_admin = $this->user_model->isadmin($this->uid);
        if (!$this->is_admin) {
            redirect('webcam');
        }
    }

    /*
     * users list with camera packets
     */

    public function index() {


        if (!$this->auth->loggedin() || !$this->is_admin) {
            redirect('login');
        }

        if (($this->input->get('id'))) {
            $data['new_id'] = (int) $this->input->get('id', true);
        }
        // get user from database
        $data['title'] = 'Список пользователей';

        // users list
        $users_list = $this->user_model->get_list();
        $data['list'] = $users_list;
        $data['fields'] = array(
            'id' => 'userid',
            'username' => 'Имя пользователя',
            'email' => 'email',
            'is_admin' => 'Админ ли?',
            'name' => 'ФИО',
            'lastname' => '',
            'info' => 'Дополнительная информация',
            0 => 'Пакеты',
        );


        $camgroups = $this->cams_model->get_cam_groups();
        $usercams = $this->user_model->get_users_packs();


        foreach ($users_list as $user) {
            $id = $user['id'];
            foreach ($camgroups as $cam) {
                $cams[$id][] = array('label' => $cam['name'], 'id' => $cam['id'], 'checked' => isset($usercams[$cam['id']][$user['id']]));
            }
        }

        $data['packets'] = $cams;
        $data['content'] = 'admin/userlist';

        $an_user = $this->user_model->get_anonymous_user();
        if (!sizeof($an_user)) {
            $data['message'] = 'Не существует анонимного пользователя (anonymous), камеры незарегистрированным юзерам показываться не будут. Срочно заводите!';
        }

        $this->_showDesign($data);
    }

    /*
     * add-remove camera packets to user
     */

    public function packets() {
        $data_all = $this->input->post(NULL, TRUE);
        $user_id = (int) $data_all['uid'];
        unset($data_all['uid']);
        foreach ($data_all as $k => $v) {
            $key = (int) substr($k, 4);
            $cams[] = $key;
        }
        // delete all packet records for user
        $this->cams_model->delete_packs($user_id);
        // add new records if we have some
        if (isset($cams) && sizeof($cams)) {
            $res = $this->cams_model->add_packs($user_id, $cams);
            //echo "<pre>";        print_r($cams);        echo "</pre>";
        }
        echo "$res записей изменено";
    }

    public function newuser() {
        $data['error'] = array();
        $data['message'] = '';
        $data['content'] = 'registration';
        $data['title'] = 'Регистрация нового пользователя';
        $data['handler'] = 'admin/newuser';
        if (!sizeof($_POST)) {
            $this->_showDesign($data);
        } else {
            $user = array();

            $user['username'] = $this->input->post('username') ? $this->input->post('username', true) : false;
            $user['name'] = $this->input->post('name') ? $this->input->post('name', true) : false;
            $user['lastname'] = $this->input->post('lastname') ? $this->input->post('lastname', true) : false;
            $user['password'] = $this->input->post('password') ? $this->input->post('password', true) : false;
            $user['confirm_password'] = $this->input->post('confirm_password') ? $this->input->post('confirm_password', true) : false;
            $user['email'] = $this->input->post('email') ? $this->input->post('email', true) : false;
            $user['info'] = $this->input->post('info') ? $this->input->post('info', true) : false;


            // not enough data: 
            if (!$user['username'] && !$user['password'] && !$user['confirm_password'] && !$user['email'] && !$user['name'] && !$user['lastname']) {
                $data['error']['all'] = 'Заполните ВСЕ поля, пожалуйста';
                $data['user'] = $user;
                $this->_showDesign($data);
                return;
            }

            // if we have such user already:
            $user_exists = $this->user_model->get('username', $user['username']);
            if ($user_exists) {
                $data['error']['username'] = 'Уже есть такой пользователь';
            }

            $email_exists = $this->user_model->get('email', $user['email']);
            if ($email_exists) {
                $data['error']['email'] = 'Уже есть такой почтовый адрес';
            }

            // passwords are different:
            if ($user['password'] != $user['confirm_password']) {
                $data['error']['confirm_password'] = 'Пароли различаются';
            }

            if (sizeof($data['error'])) {
                //print_r ($data['error']);
                // shit happens
                $data['user'] = $user;
                $this->_showDesign($data);
                return;
            } else {
                unset($user['confirm_password']);
                $id = $this->user_model->insert($user);
                if ($id) {
                    $this->session->set_flashdata('success', $id);
                    $data['message'] = 'Добавлен новый пользователь, в таблице он выделен цветом.';
                    redirect(site_url('admin?id=' . $id), 'refresh');
                } else {
                    $data['error']['all'] = 'Произошла ошибка, пользователь не добавлен.';
                    $this->_showpage();
                    return;
                }
            }
        }
    }

    /*
     * list of cameras, just a list.
     */

    public function camlist() {
        if (!$this->auth->loggedin() || !$this->is_admin) {
            redirect('login');
        }

        $this->load->model('cams_model');
        $data['title'] = 'Группы камер';

        // cams list
        $cams_list = $this->cams_model->get_list();
        $data['list'] = $cams_list;
        $data['fields'] = array(
            'id' => 'id',
            'name' => 'Название камеры',
            'descr' => 'Описание',
            'url' => 'адрес',
            'user' => 'ZM авторизация: user/pass',
            'pass' => '',
            'down' => 'выкл'
        );
        $data['content'] = 'admin/camlist';

        $this->_showDesign($data);
    }

    public function newslist($message = '') {
        if (!$this->auth->loggedin() || !$this->is_admin) {
            redirect('login');
        }

        $this->load->model('cams_model');
        $data['title'] = 'Группы камер';

        // cams list
        $data['list'] = $this->news_model->get_list();
        $data['fields'] = array(
            'id' => 'id',
            'header' => 'Заголовок',
            'short' => 'Кратко',
            'full' => 'Подробно',
        );
        if ($this->session->flashdata('message')) {
            $data['message'] = $this->session->flashdata('message');
        }
        if ($message) {
            $data['message'] = $message;
        }
        $data['content'] = 'admin/newslist';

        $this->_showDesign($data);
    }

    /*
     * groups of cameras
     */

    public function camgroups() {


        if (!$this->auth->loggedin() || !$this->is_admin) {
            redirect('login');
        }

        $this->load->model('cams_model');
        $data['title'] = 'Группы камер';


        $camgroups = $this->cams_model->get_cam_groups();
        $data['list'] = $camgroups;
        $data['fields'] = array(
            'id' => 'id группы',
            'name' => 'Название ',
            'descr' => 'Описание',
            0 => 'Список камер'
        );



        $cams_in_groups = $this->cams_model->get_cams_packs();
        $cams_list = $this->cams_model->get_list();

        foreach ($camgroups as $group) {
            $id = $group['id'];
            foreach ($cams_list as $cam) {
                $cams[$id][] = array('label' => $cam['name'], 'id' => $cam['id'], 'checked' => isset($cams_in_groups[$cam['id']][$group['id']]));
            }
        }

        $data['packets'] = $cams;
        $data['content'] = 'admin/camgroups';

        $this->_showDesign($data);
    }

    /*
     * add-remove camera to packet
     */

    public function campack() {
//        print_r ($_POST);
        $data_all = $this->input->post(NULL, TRUE);
        $group_id = (int) $data_all['gid'];
        unset($data_all['gid']);
        foreach ($data_all as $k => $v) {
            $key = (int) substr($k, 4);
            $cams[] = $key;
        }
//        print_r ($cams);
        // delete all packet records for user
        $this->cams_model->delete_cams_from_pack($group_id);
        // add new records if we have some
        if (isset($cams) && sizeof($cams)) {
            $res = $this->cams_model->add_cams_2pack($group_id, $cams);
            //echo "<pre>";        print_r($cams);        echo "</pre>";
        }
        echo "$res записей изменено";
    }

    public function update() {
        $data_all = $this->input->post(NULL, TRUE);
        //print_r($data_all);
        $arr = explode('_', $data_all['id']);
        $arr_size = sizeof($arr);
        if ($arr_size == 2) {
            $id = $arr[1];
            $field = $arr[0];
        } else {
            $id = $arr[$arr_size-1];
            unset($arr[$arr_size-1]);
            $field = implode('_', $arr);
        }

        $value = $data_all['value'];
        $data[$field] = $value;
        switch ($data_all['list']) {
            case 'userlist' :
                $tblname = 'users';
                $this->user_model->update($id, $data);
                break;
            case 'camgroupslist':
                $tblname = 'groups';
                $this->cams_model->update($id, $data, $tblname);
                break;
            case 'camlist' :
                if ($field == 'down') {
                    //'Yes' or 'No' checkbox 
                    if ($value == 'Yes') {
                        $data[$field] = 1;
                    } else {
                        $data[$field] = 0;
                    }
//                    print_r ($data);
                }
                $this->cams_model->update($id, $data);
                break;
            case 'newslist' :
                $this->news_model->update($id, $data);
                break;
        }
        echo $data_all['value'];

        //print_r($data_all);
    }

    public function delcam() {
        $id = (int) $this->uri->segment(3);
        if ($id) {
            $rec_id = $this->cams_model->delete($id);
            $this->session->set_flashdata('message', 'Камера удалена');
        } else {
            $this->session->set_flashdata('message', 'Нет такой камеры');
        }
        redirect('admin/camlist');
    }

    public function deluser() {
        $id = (int) $this->uri->segment(3);
        if ($id) {
            $rec_id = $this->user_model->delete($id);
            $this->session->set_flashdata('message', 'Пристрелили');
        } else {
            $this->session->set_flashdata('message', 'Еще не родился');
        }
        redirect('admin');
    }

    public function delcamgroup() {
        $id = (int) $this->uri->segment(3);
        if ($id) {
            $rec_id = $this->cams_model->delete($id, 'groups');
            $this->session->set_flashdata('message', 'Пакет камер удален');
        } else {
            $this->session->set_flashdata('message', 'Нет такого пакета');
        }
        redirect('admin/camgroups');
    }

    public function addnews() {
        $data_all = $this->input->post(NULL, TRUE);
        if (!$data_all['header'] && !$data_all['short'] && !$data_all['full']) {
            $this->session->set_flashdata('message', 'Пустую новость не буду добавлять');
        } else {
            $data['header'] = $data_all['header'];
            $data['short'] = $data_all['short'];
            $data['full'] = $data_all['full'];
            $id = $this->news_model->add($data);
            if ($id) {
                $this->session->set_flashdata('message', 'Новость № ' . $id . ' добавлена');
            } else {
                $this->session->set_flashdata('message', 'Не получилось добавить новость');
            }
        }
        redirect('admin/newslist');
    }

    public function addcam() {
        $data_all = $this->input->post(NULL, TRUE);
        if (!$data_all['name'] && !$data_all['url']) {
            $this->session->set_flashdata('message', 'Название и адрес должны быть заполнены');
        } else {
            $data['name'] = $data_all['name'];
            $data['url'] = $data_all['url'];
            $data['descr'] = $data_all['descr'];
            $data['user'] = $data_all['user'];
            $data['pass'] = $data_all['pass'];
            $id = $this->cams_model->insert($data);
            if ($id) {
                $this->session->set_flashdata('message', 'Камера № ' . $id . ' добавлена');
            } else {
                $this->session->set_flashdata('message', 'Не получилось добавить камеру');
            }
        }
        redirect('admin/camlist');
    }

    public function addgroup() {
        $data_all = $this->input->post(NULL, TRUE);
        if (!$data_all['name']) {
            $this->session->set_flashdata('message', 'Название группы введите пожалуйста');
        } else {
            $data['name'] = $data_all['name'];
            $data['descr'] = $data_all['descr'];
            $id = $this->cams_model->insert($data, 'groups');
            if ($id) {
                $this->session->set_flashdata('message', 'Группа камер № ' . $id . ' добавлена');
            } else {
                $this->session->set_flashdata('message', 'Не получилось добавить пакет');
            }
        }
        redirect('admin/camgroups');
    }

    public function delnews() {
        $data_all = $this->input->post(NULL, TRUE);
        $id = (int) $data_all['id'];
        // delete this news
        $this->news_model->delete($id);
        $this->session->set_flashdata('message', 'Запись удалена');
        redirect('admin/newslist');
    }

    private function _showDesign($data) {
        $data['logged'] = 'parts/logged';
        if (!isset($data['error'])) {
            $data['error'] = $this->session->flashdata('error');
        }
        $data['fio'] = $this->user_model->get_fio($this->uid);

        if ($this->is_admin) {
            $data['adminmenu'] = 'admin/menu';
        }

//        $data['fio'] = $this->user_model->get_fio($this->auth->userid());
        $data['uid'] = $this->auth->userid();

        if ($this->session->flashdata('message')) {
            $data['message'] = $this->session->flashdata('message');
        }

        $this->load->view('template', $data);
    }

}

/* End of file admin.php */
/* Location: ./application/controllers/admin.php */