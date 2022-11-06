<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
require_once 'vendor/autoload.php';

$author_id = (int)$_GET['author_id'];

try {
    $container = new \Blog\Container();
    $authors_controller = $container->get(\Blog\AuthorsController::class);
    $commentaries_controller = $container->get(\Blog\CommentariesController::class);

    $author = $authors_controller->get_author_details($author_id);
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
    die();
}

get_header();

?>

<div class="author-wrapper">
    <div class="author-details">
        <h4>Author Details:</h4>
        <table>
            <tr>
                <td>Name:</td>
                <td><?= $author->get_author_name(); ?></td>
            </tr>
            <tr>
                <td>Email:</td>
                <td><?= $author->get_author_email(); ?></td>
            </tr>
            <tr>
                <td>Date Registered:</td>
                <td><?= $author->get_author_date_registered(); ?></td>
            </tr>
        </table>
    </div>
    <div class="post-comments">
        <h4>Author Commentaries:</h4>
        <div class="commentary">
            <?php foreach ($author->get_author_commentaries() as $commentary_id) {
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