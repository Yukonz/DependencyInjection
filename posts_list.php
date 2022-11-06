<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
require_once 'vendor/autoload.php';

try {
    $container = new \Blog\Container();
    $posts_controller = $container->get(\Blog\PostsController::class);
    $authors_controller = $container->get(\Blog\AuthorsController::class);

    $all_posts = $posts_controller->list_posts();
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
    die();
}

get_header();

?>

<div class="author-wrapper">
    <?php foreach ($all_posts as $post_id) {
        $post = $posts_controller->get_blog_record($post_id); ?>
        <div class="author-details">
            <h4>
                <a href="/post_page.php?post_id=<?= $post_id; ?>">
                    <?= $post->get_post_title(); ?>
                </a>
            </h4>
            <table>
                <tr>
                    <td>Date:</td>
                    <td><?= $post->get_post_date(); ?></td>
                </tr>
                <tr>
                    <td>Authors:</td>
                    <td>
                        <?php foreach ($post->get_post_editors() as $author_id => $role) { ?>
                            <a href="/author_page.php?author_id=<?= $author_id ?>">
                                <?= $role; ?>: <?= $authors_controller->get_author_details($author_id)->get_author_name(); ?>
                            </a>
                            <br>
                        <?php } ?>
                    </td>
                </tr>
            </table>
            <p class="post-preview">
                <?= wp_trim_words($post->get_post_content(), 20); ?>
            </p>
        </div>
    <?php } ?>
</div>

<?php

get_footer();