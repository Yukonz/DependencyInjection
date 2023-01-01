<?php

namespace Blog;

class AuthorsStorage
{
    private AuthorDataSource $authors_source;

    public function __construct(AuthorDataSource $authors_source)
    {
        $this->authors_source = $authors_source;
    }

    public function get_author_by_id(int $author_id) : PostAuthor
    {
        return $this->authors_source->get_author_by_id($author_id);
    }

    public function get_author_posts(int $author_id) : array
    {
        return $this->authors_source->get_author_posts($author_id);
    }

    public function list_authors() : array
    {
        return $this->authors_source->list_authors();
    }

    public function delete_author(int $author_id)
    {
        $this->authors_source->delete_author($author_id);
    }
}

interface AuthorDataSource
{
    public function get_author_by_id(int $author_id) : PostAuthor;
    public function get_author_posts(int $author_id) : array;
    public function list_authors() : array;
    public function delete_author(int $author_id);
}

class AuthorDataSourceMySQL implements AuthorDataSource
{
    private Db $db;

    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    public function list_authors() : array
    {
        return $this->db->wpdb->get_col("SELECT DISTINCT user_id
                                         FROM post_editors");
    }

    public function get_author_by_id(int $author_id) : PostAuthor
    {
        $author_data = $this->db->wpdb->get_row("SELECT id, date_registered, user_name, user_email
                                                 FROM users 
                                                 WHERE id = {$author_id}");

        //check if author's account has been deleted
        if (!$author_data && $this->is_author_have_posts($author_id)) {
            $author_data = new stdClass();
            $author_data->id = $author_id;
            $author_data->user_name = 'Deleted Author';
        }

        $author_data->commentaries = $this->get_author_commentaries($author_id);

        return new PostAuthor($author_data);
    }

    public function get_author_posts(int $author_id) : array
    {
        return $this->db->wpdb->get_col("SELECT DISTINCT post_id
                                         FROM post_editors
                                         WHERE user_id = {$author_id}");
    }

    public function delete_author(int $author_id)
    {
        $this->db->wpdb->query("DELETE users, post_editors, commentaries, post_commentaries, author_commentaries
                                FROM users AS u
                                LEFT JOIN post_editors AS pe
                                ON u.id = pe.user_id
                                LEFT JOIN commentaries AS c
                                ON u.id = c.comment_author
                                LEFT JOIN post_commentaries AS pc
                                ON c.id = pc.comment_id
                                LEFT JOIN author_commentaries AS ac
                                ON c.id = ac.comment_id
                                WHERE u.id = {$author_id}");
    }

    private function get_author_commentaries(int $author_id) : array
    {
        return $this->db->wpdb->get_col("SELECT comment_id
                                         FROM author_commentaries
                                         WHERE author_id = {$author_id}");
    }

    private function is_author_have_posts(int $author_id) : bool
    {
        return (bool)$this->db->wpdb->get_var("SELECT id
                                               FROM post_editors
                                               WHERE user_id = {$author_id}
                                               LIMIT 1");
    }
}