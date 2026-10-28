<?php
/**
 * Integration smoke for PostRepository against a real WordPress + DB.
 *
 *   wp eval-file vendor/wonderwp/repository/tests/Integration/smoke-posts.php
 *   ddev wp eval-file vendor/wonderwp/repository/tests/Integration/smoke-posts.php
 */

use WonderWp\Component\Repository\PostRepository;

if (!defined('ABSPATH')) {
    fwrite(STDERR, "WordPress is not loaded. Run via WP-CLI from a project that requires wonderwp/repository.\n");
    exit(1);
}

$autoloadCandidates = [
    dirname(__DIR__, 4) . '/autoload.php',
    dirname(__DIR__, 2) . '/vendor/autoload.php',
];
foreach ($autoloadCandidates as $autoload) {
    if (is_file($autoload)) {
        require_once $autoload;
        break;
    }
}

if (!class_exists(PostRepository::class)) {
    fwrite(STDERR, "wonderwp/repository is not autoloadable from this WordPress project.\n");
    exit(1);
}

$failed = 0;
$passed = 0;

$assert = static function (bool $ok, string $label, mixed $detail = null) use (&$failed, &$passed): void {
    if ($ok) {
        $passed++;
        if (defined('WP_CLI') && class_exists('WP_CLI')) {
            WP_CLI::log("  PASS  $label");
        } else {
            fwrite(STDOUT, "  PASS  $label\n");
        }
        return;
    }
    $failed++;
    $extra = $detail !== null ? ' | ' . (is_string($detail) ? $detail : wp_json_encode($detail)) : '';
    if (defined('WP_CLI') && class_exists('WP_CLI')) {
        WP_CLI::warning("  FAIL  $label$extra");
    } else {
        fwrite(STDERR, "  FAIL  $label$extra\n");
    }
};

$ids = static function (array $posts): array {
    return array_values(array_map(static fn ($p) => (int) $p->ID, $posts));
};

fwrite(STDOUT, "=== PostRepository Integration smoke ===\n");

$repo = new PostRepository();
$criteria = ['post_type' => 'post', 'post_status' => 'publish'];

$directAll = get_posts(array_merge($criteria, ['numberposts' => -1]));
$assert(is_array($directAll), 'get_posts baseline is array');
$total = count($directAll);

$viaRepoAll = $repo->findAll();
$assert(is_array($viaRepoAll), 'findAll returns array');
$assert($ids($viaRepoAll) === $ids($directAll), 'findAll IDs match get_posts');

if ($total === 0) {
    fwrite(STDOUT, "  SKIP  no published posts — insert fixtures to exercise limit/offset/orderBy\n");
}

if ($total >= 2) {
    $limited = $repo->findBy($criteria, null, 2);
    $directLimited = get_posts(array_merge($criteria, ['numberposts' => 2]));
    $assert(count($limited) === 2, 'findBy limit=2 returns 2', count($limited));
    $assert($ids($limited) === $ids($directLimited), 'findBy limit=2 IDs match get_posts');
}

if ($total >= 3) {
    $page = $repo->findBy($criteria, null, 2, 1);
    $directPage = get_posts(array_merge($criteria, ['numberposts' => 2, 'offset' => 1]));
    $assert(count($page) === 2, 'findBy limit=2 offset=1 returns 2', count($page));
    $assert($ids($page) === $ids($directPage), 'findBy limit/offset IDs match get_posts');
}

if ($total >= 1) {
    $firstId = (int) $directAll[0]->ID;
    $found = $repo->find($firstId);
    $assert($found instanceof WP_Post && (int) $found->ID === $firstId, "find($firstId)");

    $missing = $repo->find(999999991);
    $assert($missing === null, 'find(missing) null');

    $orderedDesc = $repo->findBy($criteria, ['date' => 'DESC']);
    $directDesc = get_posts(array_merge($criteria, [
        'numberposts' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
    ]));
    $assert($ids($orderedDesc) === $ids($directDesc), "orderBy ['date'=>'DESC'] matches WP");
}

fwrite(STDOUT, "\n=== result: $passed passed, $failed failed ===\n");

if ($failed > 0) {
    exit(1);
}

fwrite(STDOUT, "OK wonderwp/repository Integration smoke-posts\n");
