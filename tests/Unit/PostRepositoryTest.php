<?php

declare(strict_types=1);

namespace WonderWp\Component\Repository\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WonderWp\Component\Repository\PostRepository;

final class PostRepositoryTest extends TestCase
{
    private PostRepository $repo;

    protected function setUp(): void
    {
        $GLOBALS['wwp_test_get_posts_calls'] = [];
        $GLOBALS['wwp_test_get_posts_return'] = [];
        $GLOBALS['wwp_test_get_post_return'] = null;
        $this->repo = new PostRepository();
    }

    public function testFindByMapsLimitOffsetAndOrderBy(): void
    {
        $this->repo->findBy(['post_status' => 'publish'], ['date' => 'DESC'], 2, 1);

        $this->assertCount(1, $GLOBALS['wwp_test_get_posts_calls']);
        $args = $GLOBALS['wwp_test_get_posts_calls'][0];
        $this->assertSame('post', $args['post_type']);
        $this->assertSame('publish', $args['post_status']);
        $this->assertSame(2, $args['numberposts']);
        $this->assertSame(1, $args['offset']);
        $this->assertSame('date', $args['orderby']);
        $this->assertSame('DESC', $args['order']);
    }

    public function testFindByDoesNotTreatOrderByAsPostFieldFilter(): void
    {
        $this->repo->findBy([], ['title' => 'ASC']);
        $args = $GLOBALS['wwp_test_get_posts_calls'][0];

        $this->assertArrayNotHasKey('title', $args);
        $this->assertSame('title', $args['orderby']);
        $this->assertSame('ASC', $args['order']);
    }

    public function testFindByWithoutLimitUsesNumberpostsAll(): void
    {
        $this->repo->findBy([]);
        $args = $GLOBALS['wwp_test_get_posts_calls'][0];
        $this->assertSame(-1, $args['numberposts']);
        $this->assertArrayNotHasKey('offset', $args);
    }

    public function testFindByZeroLimitTreatedAsUnlimitedDueToEmptyCheck(): void
    {
        // Documents current contract: !empty(0) is false → numberposts = -1.
        $this->repo->findBy([], null, 0);
        $this->assertSame(-1, $GLOBALS['wwp_test_get_posts_calls'][0]['numberposts']);
    }

    public function testFindByPreservesExplicitPostType(): void
    {
        $this->repo->findBy(['post_type' => 'page']);
        $this->assertSame('page', $GLOBALS['wwp_test_get_posts_calls'][0]['post_type']);
    }

    public function testFindReturnsWpPost(): void
    {
        $post = new \WP_Post();
        $post->ID = 42;
        $GLOBALS['wwp_test_get_post_return'] = $post;

        $found = $this->repo->find(42);
        $this->assertInstanceOf(\WP_Post::class, $found);
        $this->assertSame(42, $found->ID);
    }

    public function testFindReturnsNullWhenMissing(): void
    {
        $GLOBALS['wwp_test_get_post_return'] = null;
        $this->assertNull($this->repo->find(999));
    }

    public function testFindOneByUsesLimitOne(): void
    {
        $post = new \WP_Post();
        $post->ID = 7;
        $GLOBALS['wwp_test_get_posts_return'] = [$post];

        $one = $this->repo->findOneBy(['name' => 'hello']);
        $this->assertSame($post, $one);
        $this->assertSame(1, $GLOBALS['wwp_test_get_posts_calls'][0]['numberposts']);
    }

    public function testGetClassName(): void
    {
        $this->assertSame(\WP_Post::class, $this->repo->getClassName());
    }
}
