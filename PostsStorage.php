<?php

namespace Blog;

class PostsStorage
{
    private PostDataSource $posts_source;

    public function __construct(PostDataSource $posts_source)
    {
        $this->posts_source = $posts_source;
    }

    public function get_post_by_id(int $post_id)
    {
        return $this->posts_source->get_post_by_id($post_id);
    }

    public function get_recent_post_by_author_id($author_id)
    {
        return $this->posts_source->get_recent_post_by_author_id($author_id);
    }
}

interface PostDataSource
{
    public function get_post_by_id(int $post_id);
    public function get_recent_post_by_author_id(int $author_id);
}

class PostDataSourceMySQL implements PostDataSource
{
    public function get_post_by_id(int $post_id)
    {
        //Make API Call
    }

    public function get_recent_post_by_author_id(int $author_id)
    {
        //Make API Call
    }
}

class PostDataSourceAPI implements PostDataSource
{
    private Db $db;

    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    public function get_post_by_id(int $post_id) : string
    {
        return (string)$this->db->wpdb->get_var("SELECT post_title 
                                                 FROM {$this->db->wpdb->prefix}posts 
                                                 WHERE ID = {$post_id}");
    }

    public function get_recent_post_by_author_id(int $author_id) : string
    {
        return (string)$this->db->wpdb->get_var("SELECT post_title 
                                                 FROM {$this->db->wpdb->prefix}posts 
                                                 WHERE post_author = {$author_id} 
                                                 LIMIT 1");
    }
}