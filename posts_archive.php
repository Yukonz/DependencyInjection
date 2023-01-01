<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
require_once 'vendor/autoload.php';

try {
    $container = new \Blog\Container();
    $posts_controller = $container->get(\Blog\PostsController::class);

    $archived_posts = $posts_controller->list_archived_posts($_POST['search_string'], $_POST['search_criteria'], $_POST['date_from'], $_POST['date_to']);
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

    <?php foreach ($archived_posts as $post) { ?>
        <div class="author-details">
            <h4><?= $post->post_title; ?></h4>
            <table>
                <tr>
                    <td>Date Created:</td>
                    <td><?= $post->post_date; ?></td>
                </tr>
                <tr>
                    <td>Date Archived:</td>
                    <td><?= $post->date_archived; ?></td>
                </tr>
                <tr>
                    <td>Authors:</td>
                    <td>
                        <?php foreach (json_decode($post->post_authors) as $author) { ?>
                            <a href="/author_page.php?author_id=<?= $author->user_id ?>">
                                <?= $author->role; ?>: <?= $author->name; ?>
                            </a>
                            <br>
                        <?php } ?>
                    </td>
                </tr>
            </table>
            <p class="post-preview">
                <?= wp_trim_words($post->psot_content, 20); ?>
            </p>
        </div>
    <?php } ?>
</div>

<?php

get_footer();