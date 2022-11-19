<?php

namespace Blog;

class CommentariesStorage
{
    private CommentaryDataSource $commentaries_source;

    public function __construct(CommentaryDataSource $Commentaries_source)
    {
        $this->commentaries_source = $Commentaries_source;
    }

    public function get_commentary_by_id(int $author_id)
    {
        return $this->commentaries_source->get_commentary_by_id($author_id);
    }
}

interface CommentaryDataSource
{
    public function get_commentary_by_id(int $commentary_id);
}

class CommentaryDataSourceMySQL implements CommentaryDataSource
{
    private Db $db;

    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    public function get_commentary_by_id(int $commentary_id) : Commentary
    {
        $commentary_data =  $this->db->wpdb->get_row("SELECT id, comment_date, comment_author, comment_content, comment_rating
                                                      FROM commentaries 
                                                      WHERE id = {$commentary_id}");

        return new Commentary($commentary_data);
    }
}