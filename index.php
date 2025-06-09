<?php
get_header();
if (have_posts()) :
    while (have_posts()) :
        the_post();
        flex_content_loop();
    endwhile;
endif;
get_footer();
