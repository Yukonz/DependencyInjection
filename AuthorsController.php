<?php

namespace Blog;

class AuthorsController
{
    private AuthorsStorage $authors_storage;

    public function __construct(AuthorsStorage $authors_storage)
    {
        $this->authors_storage = $authors_storage;
    }

    public function get_author_details(int $author_id) : PostAuthor
    {
        $post_author = $this->authors_storage->get_author_by_id($author_id);

        if (!$post_author->get_author_id()) {
            throw new \Exception('Author not found');
        }

        return $post_author;
    }
}