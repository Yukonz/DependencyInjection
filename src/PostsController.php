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

    public function list_posts(string $search_string = '', string $search_criteria = 'post', string $date_from = '', string $date_to = '') : array
    {
        $posts = $this->posts_storage->list_posts($search_string, $search_criteria, $date_from, $date_to);

        if (empty($posts)) {
            throw new \Exception('No posts found');
        }

        return $posts;
    }

    public function list_archived_posts(string $search_string = '', string $search_criteria = 'post', string $date_from = '', string $date_to = '') : array
    {
        $archived_posts = $this->posts_storage->list_archived_posts($search_string, $search_criteria, $date_from, $date_to);

        if (!$archived_posts) {
            throw new \Exception('No archived posts found');
        }

        return $archived_posts;
    }

    public function list_posts_view() : array
    {
        $posts = $this->posts_storage->list_posts_view();

        if (!$posts) {
            throw new \Exception('No posts found');
        }

        return $posts;
    }
}