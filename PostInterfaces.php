<?php

namespace Blog;

interface IPost
{
    public function get_all_post_data();
}

interface IPostActions
{
    public function print_post_to_docx();
    public function print_post_to_pdf();
}

interface IPostAuthor
{
    public function get_author_name();
    public function get_author_avatar();
}