<?php

namespace WonderWp\Component\Repository;

class UserRepository implements RepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function find(mixed $id): ?\WP_User
    {
        $user = get_user_by('ID', $id);

        return $user instanceof \WP_User ? $user : null;
    }

    /**
     * @inheritDoc
     * @return \WP_User[]
     */
    public function findAll(): array
    {
        return get_users();
    }

    /**
     * @inheritDoc
     * @return \WP_User[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        $args = $criteria;

        if (!empty($limit)) {
            $args['number'] = $limit;
        }

        if (!empty($offset)) {
            $args['offset'] = $offset;
        }

        if (!empty($orderBy)) {
            $args = array_merge($args, $orderBy);
        }

        return get_users($args);
    }

    /**
     * @inheritDoc
     */
    public function findOneBy(array $criteria): ?\WP_User
    {
        $users = $this->findBy($criteria, null, 1);

        if (!empty($users)) {
            return $users[0];
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getClassName(): string
    {
        return \WP_User::class;
    }
}
