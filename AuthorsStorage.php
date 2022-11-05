<?php

namespace Blog;

class AuthorsStorage
{
    private AuthorDataSource $authors_source;

    public function __construct(AuthorDataSource $authors_source)
    {
        $this->authors_source = $authors_source;
    }

    public function get_author_by_id(int $author_id)
    {
        return $this->authors_source->get_author_by_id($author_id);
    }
}

interface AuthorDataSource
{
    public function get_author_by_id(int $author_id);
}

class AuthorDataSourceMySQL implements AuthorDataSource
{
    private Db $db;

    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    public function get_author_by_id(int $author_id) : PostAuthor
    {
        $author_data =  $this->db->wpdb->get_var("SELECT id, date_registered, user_name, user_email
                                                  FROM {$this->db->wpdb->prefix}users 
                                                  WHERE id = {$author_id}");

        return new PostAuthor($author_data);
    }
}