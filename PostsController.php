<?php

namespace Blog;

class PostsController
{
    private PostsStorage $posts_storage;

    public function __construct(PostsStorage $posts_storage)
    {
        $this->posts_storage = $posts_storage;
    }

    public function get_blog_record(int $record_id = 0, int $author_id = 0) : string
    {
        if ($record_id) {
            $post_title = $this->posts_storage->get_post_by_id($record_id);
        } elseif ($author_id) {
            $post_title = $this->posts_storage->get_recent_post_by_author_id($author_id);
        }

        if (empty($post_title)) {
            throw new Exception('Post not found');
        }

        return $post_title;
    }
}