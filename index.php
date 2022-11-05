<?php

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';

require_once 'Db.php';
require_once 'Post.php';
require_once 'PostAuthor.php';
require_once 'Commentary.php';
require_once 'PostsStorage.php';
require_once 'AuthorsStorage.php';
require_once 'CommentariesStorage.php';
require_once 'PostsController.php';
require_once 'AuthorsController.php';
require_once 'CommentariesController.php';
require_once 'Container.php';

$post_id = 123456;

try {
    $container = new \Blog\Container();
    $posts_controller = $container->get(\Blog\PostsController::class);
    $authors_controller = $container->get(\Blog\AuthorsController::class);
    $commentaries_controller = $container->get(\Blog\CommentariesController::class);

    $post = $posts_controller->get_blog_record($post_id);
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
    die();
}

get_header();

?>

<div class="post-wrapper">
    <div class="post-authors">
        <h4>Post Authors:</h4>
        <ul>
            <?php foreach ($post->get_post_editors() as $author_id => $role) { ?>
                <li>
                    <?= $role; ?>: <?= $authors_controller->get_author_details($author_id)->get_author_name(); ?>
                </li>
            <?php } ?>
        </ul>
    </div>
    <div class="post-container">
        <div class="post-title">
            <?= $post->get_post_title(); ?>
        </div>
        <div class="post-content">
            <?= $post->get_post_content(); ?>
        </div>
    </div>
    <div class="post-comments">
        <h4>Post Commentaries:</h4>
        <div class="commentary">
            <?php foreach ($post->get_post_commentaries() as $commentary_id) {
                $commentary = $commentaries_controller->get_commentary_details($commentary_id); ?>
                <div>
                    <span>
                        <?= $authors_controller->get_author_details($commentary->get_commentary_author())->get_author_name(); ?>
                    </span>
                    <span>
                        <?= $commentary->get_commentary_date(); ?>
                    </span>
                    <span>
                        <?= $commentary->get_commentary_rating(); ?>
                    </span>
                </div>
                <hr>
                <p>
                    <?= $commentary->get_commentary_content(); ?>
                </p>
                <hr>
            <?php } ?>
        </div>
    </div>
</div>

<?php

get_footer();

