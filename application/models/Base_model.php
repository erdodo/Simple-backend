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
        

        return $this->db->get($tableName)->row();
    }
    
    public function list($tableName,object $config)
    {

        $filters = $config->filters ?? [];
        $like = $config->likes ?? [];
        $order = $config->sorts ?? [];
        $limit = $config->limit ?? 50;
        $page = $config->page ?? 1;
        
        
        foreach ($filters as $key => $value) {
            $this->db->where($key, $value);
        }
        foreach ($like as $key => $value) {
            $this->db->like($key, $value);
        }
        foreach ($order as $key => $value) {
            $this->db->order_by($key, $value);
        }
        
        

        $data =  $this->db
            ->limit($limit, $limit * ($page - 1))
            ->get($tableName);
        if($data) return $data->result();
    }
    public function count($tableName,object $config)
    {
        $where = $config->filters ?? [];
        $like = $config->likes ?? [];
        $order = $config->sorts ?? [];
        
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

        $state = $this->db->insert($tableName, $data);
        return $state; 

        
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
    public function set_query($query="")
    {
        
        return $this->db->query($query);
    }
    public function phpmyadmin_query($query)
    {
        $phpmyadmin = $this->load->database('phpmyadmin', TRUE); // the TRUE paramater tells CI that you'd like to return the database object.

        return $phpmyadmin->query($query);
    }
}
