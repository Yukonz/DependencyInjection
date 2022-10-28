<?php

namespace Blog;

class Post implements IPost
{
    public int $post_id;

    public string $post_date;
    public string $post_title;
    public string $post_content;

    public function get_all_post_data()
    {
    }

    public function get_post_title()
    {
    }

    public function get_post_body()
    {
    }

    public function get_post_date()
    {
    }
}
