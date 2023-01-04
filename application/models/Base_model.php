<?php

class Base_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function show($tableName,$where = array())
    {
        return $this->db->where($where)->get($tableName)->row();
    }
    public function list($tableName,$where = array(), $order = "", $limit = 100, $page = 1, $like = array())
    {

        foreach ($where as $key => $value) {
            $this->db->where($key, $value);
        }
        foreach ($like as $key => $value) {
            $this->db->like($key, $value);
        }

        return $this->db
            ->order_by($order)
            ->limit($limit, $limit * ($page - 1))
            ->get($tableName)->result();
    }
    /*public function add($data = array(), $tbname)
    {

        return $this->db->insert($this->tableName, $data);
        $this->db->close();
    }

    public function update($where = array(), $data = array())
    {
        return $this->db->where($where)->update($this->tableName, $data);
    }

    public function delete($where = array())
    {
        return $this->db->where($where)->delete($this->tableName);
    }
    public function columns($table)
    {
        $columns = [];
        $information_schema = $this->load->database('information_schema', TRUE);
        $query = "SELECT TABLE_NAME,COLUMN_NAME, IS_NULLABLE,DATA_TYPE,CHARACTER_MAXIMUM_LENGTH,PRIVILEGES,COLUMN_DEFAULT,COLUMN_COMMENT ,COLUMN_KEY
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_NAME = '$table'
            AND TABLE_SCHEMA = 'Simple'";

        $result = $information_schema->query($query)->result() or null;


        foreach ($result as $row) {
            $where = [
                'TABLE_NAME' => $table,
                "COLUMN_NAME" => $row->COLUMN_NAME
            ];
            $other_table = $information_schema->where($where)->get('KEY_COLUMN_USAGE')->row();
            $columns[$row->COLUMN_NAME] = [
                "name" => $row->COLUMN_NAME,
                "display" => $row->COLUMN_COMMENT,
                "type" => $row->DATA_TYPE == 'tinytext' ? 'file' : $row->DATA_TYPE,
                "length" => $row->CHARACTER_MAXIMUM_LENGTH,
                "null" => $row->IS_NULLABLE,
                "default" => $row->COLUMN_DEFAULT,
                "key" => $row->COLUMN_KEY,
                "table_name" => $other_table->REFERENCED_TABLE_NAME ?? '',
                "table_column" => explode('_', $other_table->CONSTRAINT_NAME ?? '')[1] ?? ''
            ];
        }
        return $columns;
    }
    public function first()
    {
        return $this->db->get($this->tableName)->row();
    }
    public function last()
    {
        return $this->db->order_by("id", "desc")->get($this->tableName)->row();
    }
    public function count($where = array(), $like = array())
    {
        foreach ($where as $key => $value) {
            $this->db->where($key, $value);
        }
        foreach ($like as $key => $value) {
            $this->db->like($key, $value);
        }


        return $this->db->get($this->tableName)->num_rows();
    }*/
}
