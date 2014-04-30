<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * This is an EXAMPLE user model, edit to match your implementation
 * OR use the adapter model for easy integration with an existing model
 */
class News_model extends CI_Model {

    // database table name
    var $table = 'news';

    /**
     * Add a camera
     * 
     * @param array camera
     * @return int id
     */
    public function insert($data) {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /**
     * Update a cam
     * 
     * @param int id
     * @param array data
     * @return int id
     */
    public function update($id, $data) {
        $this->db->where('id', $id)->update($this->table, $data);
        return $id;
    }

    /**
     * Delete a cam
     * 
     * @param int value
     * 
     */
    public function delete($id) {
        echo "here i am";
        $this->db->where('id', $id)->delete($this->table);
    }

    /**
     * Retrieve a news, one news
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
     * @return array news
     */
    public function get_list($limit = 0) {
        if ($limit) {
            return $this->db->order_by("added","desc")->limit($limit)->get($this->table)->result_array();
        } else {
            return $this->db->order_by("added","desc")->get($this->table)->result_array();
        }
    }

    public function add($data) {
        $data['added'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

}