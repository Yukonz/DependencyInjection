<?php

namespace Blog;

class Post implements IPost
{
    private int $post_id;
    private string $post_date;
    private string $post_title;
    private string $post_content;
    private array $post_editors;
    private array $post_commentaries;

    public function __construct(object $post_data)
    {
        $this->post_id = $post_data->id;
        $this->post_date = $post_data->post_date;
        $this->post_title = $post_data->post_title;
        $this->post_content = $post_data->post_content;
        $this->post_editors = $post_data->post_editors;
        $this->post_commentaries = $post_data->post_commentaries;
    }

    public function get_post_id() : int
    {
        return $this->post_id;
    }

    public function get_post_date() : string
    {
        return $this->post_date;
    }

    public function get_post_title() : string
    {
        return $this->post_title;
    }

    public function get_post_content() : string
    {
        return $this->post_content;
    }

    public function get_post_editors() : array
    {
        return $this->post_editors;
    }

    public function get_post_commentaries() : array
    {
        return $this->post_commentaries;
    }
}
