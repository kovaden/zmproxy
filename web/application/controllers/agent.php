<?php

/*
 * * Controller for XMLRPC based agent operations.
 * *  contained all code for database access
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Agent extends CI_Controller {
    /*
     * rpc values
     */

    //private $pass = "LkbyysqCnhfiysqRfvthysqGfhjkm";

    private $queuesz = 100;
    private $interval = 'None';
    private $type = 'zm'; // next time will be file/zm , when i'll not code for food
    private $id = 0;
    private $name = '';
    private $url = '';

    /*
     * service variables
     */
    private $username = '';
    private $password = '';
    private $logged = false;
    private $write_log = true;

    public function __construct() {
        parent::__construct();

	$this->username = $this->config->item('admin_user');
	$this->password = $this->config->item('admin_pass');

        $this->load->library('xmlrpc');
        $this->load->library('xmlrpcs');
        $this->load->library('auth');

        $this->load->model('cams_model');
        $this->load->model('user_model');

        if (!$this->logged) {
            $this->logged = $this->login();
        }
    }

    private function login() {
        $user = $this->user_model->get('username', $this->username);
        if ($user && $this->user_model->check_password($this->password, $user['password'])) {
            return true;
        } else {
            return false;
        }
    }

    public function index() {

        $config['functions']['ReadCameraList'] = array('function' => 'agent.ReadCameraList');
        $config['object'] = $this;  

        $this->log_message('debug', 'Agent controller started. Initializing xmlrpc...');
        $this->xmlrpcs->initialize($config);
        $this->log_message('debug', 'Continue');
        $this->xmlrpcs->serve();
        $this->log_message('debug', 'Agent controller finished.');
    }

    function ReadCameraList($request) {
        $this->log_message('debug', 'ReadCameraList');
        if ($this->logged) {
            $parameters = $request->output_parameters();
            $this->log_message('debug', 'Start GetServerSettings()');
            $sql = "SELECT id, name, url, camtype, queuesz, user, pass, down FROM cameras";
            $arr = $this->db->query($sql);
            $this->log_message('debug', print_r($arr, TRUE));
            $res = $arr->result_array();
            $this->log_message('debug', print_r($res, TRUE));
            $ret = array();
            $ret = array();
            if ($arr->num_rows() > 0) {
                foreach ($arr->result_array() as $key => $var) {
                    $va = array();
                    $va['id'] = $var['id'];
                    $va['name'] = $var['name'];
                    $va['url'] = $var['url'];
                    $va['type'] = $var['camtype'];
                    $va['interval'] = 'None';
                    $va['queuesz'] = $var['queuesz'];
                    $va['user'] = $var['user'];
                    $va['passwd'] = $var['pass'];
		    $va['down'] = $var['down'];
                    $ret[] = array($va, 'struct');
                }
            } else {
                $this->log_message('debug', 'Empty result');
            }

            $response = array($ret, 'array');
            $this->log_message('debug', 'ReadCameraList response: ' . print_r($response, TRUE));
            return $this->xmlrpc->send_response($response);
        } else {
            $this->log_message('debug', 'Unsuccessfull login');
            return false;
        }
    }

    private function log_message($level, $message) {
        if ($this->write_log) {
            $this->load->helper('file');
            $data = date('d-m-Y H:i:s') . "\n" . $message . "\n\n";
            write_file('rpc.log', $data, 'a');
        }
    }

   
}

?>