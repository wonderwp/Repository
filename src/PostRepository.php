<?php

namespace WonderWp\Component\Repository;

use function WonderWp\Functions\array_merge_recursive_distinct;

class PostRepository implements RepositoryInterface
{
    const POST_TYPE = 'post';

    /**
     * @inheritDoc
     */
    public function find(mixed $id): ?\WP_Post
    {
        $post = get_post($id);

        return $post instanceof \WP_Post ? $post : null;
    }

    /**
     * @inheritDoc
     * @return \WP_Post[]
     */
    public function findAll(): array
    {
        $criteria = [
            'numberposts' => -1,
            'post_type'   => static::POST_TYPE,
        ];

        return get_posts($criteria);
    }

    /**
     * @inheritDoc
     * @return \WP_Post[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {

        if (empty($criteria['post_type'])) {
            $criteria['post_type'] = static::POST_TYPE;
        }

        if (!empty($limit)) {
            $criteria['numberposts'] = $limit;
        } else {
            $criteria['numberposts'] = -1;
        }
        if (!empty($offset)) {
            $criteria['offset'] = $offset;
        }

        if (!empty($orderBy)) {
            if (count($orderBy) == 1) {
                //There's just one criteria, split into orderby and order
                $orderbyKeys = array_keys($orderBy);
                $orderKeys = array_values($orderBy);
                $criteria['orderby'] = reset($orderbyKeys);
                $criteria['order'] = reset($orderKeys);
            } else {
                $criteria['orderby'] = $orderBy;
            }
        }

        return get_posts($criteria);
    }

    /**
     * @inheritDoc
     */
    public function findOneBy(array $criteria): ?\WP_Post
    {
        $posts = $this->findBy($criteria, null, 1);

        return $posts[0] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getClassName(): string
    {
        return \WP_Post::class;
    }

    /**
     * Count items for given criteria.
     *
     * @param  array $criteria
     *
     * @return integer
     */
    public function countBy(array $criteria): int
    {
        // TODO - Optimize
        return count($this->findBy($criteria));
    }

    public function getTermsForTaxonomy(array $args = []): array|\WP_Error
    {
        return get_terms($args);
    }

    /**
     * Find related random post (by same category)
     *
     * @param \WP_Post $currentPost
     * @param string   $taxonomyName
     * @param array    $args
     *
     * @return \WP_Post[]
     */
    public function findRandomRelated(\WP_Post $currentPost, string $taxonomyName, array $args = []): array
    {
        $defaultCriteria = [
            "post_type"   => static::POST_TYPE,
            "post_status" => "publish",
            'exclude'     => [$currentPost->ID],
            'orderby'     => 'rand',
            'order'       => 'ASC',
        ];

        $categories = get_the_terms($currentPost, 'pml_cat');
        if (is_array($categories) && count($categories) > 0) {
            $categoryQuery = [
                'taxonomy'         => PMLManager::PML_CUSTOM_TAXO_CATEGORY,
                'field'            => 'id',
                'include_children' => false,
            ];

            foreach ($categories as $category) {
                $categoryQuery['terms'][] = $category->term_id;
            }

            $defaultCriteria['tax_query'][] = $categoryQuery;
        }
        $criteria = array_merge_recursive_distinct($defaultCriteria, $args);

        return get_posts($criteria);
    }
}
