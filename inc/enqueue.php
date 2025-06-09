<?php
add_action('wp_enqueue_scripts', function () {
    $manifestPath = get_theme_file_path('dist/.vite/manifest.json');
    if (file_exists($manifestPath)) {
        $manifest = json_decode(file_get_contents($manifestPath), true);
        if (isset($manifest['src/scripts/main.js'])) {
            wp_enqueue_script('mytheme', get_theme_file_uri('dist/' . $manifest['src/scripts/main.js']['file']));
            // Enqueue the CSS file
            wp_enqueue_style('mytheme', get_theme_file_uri('dist/' . $manifest['src/styles/main.scss']['file']));
        }
    }
});
