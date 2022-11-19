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

    public function list_posts_view() : array
    {
        return $this->posts_source->list_posts_view();
    }
}

interface PostDataSource
{
    public function list_posts() : array;
    public function get_post_by_id(int $post_id) : Post;
    public function list_posts_view() : array;
}

class PostDataSourceAPI implements PostDataSource
{
    public function list_posts() : array
    {
    }

    public function get_post_by_id(int $post_id) : Post
    {
    }

    public function list_posts_view() : array
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
        return $this->db->wpdb->get_col("SELECT id FROM {$this->db->wpdb->prefix}posts");
    }

    public function list_posts_view() : array
    {
        if (!$this->check_list_posts_view_exists()) {
            $this->create_list_posts_view();
        }

        return $this->db->wpdb->get_results("SELECT * FROM posts_list");
    }

    private function check_list_posts_view_exists() : string
    {
        return (string)$this->db->wpdb->get_var("SHOW TABLES LIKE 'posts_list'");
    }

    private function create_list_posts_view()
    {
        $this->db->wpdb->query("CREATE OR REPLACE VIEW posts_list AS 
                                    SELECT p.id, 
                                           p.post_title AS title,                                         
                                           COUNT(DISTINCT pe.user_id) - 1 AS editors,
                                           COUNT(DISTINCT pc.comment_id) AS comments,
                                           (SELECT u.user_name 
                                            FROM {$this->db->wpdb->prefix}users AS u
                                            JOIN {$this->db->wpdb->prefix}post_editors AS pe
                                            ON u.id = pe.user_id
                                            JOIN {$this->db->wpdb->prefix}user_roles AS ur
                                            ON ur.id = pe.role_id
                                            WHERE pe.post_id = p.id
                                            AND ur.role_title = 'author') AS author
                                    FROM {$this->db->wpdb->prefix}posts AS p
                                    JOIN {$this->db->wpdb->prefix}post_editors AS pe
                                    ON p.id = pe.post_id
                                    JOIN {$this->db->wpdb->prefix}users AS u
                                    ON pe.user_id = u.id
                                    JOIN {$this->db->wpdb->prefix}user_roles AS ur
                                    ON pe.role_id = ur.id
                                    JOIN {$this->db->wpdb->prefix}post_commentaries AS pc
                                    ON p.id = pc.post_id
                                    GROUP BY p.id 
                                    ORDER BY p.post_date DESC");
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