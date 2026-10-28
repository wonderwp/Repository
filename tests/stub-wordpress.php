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
}

