<?php
    require 'wp-includes/formatting.php';
    require 'wp-includes/functions.php';
    require 'wp-includes/post.php';
    require 'wp-includes/user.php';
    require 'wp-includes/class-wp-post.php';
    require 'wp-includes/cache.php';

    function do_action( $tag, ...$arg ) {
        global $wp_filter, $wp_actions, $wp_current_filter;
     
        if ( ! isset( $wp_actions[ $tag ] ) ) {
            $wp_actions[ $tag ] = 1;
        } else {
            ++$wp_actions[ $tag ];
        }
     
        // Do 'all' actions first.
        if ( isset( $wp_filter['all'] ) ) {
            $wp_current_filter[] = $tag;
            $all_args            = func_get_args();
            _wp_call_all_hook( $all_args );
        }
     
        if ( ! isset( $wp_filter[ $tag ] ) ) {
            if ( isset( $wp_filter['all'] ) ) {
                array_pop( $wp_current_filter );
            }
            return;
        }
     
        if ( ! isset( $wp_filter['all'] ) ) {
            $wp_current_filter[] = $tag;
        }
     
        if ( empty( $arg ) ) {
            $arg[] = '';
        } elseif ( is_array( $arg[0] ) && 1 === count( $arg[0] ) && isset( $arg[0][0] ) && is_object( $arg[0][0] ) ) {
            // Backward compatibility for PHP4-style passing of `array( &$this )` as action `$arg`.
            $arg[0] = $arg[0][0];
        }
     
        $wp_filter[ $tag ]->do_action( $arg );
     
        array_pop( $wp_current_filter );
    }

    function apply_filters( $tag, $value ) {
        global $wp_filter, $wp_current_filter;
     
        $args = func_get_args();
     
        // Do 'all' actions first.
        if ( isset( $wp_filter['all'] ) ) {
            $wp_current_filter[] = $tag;
            _wp_call_all_hook( $args );
        }
     
        if ( ! isset( $wp_filter[ $tag ] ) ) {
            if ( isset( $wp_filter['all'] ) ) {
                array_pop( $wp_current_filter );
            }
            return $value;
        }
     
        if ( ! isset( $wp_filter['all'] ) ) {
            $wp_current_filter[] = $tag;
        }
     
        // Don't pass the tag name to WP_Hook.
        array_shift( $args );
     
        $filtered = $wp_filter[ $tag ]->apply_filters( $value, $args );
     
        array_pop( $wp_current_filter );
     
        return $filtered;
    }