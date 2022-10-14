<?php

namespace Blog;

class PostsStorage
{
    private Db $db;

    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    public function get_post_by_id(int $record_id) : string
    {
        return (string)$this->db->wpdb->get_var("SELECT post_title 
                                                 FROM {$this->db->wpdb->prefix}posts 
                                                 WHERE ID = {$record_id}");
    }

    public function get_recent_post_by_author_id(int $author_id) : string
    {
        return (string)$this->db->wpdb->get_var("SELECT post_title 
                                                 FROM {$this->db->wpdb->prefix}posts 
                                                 WHERE post_author = {$author_id} 
                                                 LIMIT 1");
    }
}