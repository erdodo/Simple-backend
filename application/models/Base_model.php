<?php

class Base_model extends CI_Model
{
    
    public function __construct()
    {
        parent::__construct();
        
    }

    public function show($tableName,object $config)
    {
        $where = $config->filters??[];
        $like = $config->likes??[];
        $order = $config->sorts??[];
        
        foreach ($where as $key => $value) {
            $this->db->where($key, $value);
        }
        foreach ($like as $key => $value) {
            $this->db->like($key, $value);
        }
        foreach ($order as $key => $value) {
            $this->db->order_by($key, $value);
        }
        

        return $this->db
            ->get($tableName)->row();
    }
    
    public function list($tableName,object $config)
    {

        $where = $config->filters;
        $like = $config->likes;
        $order = $config->sorts;
        $limit = $config->limit;
        $page = $config->page;
        
        foreach ($where as $key => $value) {
            $this->db->where($key, $value);
        }
        foreach ($like as $key => $value) {
            $this->db->like($key, $value);
        }
        foreach ($order as $key => $value) {
            $this->db->order_by($key, $value);
        }
        

        return $this->db
            ->limit($limit, $limit * ($page - 1))
            ->get($tableName)->result();
    }
    public function count($tableName,object $config)
    {
        $where = $config->filters;
        $like = $config->likes;
        $order = $config->sorts;
        
        foreach ($where as $key => $value) {
            $this->db->where($key, $value);
        }
        foreach ($like as $key => $value) {
            $this->db->like($key, $value);
        }
        foreach ($order as $key => $value) {
            $this->db->order_by($key, $value);
        }
        

        return $this->db
            ->get($tableName)->num_rows();
    }
    public function add($tableName,$data = array())
    {

        
        return ($this->db->insert($tableName, $data));

        $this->db->close();
    }
    public function update($tableName,$data = array(),$config=[])
    {
        return $this->db->where($config->filters)->update($tableName, $data);
    }
    public function delete($tableName,$config=[])
    {
        return $this->db->where($config->filters)->delete($tableName);
    }
    public function query($query="")
    {
        return $this->db->query($query)->row();
    }
}
