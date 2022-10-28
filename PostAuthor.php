<?php

namespace Blog;

class PostAuthor implements IPost, IPostAuthor
{
    public int $post_id;
    public int $author_id;

    public string $author_name;
    public string $avatar_url;

    public function get_all_post_data()
    {
    }

    public function get_author_name()
    {
    }

    public function get_author_avatar()
    {
    }
}