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

    public function list_posts(string $search_string = '', string $search_criteria = 'post', string $date_from = '', string $date_to = '') : array
    {
        return $this->posts_source->list_posts($search_string, $search_criteria, $date_from, $date_to);
    }

    public function list_posts_view() : array
    {
        return $this->posts_source->list_posts_view();
    }
}

interface PostDataSource
{
    public function list_posts(string $search_string = '', string $search_criteria = 'post', string $date_from = '', string $date_to = '') : array;
    public function get_post_by_id(int $post_id) : Post;
    public function list_posts_view() : array;
}

class PostDataSourceAPI implements PostDataSource
{
    public function list_posts(string $search_string = '', string $search_criteria = 'post', string $date_from = '', string $date_to = '') : array
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
                                               FROM posts 
                                               WHERE ID = {$post_id}");

        $post_data->post_editors = $this->get_post_editors($post_id);
        $post_data->post_commentaries = $this->get_post_commentaries($post_id);

        return new Post($post_data);
    }

    public function list_posts(string $search_string = '', string $search_criteria = 'post', string $date_from = '', string $date_to = '') : array
    {
        $date_filter_str = "";

        if ($date_from) {
            $date_from = date('Y-m-d', strtotime($date_from));
            $date_filter_str .= "AND post_date >= '{$date_from}'";
        }

        if ($date_to) {
            $date_to = date('Y-m-d', strtotime($date_to));
            $date_filter_str .= "AND post_date <= '{$date_to}'";
        }

        $search_string = esc_sql(trim($search_string));

        switch ($search_criteria) {
            case 'author':
                $authors_filter_str = "";
                $authors_join_str = "";

                if ($search_string) {
                    $authors_join_str = "JOIN post_editors AS pe
                                         ON p.id = pe.post_id
                                         JOIN users AS u
                                         ON u.id = pe.user_id";

                    $authors_filter_str = "AND (u.user_name LIKE '%{$search_string}%'
                                           OR u.user_email LIKE '%{$search_string}%')";
                }

                return $this->db->wpdb->get_col("SELECT id FROM posts AS p
                                                 {$authors_join_str}
                                                 WHERE 1
                                                 {$date_filter_str}
                                                 {$authors_filter_str}");

            case 'post':
                $posts_filter_str = "";

                if ($search_string) {
                    $posts_filter_str = "AND (post_title LIKE '%{$search_string}%' 
                                         OR post_content LIKE '%{$search_string}%')";
                }

                return $this->db->wpdb->get_col("SELECT id FROM posts
                                                 WHERE 1
                                                 {$date_filter_str}
                                                 {$posts_filter_str}");

            default:
                return [];
        }
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
                                            FROM users AS u
                                            JOIN post_editors AS pe
                                            ON u.id = pe.user_id
                                            JOIN user_roles AS ur
                                            ON ur.id = pe.role_id
                                            WHERE pe.post_id = p.id
                                            AND ur.role_title = 'author') AS author
                                    FROM posts AS p
                                    JOIN post_editors AS pe
                                    ON p.id = pe.post_id
                                    JOIN users AS u
                                    ON pe.user_id = u.id
                                    JOIN user_roles AS ur
                                    ON pe.role_id = ur.id
                                    JOIN post_commentaries AS pc
                                    ON p.id = pc.post_id
                                    GROUP BY p.id 
                                    ORDER BY p.post_date DESC");
    }

    private function get_post_editors(int $post_id) : array
    {
        $post_authors = $this->db->wpdb->get_results("SELECT ur.role_title, pe.user_id
                                                      FROM post_editors AS pe
                                                      JOIN user_roles AS ur
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
                                         FROM post_commentaries
                                         WHERE post_id = {$post_id}");
    }

    public function delete_post(int $post_id)
    {
        if (!$this->check_post_archiving_trigger_exists()) {
            $this->create_post_archiving_trigger();
        }

        $this->db->wpdb->query("DELETE p, pe, pc, c
                                FROM posts AS p
                                JOIN post_editors AS pe
                                ON p.id = pe.post_id
                                JOIN post_commentaries AS pc
                                ON p.id = pc.post_id
                                JOIN commentaries AS c 
                                ON pc.comment_id = c.id
                                WHERE p.id = {$post_id}");
    }

    private function check_post_archiving_trigger_exists() : string
    {
        return (string)$this->db->wpdb->get_var("SHOW TRIGGERS LIKE 'post_archiving'");
    }

    private function create_post_archiving_trigger()
    {
        $this->db->wpdb->query("CREATE TRIGGER post_archiving
                                BEFORE DELETE
                                ON posts FOR EACH ROW
                                INSERT INTO posts_archive (date_archived, post_id, post_date, post_title, post_content, post_authors, post_comments)
                                SELECT NOW(), p.id, p.post_date, p.post_title, p.post_content,
                                (SELECT JSON_ARRAYAGG(ur.role_title)
                                 FROM post_editors AS pe
                                 JOIN user_roles AS ur
                                 ON pe.role_id = ur.id
                                 WHERE pe.post_id = OLD.id
                                 ORDER BY pe.role_id ASC
                                 LIMIT 3),
                                (SELECT JSON_ARRAYAGG(c.comment_content)
                                 FROM commentaries AS c
                                 JOIN post_commentaries AS pc
                                 ON c.id = pc.comment_id
                                 WHERE pc.post_id = OLD.id
                                 ORDER BY c.id DESC
                                 LIMIT 2)
                                FROM posts AS p
                                WHERE id = OLD.id");
    }
}