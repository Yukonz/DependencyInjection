<?php

namespace Blog;

class CommentariesController
{
    private CommentariesStorage $commentaries_storage;

    public function __construct(CommentariesStorage $commentaries_storage)
    {
        $this->commentaries_storage = $commentaries_storage;
    }

    public function get_commentary_details(int $commentary_id) : Commentary
    {
        $commentary = $this->commentaries_storage->get_commentary_by_id($commentary_id);

        if (!$commentary->get_commentary_id()) {
            throw new \Exception('Commentary not found');
        }

        return $commentary;
    }
}