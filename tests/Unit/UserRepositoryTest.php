<?php

declare(strict_types=1);

namespace WonderWp\Component\Repository\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WonderWp\Component\Repository\UserRepository;

final class UserRepositoryTest extends TestCase
{
    private UserRepository $repo;

    protected function setUp(): void
    {
        $GLOBALS['wwp_test_get_users_calls'] = [];
        $GLOBALS['wwp_test_get_users_return'] = [];
        $GLOBALS['wwp_test_get_user_by_return'] = false;
        $this->repo = new UserRepository();
    }

    public function testFindByMapsLimitAndOffset(): void
    {
        $this->repo->findBy(['role' => 'subscriber'], null, 5, 10);

        $this->assertCount(1, $GLOBALS['wwp_test_get_users_calls']);
        $args = $GLOBALS['wwp_test_get_users_calls'][0];
        $this->assertSame('subscriber', $args['role']);
        $this->assertSame(5, $args['number']);
        $this->assertSame(10, $args['offset']);
    }

    public function testFindByMergesOrderByIntoArgs(): void
    {
        // Documents current contract: orderBy is array_merge'd (unlike Post/Taxonomy split).
        $this->repo->findBy([], ['orderby' => 'login', 'order' => 'ASC']);
        $args = $GLOBALS['wwp_test_get_users_calls'][0];
        $this->assertSame('login', $args['orderby']);
        $this->assertSame('ASC', $args['order']);
    }

    public function testFindByZeroLimitOmitsNumberDueToEmptyCheck(): void
    {
        $this->repo->findBy([], null, 0);
        $this->assertArrayNotHasKey('number', $GLOBALS['wwp_test_get_users_calls'][0]);
    }

    public function testFindReturnsWpUser(): void
    {
        $user = new \WP_User();
        $user->ID = 3;
        $GLOBALS['wwp_test_get_user_by_return'] = $user;

        $found = $this->repo->find(3);
        $this->assertInstanceOf(\WP_User::class, $found);
        $this->assertSame(3, $found->ID);
    }

    public function testFindReturnsNullWhenMissing(): void
    {
        $GLOBALS['wwp_test_get_user_by_return'] = false;
        $this->assertNull($this->repo->find(999));
    }

    public function testFindOneByUsesLimitOne(): void
    {
        $user = new \WP_User();
        $user->user_login = 'alice';
        $GLOBALS['wwp_test_get_users_return'] = [$user];

        $one = $this->repo->findOneBy(['search' => 'alice']);
        $this->assertSame($user, $one);
        $this->assertSame(1, $GLOBALS['wwp_test_get_users_calls'][0]['number']);
    }

    public function testFindOneByReturnsNullWhenEmpty(): void
    {
        $GLOBALS['wwp_test_get_users_return'] = [];
        $this->assertNull($this->repo->findOneBy(['search' => 'nobody']));
    }

    public function testGetClassName(): void
    {
        $this->assertSame(\WP_User::class, $this->repo->getClassName());
    }
}
