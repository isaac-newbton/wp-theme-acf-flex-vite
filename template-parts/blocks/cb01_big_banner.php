<?php
$headline = get_sub_field('headline');
?>

<section class="cb01-big-banner" <?= flex_block_id() ?> <?= flex_block_adjacent_attr($args) ?>>
    <h1><?= $headline ?></h1>
</section>