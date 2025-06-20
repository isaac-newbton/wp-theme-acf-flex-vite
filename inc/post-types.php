<?php

function add_thumbnails_to_posts()
{
    add_theme_support('post-thumbnails');
}
add_action('init', 'add_thumbnails_to_posts');

function remove_wp_editor_from_pages()
{
    remove_post_type_support('page', 'editor');
}
add_action('admin_init', 'remove_wp_editor_from_pages');
