<?php
/**
 * ST4RIS Theme functions
 */

// Enqueue parent + child styles
add_action( 'wp_enqueue_scripts', 'st4ris_enqueue_styles' );
function st4ris_enqueue_styles() {
    wp_enqueue_style(
        'parent-style',
        get_template_directory_uri() . '/style.css'
    );
    wp_enqueue_style(
        'st4ris-style',
        get_stylesheet_directory_uri() . '/style.css',
        [ 'parent-style' ]
    );
    // Carousel JS
    wp_enqueue_script(
        'st4ris-carousel',
        get_stylesheet_directory_uri() . '/js/carousel.js',
        [],
        '1.0.0',
        true
    );
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

// Theme support
add_action( 'after_setup_theme', 'st4ris_setup' );
function st4ris_setup() {
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'align-wide' );
}
