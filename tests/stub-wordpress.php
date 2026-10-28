<?php

declare(strict_types=1);

namespace {
    if (!class_exists('WP_Term', false)) {
        class WP_Term
        {
            public int $term_id = 0;
            public string $name = '';
            public string $slug = '';
            public string $taxonomy = '';
        }
    }

    if (!class_exists('WP_Post', false)) {
        class WP_Post
        {
            public int $ID = 0;
            public string $post_title = '';
            public string $post_type = 'post';
            public string $post_status = 'publish';
        }
    }

    if (!class_exists('WP_User', false)) {
        class WP_User
        {
            public int $ID = 0;
            public string $user_login = '';
        }
    }

    if (!class_exists('WP_Error', false)) {
        class WP_Error
        {
            public function __construct(public $code = '', public $message = '', public $data = '')
            {
            }
        }
    }

    if (!function_exists('get_terms')) {
        function get_terms($args = [], $deprecated = '')
        {
            $GLOBALS['wwp_test_get_terms_calls'][] = $args;

            return $GLOBALS['wwp_test_get_terms_return'] ?? [];
        }
    }

    if (!function_exists('get_term')) {
        function get_term($term, $taxonomy = '', $output = 'OBJECT', $filter = 'raw')
        {
            return $GLOBALS['wwp_test_get_term_return'] ?? null;
        }
    }

    if (!function_exists('get_posts')) {
        function get_posts($args = null)
        {
            $GLOBALS['wwp_test_get_posts_calls'][] = $args;

            return $GLOBALS['wwp_test_get_posts_return'] ?? [];
        }
    }

    if (!function_exists('get_post')) {
        function get_post($post = null, $output = 'OBJECT', $filter = 'raw')
        {
            return $GLOBALS['wwp_test_get_post_return'] ?? null;
        }
    }

    if (!function_exists('get_users')) {
        function get_users($args = [])
        {
            $GLOBALS['wwp_test_get_users_calls'][] = $args;

            return $GLOBALS['wwp_test_get_users_return'] ?? [];
        }
    }

    if (!function_exists('get_user_by')) {
        function get_user_by($field, $value)
        {
            return $GLOBALS['wwp_test_get_user_by_return'] ?? false;
        }
    }
}
