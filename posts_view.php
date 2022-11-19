<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
require_once 'vendor/autoload.php';

try {
    $container = new \Blog\Container();
    $posts_controller = $container->get(\Blog\PostsController::class);
    $all_posts_view = $posts_controller->list_posts_view();
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
    die();
}

get_header();

?>

<div class="posts-wrapper">
    <table  class="posts-list">
        <thead>
        <tr>
            <th>ID</th>
            <th>Post Title</th>
            <th>Author Name</th>
            <th>Editors Number</th>
            <th>Comments Number</th>
        </tr>
        </thead>
        <tbody>
            <?php foreach ($all_posts_view as $post) { ?>
                <tr>
                    <td><?= $post->id ?></td>
                    <td>
                        <a href="/post_page.php?post_id=<?= $post->id; ?>">
                            <?= $post->title; ?>
                        </a>
                    </td>
                    <td><?= $post->author; ?></td>
                    <td><?= $post->editors; ?></td>
                    <td><?= $post->comments; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php

get_footer();