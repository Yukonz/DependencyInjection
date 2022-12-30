<?php

namespace tests;

use Exception;
use PHPUnit\Framework\TestCase;

class PostsControllerTest extends TestCase
{
    public object $container;
    public object $posts_controller;

    public function setUp() : void
    {
        $this->container = new \Blog\Container();
        $this->posts_controller = $this->container->get(\Blog\PostsController::class);
    }

    public function test_list_posts_search_by_post_success()
    {
        $search_text = 'Existed Post';
        $search_criteria = 'post';
        
        $test_result = $this->posts_controller->list_posts($search_text, $search_criteria);

        $this->assertTrue(is_array($test_result) && !empty($test_result));
    }

    public function test_list_posts_search_by_post_exception()
    {
        $search_text = 'Non-Existed Post';
        $search_criteria = 'post';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No posts found');
        
        $this->posts_controller->list_posts($search_text, $search_criteria);
    }

    public function test_list_posts_search_by_author_success()
    {
        $search_text = 'author@email.com';
        $search_criteria = 'author';

        $test_result = $this->posts_controller->list_posts($search_text, $search_criteria);

        $this->assertTrue(is_array($test_result) && !empty($test_result));
    }

    public function test_list_posts_search_by_author_exception()
    {
        $search_text = 'Non-Existed author';
        $search_criteria = 'author';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No posts found');

        $this->posts_controller->list_posts($search_text, $search_criteria);
    }

    public function test_list_posts_search_by_date_success()
    {
        $date_from = '2020-01-01';
        $date_to = '2022-01-01';

        $test_result = $this->posts_controller->list_posts('', 'post', $date_from, $date_to);

        $this->assertTrue(is_array($test_result) && !empty($test_result));
    }

    public function test_list_posts_search_by_date_exception()
    {
        $date_from = '1999-01-01';
        $date_to = '2000-01-01';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No posts found');

        $this->posts_controller->list_posts('', 'post', $date_from, $date_to);
    }
}