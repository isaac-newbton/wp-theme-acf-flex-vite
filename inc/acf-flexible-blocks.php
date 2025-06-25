<?php

if (!defined('FLEX_BLOCKS_PATH')) define('FLEX_BLOCKS_PATH', get_template_directory() . '/template-parts/blocks/');

if (!function_exists('flex_block_id') && function_exists('get_sub_field')) {
    function flex_block_id($field_name = 'block_id')
    {
        if ($block_id = get_sub_field($field_name)) {
            return 'id="' . esc_attr(trim(str_replace([' '], ['_'], $block_id))) . '"';
        } else {
            return '';
        }
    }
}

if (!function_exists('flex_block_adjacent_attr')) {
    function flex_block_adjacent_attr($args)
    {
        $html = '';
        if (is_array($args)) {
            if (isset($args['prev']) && is_string($args['prev']) && !empty($args['prev'])) {
                $html .= ' data-prevlayout="' . esc_attr($args['prev']) . '"';
            }
            if (isset($args['next']) && is_string($args['next']) && !empty($args['next'])) {
                $html .= ' data-nextlayout="' . esc_attr($args['next']) . '"';
            }
        }
        return $html;
    }
}

if (function_exists('have_rows')) {
    function flex_content_loop($sections = ['banner', 'content'])
    {
        if (!is_array($sections) || empty($sections)) return false;
        $content_list = [];
        $content_index = 0;
        foreach ($sections as $section) {
            if (($layouts = get_field($section)) && is_array($layouts) && !empty($layouts)) {
                foreach ($layouts as $layout) {
                    if (is_array($layout) && isset($layout['acf_fc_layout'])) {
                        $content_list[] = $layout['acf_fc_layout'];
                    }
                }
            }
        }
        foreach ($sections as $section) {
            if (have_rows($section)) {
                while (have_rows($section)) {
                    the_row();
                    $layout = get_row_layout();
                    if (file_exists(FLEX_BLOCKS_PATH . "$layout.php")) {
                        if (isset($content_log['layout_counter'][$layout])) {
                            $content_log['layout_counter'][$layout]++;
                        } else {
                            $content_log['layout_counter'][$layout] = 1;
                        }
                        if (isset($section_log['layout_counter'][$layout])) {
                            $section_log['layout_counter'][$layout]++;
                        } else {
                            $section_log['layout_counter'][$layout] = 1;
                        }
                        $args = [
                            'layout' => $layout,
                            'next' => isset($content_list[$content_index + 1]) ? $content_list[$content_index + 1] : false,
                            'prev' => ($content_index > 0) ? $content_list[$content_index - 1] : false,
                        ];
                        get_template_part("template-parts/blocks/$layout", null, $args);
                        $previous_layout = $layout;
                    } else {
                        echo "<!--File not found for $layout (" . FLEX_BLOCKS_PATH . "$layout.php)-->";
                    }
                    $content_index++;
                }
                $content_log['sections'][] = $section_log;
            }
        }
    }
}
