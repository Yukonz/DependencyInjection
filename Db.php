<?php

namespace Blog;

class Db
{
    public wpdb $wpdb;

    public function __construct()
    {
        $this->wpdb = new wpdb('username', 'password', 'database', 'localhost');
    }
}