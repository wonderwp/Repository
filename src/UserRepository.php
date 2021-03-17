<?php

namespace WonderWp\Component\Repository;

class UserRepository implements RepositoryInterface
{
    /**
     * @inheritDoc
     * @return \WP_User|false
     */
    public function find($id)
    {
        return get_user_by('ID', $id);
    }

    /**
     * @inheritDoc
     * @return \WP_User[]
     */
    public function findAll()
    {
        return get_users();
    }

    /**
     * @inheritDoc
     * @return \WP_User[]
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
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
     * @return \WP_User|false
     */
    public function findOneBy(array $criteria)
    {
        $users = $this->findBy($criteria, null, 1);

        if (!empty($users)) {
            return $users[0];
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function getClassName()
    {
        return \WP_User::class;
    }
}
