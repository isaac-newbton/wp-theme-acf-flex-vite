<?php
$headline = get_sub_field('headline');
$cta_link = get_sub_field('cta_link');
?>

<section class="cb03-cta" <?= flex_block_id() ?> <?= flex_block_adjacent_attr($args) ?>>
    <h3><?= $headline ?></h3>
    <a href="<?= esc_attr($cta_link['url']) ?>"><?= $cta_link['title'] ?></a>
</section>