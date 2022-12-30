<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
require_once 'vendor/autoload.php';

try {
    if (isset($_POST['find_posts'])) {
        $container = new \Blog\Container();
        $posts_controller = $container->get(\Blog\PostsController::class);
        $authors_controller = $container->get(\Blog\AuthorsController::class);

        $found_posts = $posts_controller->list_posts($_POST['search_string'], $_POST['search_criteria'], $_POST['date_from'], $_POST['date_to']);
    } else {
        $found_posts = [];
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
    die();
}

get_header();

?>

<div class="author-wrapper">
    <div class="search_form">
        <form method="post">
            <input type="search" name="search_string" value="" placeholder="Find posts..." >
            <select name="search_criteria">
                <option value="post">Post content</option>
                <option value="author">Post author</option>
            </select>
            <br>
            <input type="date" name="date_from" value="" placeholder="Date from...">
            <input type="date" name="date_to" value="" placeholder="Date to...">
            <br>
            <button type="submit" name="find_posts" value="1">Search</button>
        </form>
    </div>
    <?php foreach ($found_posts as $post_id) {
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