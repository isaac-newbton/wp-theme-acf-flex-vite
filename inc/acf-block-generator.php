<?php

if(!defined('P10_BLOCK_TEMPLATES_DIR')) {
    define('P10_BLOCK_TEMPLATES_DIR', get_template_directory() . '/template-parts/blocks');
}
if(!defined('P10_BLOCK_STYLES_DIR')) {
    define('P10_BLOCK_STYLES_DIR', get_template_directory() . '/src/styles/blocks');
}
if(!defined('P10_BLOCK_SCRIPTS_DIR')) {
    define('P10_BLOCK_SCRIPTS_DIR', get_template_directory() . '/src/scripts/blocks');
}
if(!defined('P10_THEME_ACF_DIR')) {
    define('P10_THEME_ACF_DIR', get_template_directory() . '/acf-json');
}
if(!defined('P10_BLOCK_SCREENSHOTS_URL')) {
    define('P10_BLOCK_SCREENSHOTS_URL', get_template_directory_uri() . '/src/images/screenshots');
}

function p10_add_block_generator_menu_page()
{
    add_management_page('ACF Block Generator', 'Block Generator', 'install_plugins', 'p10_acf_block_generator', 'p10_block_generator_admin_page');
}
add_action('admin_menu', 'p10_add_block_generator_menu_page');

function p10_block_generator_admin_page()
{
    $is_previewing = ($_SERVER['REQUEST_METHOD'] === 'POST') && ($_POST['submit'] == 'Preview');
    $is_submitting = ($_SERVER['REQUEST_METHOD'] === 'POST') && ($_POST['submit'] == 'Generate');
    $blocks = [];
    $attempted = [];
    if ($is_previewing || $is_submitting) {
        $lines = explode(PHP_EOL, $_POST['blocks']);
        if ($lines && !empty($lines)) {
            foreach ($lines as $line) {
                if ($block = p10_block_generator_make_block_array($line)) {
                    $blocks[] = $block;
                }
            }
        }
        if($is_submitting && !empty($blocks)) {
            $block_groups = [
                'banners' => [],
                'content' => []
            ];
            foreach($blocks as $block) {
                $result = [
                    'block' => $block,
                    'template' => false,
                    'style' => false,
                    'script' => false
                ];
                if(isset($block['assets']['template'])) {
                    if(!file_exists($block['assets']['template'])) {
                        if($f = fopen($block['assets']['template'], 'w')) {
                            $class_string = esc_attr($block['class_name'] . ' ' . $block['class_name_short'] . ' p10-block' . ($block['banner'] ? ' p10-banner-block' : ''));
                            fwrite($f, '<?php' . PHP_EOL . PHP_EOL . '?>' . PHP_EOL . PHP_EOL . "<section class=\"$class_string\" <?=flex_block_id()?> <?=flex_block_adjacent_attr(\$args)?> >" . PHP_EOL . PHP_EOL . '</section>');
                            fclose($f);
                            $result['template'] = file_exists($block['assets']['template']) ? $block['assets']['template'] : false;
                        }
                    }
                }
                if(isset($block['assets']['style'])) {
                    if(!file_exists($block['assets']['style'])) {
                        if($f = fopen($block['assets']['style'], 'w')) {
                            $selector_string = '.' . esc_attr($block['class_name']) . ', .' . esc_attr($block['class_name_short']);
                            fwrite($f, '@use "../general/variables" as *;' . PHP_EOL . PHP_EOL . "$selector_string {" . PHP_EOL . PHP_EOL . '}');
                            fclose($f);
                            $result['style'] = file_exists($block['assets']['style']) ? $block['assets']['style'] : false;
                        }
                    }
                }
                if(isset($block['assets']['script'])) {
                    if(!file_exists($block['assets']['script'])) {
                        if($f = fopen($block['assets']['script'], 'w')) {
                            $block_array_name = "{$block['class_name_short']}Blocks";
                            $selector_string = '.' . esc_attr($block['class_name']) . ', .' . esc_attr($block['class_name_short']);
                            fwrite($f, 'document.addEventListener("DOMContentLoaded", ()=>{' . PHP_EOL . PHP_EOL . "\tconst $block_array_name = document.querySelectorAll('$selector_string');" . PHP_EOL . "\tif(0 < $block_array_name.length) {" . PHP_EOL . PHP_EOL . "\t}" . PHP_EOL . PHP_EOL . '});');
                            fclose($f);
                            $result['script'] = file_exists($block['assets']['script']) ? $block['assets']['script'] : false;
                        }
                    }
                }
                if(isset($block['assets']['acf_group'])) {
                    $acf_json_file = P10_THEME_ACF_DIR . "/{$block['assets']['acf_group']}.json";
                    if(!file_exists($acf_json_file)) {
                        if($f = fopen($acf_json_file, 'w')) {
                            ob_start();
?>{
    "key": "<?=$block['assets']['acf_group']?>",
    "title": "<?=$block['full_name']?>",
    "fields": [
        {
            "key": "<?=uniqid('field_')?>",
            "label": "Settings",
            "name": "",
            "aria-label": "",
            "type": "tab",
            "instructions": "",
            "required": 0,
            "conditional_logic": 0,
            "wrapper": {
                "width": "",
                "class": "",
                "id": ""
            },
            "placement": "left",
            "endpoint": 0,
            "selected": 0
        },
        {
            "key": "<?=uniqid('field_')?>",
            "label": "Block ID",
            "name": "block_id",
            "aria-label": "",
            "type": "text",
            "instructions": "Optional ID for jump linking.",
            "required": 0,
            "conditional_logic": 0,
            "wrapper": {
                "width": "35",
                "class": "",
                "id": ""
            },
            "default_value": "",
            "maxlength": "",
            "allow_in_bindings": 0,
            "placeholder": "",
            "prepend": "#",
            "append": ""
        },
        {
            "key": "<?=uniqid('field_')?>",
            "label": "Screenshot",
            "name": "",
            "aria-label": "",
            "type": "message",
            "instructions": "",
            "required": 0,
            "conditional_logic": 0,
            "wrapper": {
                "width": "65",
                "class": "",
                "id": ""
            },
            "message": "<img src=\"<?=P10_BLOCK_SCREENSHOTS_URL . "cb{$block['cb']}"?>\" alt=\"<?=$block['label']?>\">",
            "new_lines": "",
            "esc_html": 0
        },
        {
            "key": "<?=uniqid('field_')?>",
            "label": "Content",
            "name": "",
            "aria-label": "",
            "type": "tab",
            "instructions": "",
            "required": 0,
            "conditional_logic": 0,
            "wrapper": {
                "width": "",
                "class": "",
                "id": ""
            },
            "placement": "left",
            "endpoint": 0,
            "selected": 0
        }
    ],
    "location": [
        [
            {
                "param": "post_type",
                "operator": "==",
                "value": "post"
            }
        ]
    ],
    "menu_order": 0,
    "position": "normal",
    "style": "default",
    "label_placement": "top",
    "instruction_placement": "label",
    "hide_on_screen": "",
    "active": false,
    "description": "",
    "show_in_rest": 0,
    "modified": <?=time()?>
}
<?php
                            $file_contents = ob_get_clean();
                            fwrite($f, $file_contents);
                            fclose($f);
                            $result['acf_group'] = file_exists($acf_json_file) ? $acf_json_file : false;
                        }
                    }
                }

                $block_groups[$block['banner'] ? 'banners' : 'content'][] = $block;
                $attempted[] = $result;
            }
            $flex_content_group_key = uniqid('group_fc');
            $flex_content_banner_key = uniqid('field_ba');
            $flex_content_content_key = uniqid('field_c0');
            $flex_content_json_file = P10_THEME_ACF_DIR . "/$flex_content_group_key.json";
            if(!file_exists($flex_content_json_file)) {
                if($f = fopen($flex_content_json_file , 'w')) {
                    ob_start();
?>{
    "key": "<?=$flex_content_group_key?>",
    "title": "Flexible Content",
    "fields": [
        {
            "key": "<?=$flex_content_banner_key?>",
            "label": "Banner",
            "name": "banner",
            "aria-label": "",
            "type": "flexible_content",
            "instructions": "",
            "required": 0,
            "conditional_logic": 0,
            "wrapper": {
                "width": "",
                "class": "flex-banner",
                "id": ""
            },
            "layouts": {<?php 
                    if(!empty($block_groups['banners'])):
                        foreach($block_groups['banners'] as $i => $block_group):
                            $layout_key = uniqid('layout_');
                ?>
                "<?=$layout_key?>": {
                    "key": "<?=$layout_key?>",
                    "name": "<?=$block_group['slug_full']?>",
                    "label": "<?=$block_group['full_name']?>",
                    "display": "block",
                    "sub_fields": [
                        {
                            "key": "<?=uniqid('field_')?>",
                            "label": "<?=$block_group['full_name']?>",
                            "name": "<?=$block_group['slug_full'] . '_fields'?>",
                            "aria-label": "",
                            "type": "clone",
                            "instructions": "",
                            "required": 0,
                            "conditional_logic": 0,
                            "wrapper": {
                                "width": "",
                                "class": "",
                                "id": ""
                            },
                            "clone": [
                                "<?=$block_group['acf_group']?>"
                            ],
                            "display": "seamless",
                            "layout": "",
                            "prefix_label": 0,
                            "prefix_name": 0
                        }
                    ],
                    "min": "",
                    "max": ""
                }<?php echo ($i < count($block_group) - 1) ? ',' : ''; endforeach; endif; ?>
            },
            "min": "",
            "max": 1,
            "button_label": "Choose a Banner"
        },
        {
            "key": "<?=$flex_content_content_key?>",
            "label": "Content",
            "name": "content",
            "aria-label": "",
            "type": "flexible_content",
            "instructions": "",
            "required": 0,
            "conditional_logic": 0,
            "wrapper": {
                "width": "",
                "class": "flex-content",
                "id": ""
            },
            "layouts": {<?php 
                    if(!empty($block_groups['content'])):
                        foreach($block_groups['content'] as $i => $block_group):
                            $layout_key = uniqid('layout_');
                ?>
                "<?=$layout_key?>": {
                    "key": "<?=$layout_key?>",
                    "name": "<?=$block_group['slug_full']?>",
                    "label": "<?=$block_group['full_name']?>",
                    "display": "block",
                    "sub_fields": [
                        {
                            "key": "<?=uniqid('field_')?>",
                            "label": "<?=$block_group['full_name']?>",
                            "name": "<?=$block_group['slug_full'] . '_fields'?>",
                            "aria-label": "",
                            "type": "clone",
                            "instructions": "",
                            "required": 0,
                            "conditional_logic": 0,
                            "wrapper": {
                                "width": "",
                                "class": "",
                                "id": ""
                            },
                            "clone": [
                                "<?=$block_group['acf_group']?>"
                            ],
                            "display": "seamless",
                            "layout": "",
                            "prefix_label": 0,
                            "prefix_name": 0
                        }
                    ],
                    "min": "",
                    "max": ""
                }<?php echo ($i < count($block_group) - 1) ? ',' : ''; endforeach; endif; ?>
            },
            "min": "",
            "max": "",
            "button_label": "Add a Block"
        }
    ],
    "location": [
        [
            {
                "param": "post_type",
                "operator": "==",
                "value": "page"
            }
        ],
        [
            {
                "param": "post_type",
                "operator": "==",
                "value": "post"
            }
        ]
    ],
    "menu_order": 10,
    "position": "acf_after_title",
    "style": "default",
    "label_placement": "top",
    "instruction_placement": "label",
    "hide_on_screen": "",
    "active": true,
    "description": "",
    "show_in_rest": 0,
    "modified": <?=time()?>
}
<?php
                    $file_contents = ob_get_clean();
                    fwrite($f, $file_contents);
                    fclose($f);
                }
            }
        }
    }
    ob_start();
?><div class="wrap">
        <h1 class="wp-heading-inline">Block Generator</h1>
        <?php if ($is_previewing): ?>
            <?php if (!empty($blocks)): ?>
                <div class="notice notice-info settings-error">
                    <p>The following blocks will be generated:</p>
                </div>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>
                                Index
                            </th>
                            <th>
                                Full Name
                            </th>
                            <th>
                                Area
                            </th>
                            <th>
                                Slug
                            </th>
                            <th>
                                CSS Selectors
                            </th>
                            <th>
                                Assets
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($blocks as $b): ?>
                            <tr>
                                <td>
                                    <?=$b['cb']?>
                                </td>
                                <td>
                                    <?=$b['full_name']?>
                                </td>
                                <td>
                                    <?=$b['banner'] ? '<strong>Banner</strong><br/><small>.push10-banner-block</small>' : 'Content<br/><small>.push10-block</small>'?>
                                </td>
                                <td>
                                    <?=$b['slug_full']?><br/>
                                </td>
                                <td>
                                    .<?=$b['class_name']?><br/>
                                    <small>.<?=$b['class_name_short']?></small>
                                </td>
                                <td>
                                    Template: <?=$b['assets']['template']?><br/>
                                    <small>Styles: <?=$b['assets']['style']?></small><br/>
                                    <small>Scripts: <?=$b['assets']['script']?></small><br/>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="notice notice-error settings-error">
                    <p><strong>No blocks could be generated.</strong></p>
                </div>
            <?php endif; ?>
        <?php elseif($is_submitting): ?>
            <?php if(!empty($blocks) && !empty($attempted)): ?>
                <div class="notice notice-info settings-error">
                    <p>The following blocks were generated:</p>
                    <ul>
                        <?php foreach($attempted as $b): ?>
                            <li>
                                <strong><?=$b['block']['full_name']?></strong>
                                <ul>
                                    <li>
                                        <strong>Template:</strong> <?=$b['template'] ?: '<em>Could not write file</em>'?>
                                    </li>
                                </ul>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php else: ?>
                <div class="notice notice-error settings-error">
                    <p><strong>No blocks could be generated.</strong></p>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p>Use this tool to generate a Flexible Content template that contains the blocks you enter below.</p>
        <?php endif; ?>
        <form method="post" action="">
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">
                            Blocks
                        </th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text">
                                    <span>Blocks</span>
                                </legend>
                                <p>
                                    <label for="blocks">
                                        Enter one block per line, separate with newlines and no commas:<br />
                                        <pre>CB01 Block Name
CB02 Another Block
CB03 Banner Block*</pre>
                                        <em>*Lines ending with an asterisk will be banners.</em>
                                    </label>
                                </p>
                                <p>
                                    <textarea name="blocks" id="blocks" class="regular-text code" rows="25" <?= $is_previewing ? 'readonly' : '' ?> required><?= $is_previewing ? $_POST['blocks'] : '' ?></textarea>
                                </p>
                                <p>
                                    <small>
                                        <strong>Additional Info:</strong><br>
                                        Lines ending with an asterisk will be banners*<br/>
                                        Words starting with lowercase will be title cased (e.g. "cb01 home banner" becomes "CB01 Home Banner")<br/>
                                        CB designation may be any of: CB01 | CB1 | cb01 | cb1 | 1 - these all become "CB01"
                                    </small>
                                </p>
                            </fieldset>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="<?= $is_previewing ? 'Generate' : 'Preview' ?>">
            </p>
        </form>
    </div><?php
    echo ob_get_clean();
}

function p10_block_generator_make_block_array(string $text): array|bool
{
    $clean_text = trim(preg_replace('/\s+/', ' ', $text));
    $block = [
        'banner' => false
    ];
    if('*' === substr($clean_text, -1)) {
        $block['banner'] = true;
        $clean_text = trim(trim($clean_text, "*"));
    }
    if (empty($clean_text)) return false;
    $block['generator'] = [
        'raw' => $text,
        'clean' => $clean_text
    ];
    $cb_matches = [];
    preg_match('/^(cb)?(\d+)(.+)/i', $clean_text, $cb_matches);
    if (isset($cb_matches[2]) && $cb_matches[2] && !empty($cb_matches[2])) {
        $block['cb'] = str_pad($cb_matches[2], 2, '0', STR_PAD_LEFT);
    } else {
        return false;
    }
    if (isset($cb_matches[3]) && $cb_matches[3] && !empty($cb_matches[3])) {
        $block['label'] = ucwords(trim($cb_matches[3]));
        $block['full_name'] = "CB{$block['cb']} - {$block['label']}";
        $block['slug'] = str_replace('-', '_', sanitize_title($block['label']));
        $block['slug_full'] = "cb{$block['cb']}-{$block['slug']}";
        $block['class_name'] = esc_attr($block['slug_full']);
        $block['class_name_short'] = esc_attr("cb{$block['cb']}");
        $block['assets'] = [
            'template' => P10_BLOCK_TEMPLATES_DIR . "/{$block['slug_full']}.php",
            'style' => P10_BLOCK_STYLES_DIR . "/{$block['slug_full']}.scss",
            'script' => P10_BLOCK_SCRIPTS_DIR . "/{$block['slug_full']}.js",
            'acf_group' => uniqid('group_10' . ($block['banner'] ? 'bb' : 'cb'))
        ];
    } else {
        return false;
    }

    return $block;
}

function p10_block_generator_make_block_assets(array $block) : bool {
    if(empty($block) || !isset($block['assets'])) return false;
    if(is_array($block['assets'])) {
        foreach($block['assets'] as $type => $path) {
            
        }
    }
    return true;
}
