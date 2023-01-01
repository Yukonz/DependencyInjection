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

    public function get_author_posts(int $author_id) : PostAuthor
    {
        $author_posts = $this->authors_storage->get_author_posts($author_id);

        if (!$author_posts) {
            throw new \Exception('Posts not found');
        }

        return $author_posts;
    }

    public function list_authors(string $search_string = '') : array
    {
        $authors = $this->authors_storage->list_authors($search_string);

        if ($search_string && isset($authors[0]) && !$authors[0]) {
            throw new \Exception('Author was deleted');
        }

        if (!$authors) {
            throw new \Exception('No authors found');
        }

        return $authors;
    }

    public function delete_author(int $author_id)
    {
        $this->authors_storage->delete_author($author_id);
    }
}