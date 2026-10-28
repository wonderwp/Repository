<?php
/**
 * Integration smoke for TaxonomyRepository against a real WordPress + DB.
 *
 * Run from ANY WordPress project where wonderwp/repository is installed:
 *
 *   wp eval-file vendor/wonderwp/repository/tests/Integration/smoke-taxonomy.php
 *   ddev wp eval-file vendor/wonderwp/repository/tests/Integration/smoke-taxonomy.php
 *
 * Optional: if a subclass for another taxonomy exists in the host (e.g. ActuTermsRepository
 * + news_category), it is discovered and exercised too.
 */

use WonderWp\Component\Repository\TaxonomyRepository;

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

if (!class_exists(TaxonomyRepository::class)) {
    fwrite(STDERR, "wonderwp/repository is not autoloadable from this WordPress project.\n");
    exit(1);
}

$failed = 0;
$passed = 0;

$assert = static function (bool $ok, string $label, mixed $detail = null) use (&$failed, &$passed): void {
    if ($ok) {
        $passed++;
        $out = defined('WP_CLI') && class_exists('WP_CLI') ? [WP_CLI::class, 'log'] : 'fwrite';
        if (is_array($out)) {
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

$ids = static function (array $terms): array {
    return array_values(array_map(static fn ($t) => (int) $t->term_id, $terms));
};

$names = static function (array $terms): array {
    return array_values(array_map(static fn ($t) => $t->name, $terms));
};

fwrite(STDOUT, "=== TaxonomyRepository Integration smoke ===\n");

/** @var array<string, class-string<TaxonomyRepository>> $suites */
$suites = [
    'category' => TaxonomyRepository::class,
];

$actuClass = 'WonderWp\\Plugin\\Actu\\Repository\\ActuTermsRepository';
if (class_exists($actuClass) && taxonomy_exists('news_category')) {
    $suites['news_category'] = $actuClass;
}

foreach ($suites as $taxonomy => $class) {
    fwrite(STDOUT, "\n--- suite: $taxonomy ($class) ---\n");

    /** @var TaxonomyRepository $repo */
    $repo = new $class();

    $directAll = get_terms([
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
    ]);
    $assert(is_array($directAll) && !is_wp_error($directAll), "$taxonomy get_terms baseline is array");
    if (!is_array($directAll)) {
        continue;
    }

    $total = count($directAll);
    $assert($total > 0, "$taxonomy has fixtures (count=$total)");

    $viaRepoAll = $repo->findAll();
    $directHideEmptyDefault = get_terms(['taxonomy' => $taxonomy]);
    $assert(is_array($viaRepoAll), "$taxonomy findAll returns array");
    $assert(
        $ids($viaRepoAll) === $ids(is_array($directHideEmptyDefault) ? $directHideEmptyDefault : []),
        "$taxonomy findAll IDs match get_terms(taxonomy only)"
    );

    if ($total >= 2) {
        $limited = $repo->findBy(['hide_empty' => false], null, 2);
        $directLimited = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'number' => 2,
        ]);
        $assert(count($limited) === 2, "$taxonomy findBy limit=2 returns 2", count($limited));
        $assert(
            $ids($limited) === $ids(is_array($directLimited) ? $directLimited : []),
            "$taxonomy findBy limit=2 IDs match get_terms number=2"
        );
    }

    if ($total >= 3) {
        $page = $repo->findBy(['hide_empty' => false], null, 2, 1);
        $directPage = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'number' => 2,
            'offset' => 1,
        ]);
        $assert(count($page) === 2, "$taxonomy findBy limit=2 offset=1 returns 2", count($page));
        $assert(
            $ids($page) === $ids(is_array($directPage) ? $directPage : []),
            "$taxonomy findBy limit/offset IDs match get_terms"
        );
        $allIds = $ids(get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]));
        $assert($ids($page) === array_slice($allIds, 1, 2), "$taxonomy offset slice matches full list");
    }

    $firstId = (int) $directAll[0]->term_id;
    $found = $repo->find($firstId);
    $assert($found instanceof WP_Term && (int) $found->term_id === $firstId, "$taxonomy find($firstId)");

    $missing = $repo->find(999999991);
    $assert($missing === null || $missing instanceof WP_Error, "$taxonomy find(missing) null|WP_Error");

    $one = $repo->findOneBy(['hide_empty' => false, 'slug' => $directAll[0]->slug]);
    $assert($one instanceof WP_Term && $one->slug === $directAll[0]->slug, "$taxonomy findOneBy slug");

    $orderedAsc = $repo->findBy(['hide_empty' => false], ['name' => 'ASC']);
    $directAsc = get_terms([
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC',
    ]);
    $assert(
        $names($orderedAsc) === $names(is_array($directAsc) ? $directAsc : []),
        "$taxonomy orderBy ['name'=>'ASC'] matches WP orderby/order ASC"
    );

    $orderedDesc = $repo->findBy(['hide_empty' => false], ['name' => 'DESC']);
    $directDesc = get_terms([
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'DESC',
    ]);
    $assert(
        $names($orderedDesc) === $names(is_array($directDesc) ? $directDesc : []),
        "$taxonomy orderBy ['name'=>'DESC'] matches WP orderby/order DESC"
    );
}

fwrite(STDOUT, "\n=== result: $passed passed, $failed failed ===\n");

if ($failed > 0) {
    exit(1);
}

fwrite(STDOUT, "OK wonderwp/repository Integration smoke-taxonomy\n");
