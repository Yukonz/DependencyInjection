<?php

namespace Blog;

class PostsController
{
    private PostsStorage $posts_storage;

    public function __construct(PostsStorage $posts_storage)
    {
        $this->posts_storage = $posts_storage;
    }

    public function get_blog_record(int $post_id) : Post
    {
        $post = $this->posts_storage->get_post_by_id($post_id);

        if (!$post->get_post_id()) {
            throw new \Exception('Post not found');
        }

        return $post;
    }
}