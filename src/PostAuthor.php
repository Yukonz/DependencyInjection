<?php

namespace Blog;

class PostAuthor implements IPostAuthor
{
    private int $author_id;
    private string $author_name;
    private string $author_email;
    private string $date_registered;
    private array $commentaries;

    public function __construct(object $author_data)
    {
        $this->author_id = $author_data->id;
        $this->author_name = $author_data->user_name;
        $this->author_email = $author_data->user_email;
        $this->date_registered = $author_data->date_registered;
        $this->commentaries = $author_data->commentaries;
    }

    public function get_author_id() : int
    {
        return $this->author_id;
    }

    public function get_author_name() : string
    {
        return $this->author_name;
    }

    public function get_author_email() : string
    {
        return $this->author_email;
    }

    public function get_author_date_registered() : string
    {
        return $this->date_registered;
    }

    public function get_author_commentaries() : array
    {
        return $this->commentaries;
    }
}