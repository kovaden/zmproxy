<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * This is an EXAMPLE user model, edit to match your implementation
 * OR use the adapter model for easy integration with an existing model
 */
class Cams_model extends CI_Model {

    // database table name
    var $table = 'cameras';

    /**
     * Add a camera
     * 
     * @param array camera
     * @return int id
     */
    public function insert($data, $table = '') {
        if (!$table) {
            $table = $this->table;
        }
        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }

    /**
     * Update a cam
     * 
     * @param int id
     * @param array data
     * @return int id
     */
    public function update($id, $data, $tbl = '') {
        if (!$tbl) {
            $tbl = $this->table;
        }
        $this->db->where('id', $id)->update($tbl, $data);
        return $id;
    }

    /**
     * Delete a cam
     * 
     * @param int value
     * 
     */
    public function delete($id, $tbl = '') {
        if (!$tbl) {
            $tbl = $this->table;
        }
        $this->db->where('id', $id)->delete($tbl);
    }

    /**
     * Retrieve a cam
     * 
     * @param int value
     * @param string identification field
     */
    public function get($id) {
        return $this->db->where('id', $id)->get($this->table)->row_array();
    }

    /**
     * Get a list of cams 
     * 
     * @return array cameras
     */
    public function get_list() {
        return $this->db->order_by("name")->get($this->table)->result_array();
    }

    /*
     * returns a list of camera groups
     */

    public function get_cam_groups() {
        return $this->db->order_by("id")->get('groups')->result_array();
    }

    /*
     * returns a list of cameras by groups
     */

    public function get_cams_packs() {
        $ret = array();
        $sql = 'SELECT c.name, c.id, gc.group_id
            FROM cameras c
            JOIN groupcams gc ON gc.id_cam = c.id
            ORDER BY id';
        $query = $this->db->query($sql);
        foreach ($query->result_array() as $row) {
            $ret[$row['id']][$row['group_id']] = $row['name'];
        }
        return $ret;
    }

    /*
     * remove campack from user
     */

    public function delete_packs($uid) {
        $this->db->where('user_id', $uid)->delete('usergroups');
    }

    // delete all cameras from given group id
    public function delete_cams_from_pack($gid) {
        $this->db->where('group_id', $gid)->delete('groupcams');
    }

    /*
     * add campack for user
     */

    public function add_packs($uid, $cams) {
        $data['user_id'] = $uid;
        $recs = 0;
        foreach ($cams as $cam) {
            $data['group_id'] = $cam;
            $this->db->insert('usergroups', $data);
            $recs++;
        }
        return $recs;
    }

    public function add_cams_2pack($gid, $cams) {
        $data['group_id'] = $gid;
        $recs = 0;
        foreach ($cams as $cam) {
            $data['id_cam'] = $cam;
            $this->db->insert('groupcams', $data);
            $recs++;
        }
        return $recs;
    }

}