<?php

declare(strict_types=1);

namespace WonderWp\Component\Repository\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WonderWp\Component\Repository\TaxonomyRepository;

final class TaxonomyRepositoryTest extends TestCase
{
    private TaxonomyRepository $repo;

    protected function setUp(): void
    {
        $GLOBALS['wwp_test_get_terms_calls'] = [];
        $GLOBALS['wwp_test_get_terms_return'] = [];
        $GLOBALS['wwp_test_get_term_return'] = null;
        $this->repo = new TaxonomyRepository();
    }

    public function testFindByMapsLimitOffsetAndOrderBy(): void
    {
        $GLOBALS['wwp_test_get_terms_return'] = [];

        $this->repo->findBy(['hide_empty' => false], ['name' => 'ASC'], 2, 1);

        $this->assertCount(1, $GLOBALS['wwp_test_get_terms_calls']);
        $args = $GLOBALS['wwp_test_get_terms_calls'][0];
        $this->assertSame('category', $args['taxonomy']);
        $this->assertFalse($args['hide_empty']);
        $this->assertSame(2, $args['number']);
        $this->assertSame(1, $args['offset']);
        $this->assertSame('name', $args['orderby']);
        $this->assertSame('ASC', $args['order']);
    }

    public function testFindByDoesNotTreatOrderByAsTermNameFilter(): void
    {
        $this->repo->findBy([], ['name' => 'DESC']);
        $args = $GLOBALS['wwp_test_get_terms_calls'][0];

        $this->assertArrayNotHasKey('name', $args);
        $this->assertSame('name', $args['orderby']);
        $this->assertSame('DESC', $args['order']);
    }

    public function testFindByReturnsEmptyArrayWhenGetTermsFails(): void
    {
        $GLOBALS['wwp_test_get_terms_return'] = new \WP_Error('fail', 'boom');

        $this->assertSame([], $this->repo->findBy([]));
    }

    public function testFindReturnsWpTerm(): void
    {
        $term = new \WP_Term();
        $term->term_id = 11;
        $GLOBALS['wwp_test_get_term_return'] = $term;

        $found = $this->repo->find(11);
        $this->assertInstanceOf(\WP_Term::class, $found);
        $this->assertSame(11, $found->term_id);
    }

    public function testFindOneByUsesLimitOne(): void
    {
        $term = new \WP_Term();
        $term->slug = 'hello';
        $GLOBALS['wwp_test_get_terms_return'] = [$term];

        $one = $this->repo->findOneBy(['slug' => 'hello']);
        $this->assertSame($term, $one);
        $this->assertSame(1, $GLOBALS['wwp_test_get_terms_calls'][0]['number']);
    }

    public function testGetClassName(): void
    {
        $this->assertSame(\WP_Term::class, $this->repo->getClassName());
    }
}
