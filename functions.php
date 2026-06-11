<?php
/**
 * ST4RIS Theme functions
 */

// Enqueue parent + child styles
add_action( 'wp_enqueue_scripts', 'st4ris_enqueue_styles' );
function st4ris_enqueue_styles() {
    wp_enqueue_style(
        'parent-style',
        get_template_directory_uri() . '/style.css',
        [],
        wp_get_theme( get_template() )->get( 'Version' )
    );
    wp_enqueue_style(
        'st4ris-style',
        get_stylesheet_directory_uri() . '/style.css',
        [ 'parent-style' ],
        filemtime( get_stylesheet_directory() . '/style.css' )
    );
    wp_enqueue_script(
        'st4ris-carousel',
        get_stylesheet_directory_uri() . '/js/carousel.js',
        [],
        filemtime( get_stylesheet_directory() . '/js/carousel.js' ),
        true
    );
}

// Favicon / site icons.
add_action( 'wp_head', 'st4ris_favicons' );
add_action( 'admin_head', 'st4ris_favicons' );
function st4ris_favicons() {
    $base = get_stylesheet_directory_uri() . '/assets/images/favicons';
    echo '<link rel="icon" href="' . esc_url( $base . '/favicon.ico' ) . '" sizes="any">' . "\n";
    echo '<link rel="icon" type="image/png" href="' . esc_url( $base . '/favicon-192.png' ) . '" sizes="192x192">' . "\n";
    echo '<link rel="apple-touch-icon" href="' . esc_url( $base . '/apple-touch-icon.png' ) . '" sizes="180x180">' . "\n";
}

// Register block patterns
add_action( 'init', 'st4ris_register_patterns' );
function st4ris_register_patterns() {
    register_block_pattern_category(
        'st4ris',
        [ 'label' => __( 'ST4RIS', 'st4ris-child' ) ]
    );
}

// Register custom post type: Staff Stories
add_action( 'init', 'st4ris_register_cpts' );
function st4ris_register_cpts() {
    register_post_type( 'staff_story', [
        'labels' => [
            'name'          => __( 'Staff Stories', 'st4ris-child' ),
            'singular_name' => __( 'Staff Story',   'st4ris-child' ),
            'add_new_item'  => __( 'Add New Story',  'st4ris-child' ),
        ],
        'public'       => true,
        'show_in_rest' => true,
        'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
        'menu_icon'    => 'dashicons-format-quote',
    ] );

    // Events CPT
    register_post_type( 'st4ris_event', [
        'labels' => [
            'name'          => __( 'Events',    'st4ris-child' ),
            'singular_name' => __( 'Event',     'st4ris-child' ),
            'add_new_item'  => __( 'Add Event', 'st4ris-child' ),
        ],
        'public'       => true,
        'show_in_rest' => true,
        'supports'     => [ 'title', 'editor', 'thumbnail' ],
        'menu_icon'    => 'dashicons-calendar-alt',
    ] );
}

// Register custom meta for events
add_action( 'init', 'st4ris_register_meta' );
function st4ris_register_meta() {
    register_post_meta( 'st4ris_event', 'event_date', [
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
    ] );
    register_post_meta( 'st4ris_event', 'event_location', [
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
    ] );
    register_post_meta( 'st4ris_event', 'event_type', [
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string', // 'event' | 'achievement'
    ] );
}

// Add meta boxes for events in classic editor fallback
add_action( 'add_meta_boxes', 'st4ris_event_meta_boxes' );
function st4ris_event_meta_boxes() {
    add_meta_box(
        'st4ris_event_details',
        __( 'Event Details', 'st4ris-child' ),
        'st4ris_event_meta_callback',
        'st4ris_event',
        'side'
    );
}
function st4ris_event_meta_callback( $post ) {
    $date     = get_post_meta( $post->ID, 'event_date',     true );
    $location = get_post_meta( $post->ID, 'event_location', true );
    $type     = get_post_meta( $post->ID, 'event_type',     true );
    wp_nonce_field( 'st4ris_event_nonce', 'st4ris_nonce' );
    echo '<p><label>Date<br><input name="event_date" value="' . esc_attr( $date ) . '" style="width:100%"></label></p>';
    echo '<p><label>Location<br><input name="event_location" value="' . esc_attr( $location ) . '" style="width:100%"></label></p>';
    echo '<p><label>Type<br><select name="event_type" style="width:100%">';
    echo '<option value="event"' . selected( $type, 'event', false ) . '>Event</option>';
    echo '<option value="achievement"' . selected( $type, 'achievement', false ) . '>Achievement</option>';
    echo '</select></label></p>';
}

add_action( 'save_post_st4ris_event', 'st4ris_save_event_meta' );
function st4ris_save_event_meta( $post_id ) {
    if ( ! isset( $_POST['st4ris_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['st4ris_nonce'], 'st4ris_event_nonce' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    foreach ( [ 'event_date', 'event_location', 'event_type' ] as $key ) {
        if ( isset( $_POST[ $key ] ) ) {
            update_post_meta( $post_id, $key, sanitize_text_field( $_POST[ $key ] ) );
        }
    }
}

// Shortcodes used by the editable homepage pattern.
add_shortcode( 'st4ris_staff_stories', 'st4ris_staff_stories_shortcode' );
function st4ris_staff_stories_shortcode() {
    $stories = get_posts( [
        'post_type'      => 'staff_story',
        'posts_per_page' => 6,
        'post_status'    => 'publish',
        'orderby'        => 'menu_order date',
        'order'          => 'ASC',
    ] );

    if ( empty( $stories ) ) {
        $stories = [
            [ 'icon' => '🔬', 'quote' => "The types of workshops or courses presented to me are limited and are often rather inconvenient, and I need to seek them out myself. I don't always know what courses are relevant to me or what is even available.", 'by' => 'Lab technician, limnology' ],
            [ 'icon' => '🌲', 'quote' => 'The job title is sometimes a challenge in itself. It is extremely broad and is used within every field of natural sciences. Contacting colleagues with the same title for help often does not lead to a solution since they have completely different backgrounds.', 'by' => 'Research engineer, forest ecology' ],
            [ 'icon' => '💧', 'quote' => "In my current position within research infrastructure, I have felt that I have started to approach the 'roof' of what I can learn at my job. I don't see a long future here if I can't find opportunities to grow and develop my skills.", 'by' => 'Lab technician, limnology' ],
        ];
    }

    ob_start();
    echo '<div class="story-carousel"><div class="story-track st4ris-carousel-track" id="st4ris-track">';
    foreach ( $stories as $story ) {
        echo '<div class="story-slide">';
        if ( is_array( $story ) ) {
            echo '<div class="story-photo-placeholder">' . esc_html( $story['icon'] ) . '</div>';
            echo '<blockquote>"' . esc_html( $story['quote'] ) . '"<footer>' . esc_html( $story['by'] ) . '</footer></blockquote>';
        } else {
            if ( has_post_thumbnail( $story ) ) {
                echo get_the_post_thumbnail( $story, 'thumbnail', [ 'class' => 'story-photo-placeholder' ] );
            } else {
                echo '<div class="story-photo-placeholder">“</div>';
            }
            $quote = wp_strip_all_tags( $story->post_content ?: $story->post_excerpt );
            echo '<blockquote>"' . esc_html( $quote ) . '"<footer>' . esc_html( get_the_title( $story ) ) . '</footer></blockquote>';
        }
        echo '</div>';
    }
    echo '</div></div><div class="story-dots">';
    $count = count( $stories );
    for ( $i = 0; $i < $count; $i++ ) {
        echo '<button class="dot st4ris-dot' . ( 0 === $i ? ' active' : '' ) . '" aria-label="Story ' . esc_attr( $i + 1 ) . '"></button>';
    }
    echo '</div><div class="story-btns"><button id="st4ris-prev">← Prev</button><button id="st4ris-next">Next →</button></div>';
    return ob_get_clean();
}

add_shortcode( 'st4ris_events', 'st4ris_events_shortcode' );
function st4ris_events_shortcode() {
    $events = get_posts( [
        'post_type'      => 'st4ris_event',
        'posts_per_page' => 3,
        'post_status'    => 'publish',
        'orderby'        => 'meta_value date',
        'meta_key'       => 'event_date',
        'order'          => 'ASC',
    ] );

    ob_start();
    echo '<div class="events-grid">';
    if ( empty( $events ) ) {
        $events = [
            [ 'type' => 'event', 'title' => 'ST4RIS @ BioGeoMon meeting', 'meta' => 'Umeå, 8–12 June', 'text' => 'Pilot survey on user needs for information dissemination' ],
            [ 'type' => 'event', 'title' => 'ST4RIS @ ICOS meeting', 'meta' => 'Lund, 15–17 September', 'text' => 'Survey to poll user needs' ],
            [ 'type' => 'achievement', 'title' => 'ST4RIS Kick-off meeting', 'meta' => 'Helsinki, 6–7 October', 'text' => 'Stay tuned for updates' ],
        ];
    }

    foreach ( $events as $event ) {
        if ( is_array( $event ) ) {
            $type = $event['type']; $title = $event['title']; $meta = $event['meta']; $text = $event['text'];
        } else {
            $type = get_post_meta( $event->ID, 'event_type', true ) ?: 'event';
            $title = get_the_title( $event );
            $date = get_post_meta( $event->ID, 'event_date', true );
            $location = get_post_meta( $event->ID, 'event_location', true );
            $meta = trim( $location . ( $location && $date ? ', ' : '' ) . $date );
            $text = wp_strip_all_tags( $event->post_content );
        }
        echo '<div class="event-card"><div class="event-type ' . esc_attr( $type ) . '">' . esc_html( ucfirst( $type ) ) . '</div><h3>' . esc_html( $title ) . '</h3>';
        if ( $meta ) echo '<p>' . esc_html( $meta ) . '</p>';
        if ( $text ) echo '<p class="event-note">' . esc_html( $text ) . '</p>';
        echo '</div>';
    }
    echo '</div>';
    return ob_get_clean();
}

// Theme support
add_action( 'after_setup_theme', 'st4ris_setup' );
function st4ris_setup() {
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'align-wide' );
}
