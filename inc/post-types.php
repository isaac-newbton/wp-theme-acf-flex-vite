<?php

function add_thumbnails_to_posts()
{
    add_theme_support('post-thumbnails');
}
add_action('init', 'add_thumbnails_to_posts');
