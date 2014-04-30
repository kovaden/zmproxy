<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * This is an EXAMPLE user model, edit to match your implementation
 * OR use the adapter model for easy integration with an existing model
 */
class User_model extends CI_Model {

    // database table name
    var $table = 'users';

    /**
     * Add a user, password will be hashed
     * 
     * @param array user
     * @return int id
     */
    public function insert($user) {
        // need the library for hashing the password
        $this->load->library("auth");

        $user['password'] = $this->hash($user['password']);
        $user['registered'] = time();

        $this->db->insert($this->table, $user);
        return $this->db->insert_id();
    }

    /**
     * Update a user, password will be hashed
     * 
     * @param int id
     * @param array user
     * @return int id
     */
    public function update($id, $user) {
        // prevent overwriting with a blank password
        if (isset($user['password']) && $user['password']) {
            $user['password'] = $this->hash($user['password']);
        } else {
            unset($user['password']);
        }

        $this->db->where('id', $id)->update($this->table, $user);
        return $id;
    }

    /**
     * Delete a user
     * 
     * @param string where
     * @param int value
     * @param string identification field
     */
    public function delete($where, $value = FALSE) {
        if (!$value) {
            $value = $where;
            $where = 'id';
        }

        $this->db->where($where, $value)->delete($this->table);
    }

    /**
     * Retrieve a user
     * 
     * @param string where
     * @param int value
     * @param string identification field
     */
    public function get($where, $value = FALSE) {
        if (!$value) {
            $value = $where;
            $where = 'id';
        }

        $user = $this->db->where($where, $value)->get($this->table)->row_array();
        return $user;
    }

    /**
     * Get a list of users with pagination options
     * 
     * @param int limit
     * @param int offset
     * @return array users
     */
    public function get_list($limit = FALSE, $offset = FALSE) {
        if ($limit) {
            return $this->db->order_by("username")->limit($limit, $offset)->get($this->table)->result_array();
        } else {
            return $this->db->order_by("username")->get($this->table)->result_array();
        }
    }

    /**
     * Check if a user exists
     * 
     * @param string where
     * @param int value
     * @param string identification field
     */
    public function exists($where, $value = FALSE) {
        if (!$value) {
            $value = $where;
            $where = 'id';
        }

        return $this->db->where($where, $value)->count_all_results($this->table);
    }

    /**
     * Password hashing function
     * 
     * @param string $password
     */
    public function hash($password) {
        $this->load->library('PasswordHash', array('iteration_count_log2' => 8, 'portable_hashes' => FALSE));

        // hash password
        return $this->passwordhash->HashPassword($password);
    }

    /**
     * Compare user input password to stored hash
     * 
     * @param string $password
     * @param string $stored_hash
     */
    public function check_password($password, $stored_hash) {
        $this->load->library('PasswordHash', array('iteration_count_log2' => 8, 'portable_hashes' => FALSE));

        // check password
        return $this->passwordhash->CheckPassword($password, $stored_hash);
    }

    public function isadmin($id) {
        return $this->db->where('id', $id)->where('is_admin', 1)->count_all_results($this->table);
    }

    public function get_short_fio($id) {
        $user = $this->get($id);
        return sprintf('%s. %s', mb_substr($user['name'],0,1), $user['lastname']);
    }

    public function get_fio($id) {
        $user = $this->get($id);
        return sprintf('%s %s', strlen($user['lastname'])?$user['lastname']:'Никтошкин', strlen($user['name'])?$user['name']:'Никитка');
    }

    public function get_anonymous_user() {
        return $this->db->where('username', 'anonymous')->get($this->table)->row_array();
    }

    public function get_cam_list($uid) {
        $sql = 'select id,name,descr,url,down from cameras c where id in 
            (SELECT gc.id_cam FROM groupcams gc 
            left JOIN usergroups ug on ug.group_id = gc.group_id 
            WHERE ug.user_id =' . $uid . ')';
//        echo $sql;
        return $this->db->query($sql)->result_array();
    }

    public function get_camera($uid, $cam_id) {
        $sql = 'select id,name,descr,url,down from cameras c where id=' . $cam_id . ' and id in 
            (SELECT gc.id_cam FROM groupcams gc 
            left JOIN usergroups ug on ug.group_id = gc.group_id 
            WHERE ug.user_id =' . $uid . ')';
        return $this->db->query($sql)->row_array();
    }


    /*
     * returns a list of camera packs connected to users
     */

    public function get_users_packs() {
        $ret = array();
        $sql = 'SELECT g.name, g.id, ug.user_id FROM groups g 
            JOIN usergroups ug
            ON ug.group_id = g.id
            order by id';
        $query = $this->db->query($sql);
        foreach ($query->result_array() as $row) {
            $ret[$row['id']][$row['user_id']] = $row['name'];
        }
        return $ret;
    }




}