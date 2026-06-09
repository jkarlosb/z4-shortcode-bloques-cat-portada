<?php

add_shortcode('z4_static_recent_post', function ($atts) {
    $atts = shortcode_atts([
        'id'              => '',
        'taxonomy'        => '',
        'term_id'         => '',
        'post_type'       => 'post',
        'image_size'      => 'large',
        'excerpt_words'   => 24,
        'read_more'       => 'Leer Más',
        'term_button'     => '',
        'term_url'        => '',
        'show_term_button'=> '1',
        'title_class'     => 'z4-fontsize-22',
        'target'          => '_self',
    ], $atts, 'z4_static_recent_post');

    $args = [
        'post_type'           => sanitize_key($atts['post_type']),
        'posts_per_page'      => 1,
        'post_status'         => 'publish',
        'ignore_sticky_posts' => false,
        'no_found_rows'       => true,
    ];

    if (!empty($atts['id'])) {
        $args['p'] = absint($atts['id']);
    } elseif (!empty($atts['taxonomy']) && !empty($atts['term_id'])) {
        $taxonomy = sanitize_key($atts['taxonomy']);
        $term_id  = absint($atts['term_id']);

        $args['tax_query'] = [
            [
                'taxonomy'         => $taxonomy,
                'field'            => 'term_id',
                'terms'            => [$term_id],
                'include_children' => true,
            ],
        ];
    }

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return '';
    }


    ob_start();

    while ($query->have_posts()) {
        $query->the_post();

        $post_id   = get_the_ID();
        $title     = get_the_title($post_id);
        $permalink = get_permalink($post_id);
        $date      = get_the_date('', $post_id);
        $excerpt   = wp_trim_words(get_the_excerpt($post_id), absint($atts['excerpt_words']), '...');

        $thumb_id = get_post_thumbnail_id($post_id);
        $img_html = '';


$featured_alignment = get_post_meta($post_id, '_custom_featured_image_alignment', true);

if (!in_array($featured_alignment, ['left', 'center', 'right'], true)) {
    $featured_alignment = 'center';
}

$featured_object_position = $featured_alignment . ' center';

        if ($thumb_id) {
            $alt = get_post_meta($thumb_id, '_wp_attachment_image_alt', true);
            $alt = $alt ?: $title;

            $img_html = wp_get_attachment_image(
                $thumb_id,
                $atts['image_size'],
                false,
                [
                    'class'             => 'wprpsp-post-img',
                    'alt'               => esc_attr($alt),
                    'data-pin-no-hover' => 'true',
                    'loading'           => 'eager',
                    'decoding'          => 'async',
		    'fetchpriority'     => 'high',
		    'style'             => 'object-position: ' . esc_attr($featured_object_position) . ';',
                ]
            );
        }

        $term_button_label = trim($atts['term_button']);
        $term_button_url   = trim($atts['term_url']);

        if (
            $atts['show_term_button'] === '1'
            && empty($term_button_label)
            && !empty($atts['taxonomy'])
            && !empty($atts['term_id'])
        ) {
            $taxonomy = sanitize_key($atts['taxonomy']);
            $term_id  = absint($atts['term_id']);
            $term     = get_term($term_id, $taxonomy);

            if ($term && !is_wp_error($term)) {
                $term_button_label = mb_strtoupper($term->name);
                $term_button_url   = get_term_link($term);
            }
        }


        if (!empty($term_button_label) && empty($term_button_url)) {
            $term_button_url = '#';
        }
        ?>

        <div class="z4-pro-slider-wrp wprpsp-clearfix z4-static-recent-post">
            <div class="wprpsp-recent-post-slider z4-wprpsp-post-slider wprpsp-design-4 wprpsp-image-fit" style="visibility: visible; opacity: 1;">
                <div class="wprpsp-post-slides">
                    <div class="wprpsp-post-list-wrap">
                        <div class="wprpsp-post-list-cnt">

                            <div class="wprpsp-medium-5 wprpsp-columns">
                                <div class="wprpsp-post-image-wrap wprpsp-post-image-bg z4imgcenter">
                                    <?php echo $img_html; ?>
                                </div>

                                <a class="wprpsp-post-link"
                                   href="<?php echo esc_url($permalink); ?>"
                                   target="<?php echo esc_attr($atts['target']); ?>"></a>
                            </div>

                            <div class="wprpsp-medium-7 wprpsp-columns">
                                <h3 class="wprpsp-post-title">
                                    <a href="<?php echo esc_url($permalink); ?>"
                                       target="<?php echo esc_attr($atts['target']); ?>"
				       class="<?php echo esc_attr($atts['title_class']); ?>">
			<?php if(get_field("z4-titulo") != null){
                                echo "<div class='z4-colour-title'>". get_field("z4-titulo") ."</div>";
                            }else{
                                echo esc_html($title);
                            }?>

                                    </a>
                                </h3>

                                <div class="wprpsp-post-date">
                                    <?php echo esc_html($date); ?>
                                </div>

                                <div class="wprpsp-post-content">
                                    <div><?php echo esc_html($excerpt); ?></div>

                                    <a class="wprpsp-read-more-btn"
                                       href="<?php echo esc_url($permalink); ?>"
                                       target="<?php echo esc_attr($atts['target']); ?>">
                                        <?php echo esc_html($atts['read_more']); ?>
                                    </a>

                                    <?php if ($atts['show_term_button'] === '1' && !empty($term_button_label)) : ?>
                                        <a class="wprpsp-read-more-btn z4studios-btn"
                                           href="<?php echo esc_url($term_button_url); ?>"
                                           target="<?php echo esc_attr($atts['target']); ?>">
                                            <?php echo esc_html($term_button_label); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
    }

    wp_reset_postdata();

    return ob_get_clean();
});
