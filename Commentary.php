<?php

namespace Blog;

class Commentary
{
    public int $commentary_id;
    public int $author_id;

    private string $commentary_date;
    private string $commentary_content;
    private int $commentary_rating = 0;

    public function __construct(object $commentary_date)
    {
        $this->commentary_id = $commentary_date->id;
        $this->author_id = $commentary_date->comment_author;
        $this->commentary_content = $commentary_date->comment_content;
        $this->commentary_date = $commentary_date->comment_date;
        $this->commentary_rating = $commentary_date->comment_rating;
    }

    public function get_commentary_date() : string
    {
        return $this->commentary_date;
    }

    public function get_commentary_author() : int
    {
        return $this->author_id;
    }

    public function get_commentary_content() : string
    {
        return $this->commentary_content;
    }

    public function get_commentary_rating() : string
    {
        return $this->commentary_rating;
    }
}
