<?php

require_once 'Db.php';
require_once 'Post.php';
require_once 'PostsStorage.php';
require_once 'PostsController.php';

try {
    $container = new \Blog\Container();
    $posts_controller = new \Blog\PostsController($container->get(\Blog\PostsController::class));

    echo $posts_controller->get_blog_record(author_id: 12345);
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}