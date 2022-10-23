<?php

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';

require_once 'Db.php';
require_once 'Post.php';
require_once 'PostsStorage.php';
require_once 'PostsController.php';
require_once 'Container.php';

try {
    $container = new \Blog\Container();
    $posts_controller = $container->get(\Blog\PostsController::class);

    echo $posts_controller->get_blog_record(319799);
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}