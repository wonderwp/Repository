<?php

namespace WonderWp\Component\Repository;

class TaxonomyRepository implements RepositoryInterface
{
    const TAXONOMY_NAME = 'category';

    public function find(mixed $id): ?object
    {
        $term = get_term($id, static::TAXONOMY_NAME);

        if ($term instanceof \WP_Term) {
            return $term;
        }

        // Preserve WP_Error for callers that inspect it; otherwise null.
        return $term instanceof \WP_Error ? $term : null;
    }

    public function findAll(): array
    {
        return $this->findBy([]);
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        $criteria['taxonomy'] = static::TAXONOMY_NAME;

        if ($limit !== null) {
            $criteria['number'] = $limit;
        }
        if ($offset !== null) {
            $criteria['offset'] = $offset;
        }
        if (!empty($orderBy)) {
            $criteria = array_merge($criteria, $orderBy);
        }

        $terms = get_terms($criteria);

        return is_array($terms) ? $terms : [];
    }

    public function findOneBy(array $criteria): ?object
    {
        $terms = $this->findBy($criteria, null, 1);

        return $terms[0] ?? null;
    }

    public function getClassName(): string
    {
        return \WP_Term::class;
    }
}
