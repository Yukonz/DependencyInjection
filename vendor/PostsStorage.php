<?php

namespace Blog;

class PostsStorage
{
    private PostDataSource $posts_source;

    public function __construct(PostDataSource $posts_source)
    {
        $this->posts_source = $posts_source;
    }

    public function get_post_by_id(int $post_id) : Post
    {
        return $this->posts_source->get_post_by_id($post_id);
    }

    public function list_posts() : array
    {
        return $this->posts_source->list_posts();
    }
}

interface PostDataSource
{
    public function list_posts() : array;
    public function get_post_by_id(int $post_id) : Post;
}

class PostDataSourceAPI implements PostDataSource
{
    public function list_posts() : array
    {
    }

    public function get_post_by_id(int $post_id) : Post
    {
    }
}

class PostDataSourceMySQL implements PostDataSource
{
    private Db $db;

    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    public function get_post_by_id(int $post_id) : Post
    {
        $post_data = $this->db->wpdb->get_var("SELECT id, post_date, post_title, post_content
                                               FROM {$this->db->wpdb->prefix}posts 
                                               WHERE ID = {$post_id}");

        $post_data->post_editors = $this->get_post_editors($post_id);
        $post_data->post_commentaries = $this->get_post_commentaries($post_id);

        return new Post($post_data);
    }

    public function list_posts() : array
    {
        return $this->db->wpdb->get_col("SELECT id
                                         FROM {$this->db->wpdb->prefix}posts");
    }

    private function get_post_editors(int $post_id) : array
    {
        $post_authors = $this->db->wpdb->get_results("SELECT ur.role_title, pe.user_id
                                                      FROM {$this->db->wpdb->prefix}post_editors AS pe
                                                      JOIN {$this->db->wpdb->prefix}user_roles AS ur
                                                      ON pe.role_id = ur.id
                                                      WHERE pe.post_id = {$post_id}");

        $post_authors_arr = [];

        foreach ($post_authors as $author) {
            $post_authors_arr[$author->user_id] = $author->role_title;
        }

        return $post_authors_arr;
    }

    private function get_post_commentaries(int $post_id) : array
    {
        return $this->db->wpdb->get_col("SELECT comment_id
                                         FROM {$this->db->wpdb->prefix}post_commentaries
                                         WHERE post_id = {$post_id}");
    }
}