<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
require_once 'vendor/autoload.php';

try {
    $container = new \Blog\Container();
    $posts_controller = $container->get(\Blog\PostsController::class);
    $authors_controller = $container->get(\Blog\AuthorsController::class);

    if (isset($_POST['delete_author']) && $_POST['delete_author']) {
        $authors_controller->delete_author((int)$_POST['delete_author']);
    }

    $all_authors = $authors_controller->list_authors();
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
    die();
}

get_header();

?>

<div class="author-wrapper">
    <form method="post">
        <?php foreach ($all_authors as $author_id) {
            $author = $authors_controller->get_author_details($author_id);
            $author_posts = $authors_controller->get_author_posts($author_id); ?>
            <div class="author-details">
                <h4>
                    <a href="/author_page?author_id=<?= $author_id; ?>">
                        <?= $author->get_author_name(); ?>
                    </a>
                </h4>
                <table>
                    <tr>
                        <td>Email:</td>
                        <td><?= $author->get_author_email(); ?></td>
                    </tr>
                    <tr>
                        <td>Date Registered:</td>
                        <td><?= $author->get_author_date_registered(); ?></td>
                    </tr>
                    <?php if (current_user_can('delete_users')) { ?>
                        <tr>
                            <td>Action:</td>
                            <td>
                                <button type="submit" name="delete_author" value="<?= $author_id; ?>">
                                    Delete Author
                                </button>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
            <div class="author-posts">
                <?php foreach ($author_posts as $post_id) { ?>
                    <a href="/post_page.php?post_id=<?= $post_id ?>">
                        <?= $posts_controller->get_blog_record($post_id)->get_post_title(); ?>
                    </a>
                <?php } ?>
            </div>
        <?php } ?>
    </form>
</div>

<?php

get_footer();