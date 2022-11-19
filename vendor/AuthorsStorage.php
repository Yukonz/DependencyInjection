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
}

interface AuthorDataSource
{
    public function get_author_by_id(int $author_id) : PostAuthor;
    public function get_author_posts(int $author_id) : array;
    public function list_authors() : array;
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

        $author_data->commentaries = $this->get_author_commentaries($author_id);

        return new PostAuthor($author_data);
    }

    public function get_author_posts(int $author_id) : array
    {
        return $this->db->wpdb->get_col("SELECT DISTINCT post_id
                                         FROM pot_editors
                                         WHERE user_id = {$author_id}");
    }

    private function get_author_commentaries(int $author_id) : array
    {
        return $this->db->wpdb->get_col("SELECT comment_id
                                         FROM author_commentaries
                                         WHERE author_id = {$author_id}");
    }
}