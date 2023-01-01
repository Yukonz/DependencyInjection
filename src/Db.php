<?php

namespace Blog;

class Db
{
    public $wpdb;

    public function __construct()
    {
        global $wpdb;

        $this->wpdb = $wpdb;
    }
}