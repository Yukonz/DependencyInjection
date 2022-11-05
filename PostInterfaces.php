<?php

namespace Blog;

interface IPost
{
    public function get_post_id();
    public function get_post_date();
    public function get_post_title();
    public function get_post_content();
}

interface IPostActions
{
    public function print_post_to_docx();
    public function print_post_to_pdf();
}

interface IPostAuthor
{
    public function get_author_id();
    public function get_author_name();
    public function get_author_email();
    public function get_author_date_registered();
}