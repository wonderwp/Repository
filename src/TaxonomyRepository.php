<?php

namespace WonderWp\Component\Repository;

class TaxonomyRepository implements RepositoryInterface
{
    const TAXONOMY_NAME = 'category';

    public function find($id)
    {
        return get_term($id, static::TAXONOMY_NAME);
    }

    public function findAll()
    {
        $criteria['taxonomy'] = static::TAXONOMY_NAME;

        return get_terms($criteria);
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $criteria['taxonomy'] = static::TAXONOMY_NAME;

        return get_terms($criteria);
    }

    public function findOneBy(array $criteria)
    {
        // TODO: Implement findOneBy() method.
    }

    public function getClassName()
    {
        return \WP_Term::class;
    }
}

