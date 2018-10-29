<?php
/**
 * Created by Asier Marqués <asiermarques@gmail.com>
 * Date: 9/11/16
 * Time: 22:36
 */

namespace Wenprise\WPQueryBuilder;

use Collections\Exceptions\Exception;
use Wenprise\WPQueryBuilder\Exception\MainMetaQueryAlreadyCreatedException;
use Wenprise\WPQueryBuilder\Exception\MainTaxonomyQueryAlreadyCreatedException;


class Builder
{
    const POST_TYPE_POST = 'post';
    const POST_TYPE_PAGE = 'page';
    const POST_TYPE_REVISION = 'revision';
    const POST_TYPE_ATTACHMENT = 'attachment';
    const POST_TYPE_MENU_ITEM = 'nav_menu_item';
    const POST_TYPE_ANY = 'any';
    const POST_STATUS_ANY = 'any';

    const POST_STATUS_PUBLISHED = 'publish';
    const POST_STATUS_DRAFT = 'draft';

    private $offset = 0;
    private $posts_per_page;

    private $order_by;
    private $order_direction = "DESC";

    /**
     * @var array
     */
    private $parameters = [];

    private $post_types = [];

    private $post_status = [];

    private $author = false;

    private $meta_key_order = false;
    private $meta_key_order_numeric = false;

    /**
     * @var MetaQueryCollection
     */
    private $mainMetaQueryCollection = null;

    /**
     * @var TaxonomyQueryCollection
     */
    private $mainTaxonomyQueryCollection = null;

    /**
     * @var string
     */
    private $search_parameter;

    /**
     * @var array|null
     */
    private $in_array;

    /**
     * @var array|null
     */
    private $not_in_array;


    public function __construct()
    {
        $this->post_types = static::POST_TYPE_ANY;
    }


    /**
     * @param $author_id
     *
     * @return $this
     */
    public function setAuthor($author_id)
    {
        $this->author = $author_id;

        return $this;
    }


    /**
     * @param $limit
     *
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->posts_per_page = $limit;

        return $this;
    }


    /**
     * @param $offset
     *
     * @return $this
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;

        return $this;
    }


    /**
     * @param string $direction
     *
     * @return $this
     */
    public function setOrderDirection($direction = "DESC")
    {
        $this->order_direction = $direction;

        return $this;
    }

    /**
     * @param $order_by
     *
     * @return $this
     */
    public function setOrderBy($order_by)
    {
        $this->order_by = $order_by;

        return $this;
    }

    /**
     * @param $order_by
     *
     * @return $this
     */
    public function addOrderBy($order_by, $direction = "DESC")
    {
        if ( ! $this->order_by) {
            $this->order_by = [];
        }

        if ($this->order_by && ! is_array($this->order_by)) {
            $this->order_by        = [(string)$this->order_by => $this->order_direction];
            $this->order_direction = null;
        }

        $this->order_by[ $order_by ] = $direction;

        return $this;
    }


    /**
     * @param      $meta_key
     * @param bool $numeric
     *
     * @return $this
     */
    public function setOrderByMeta($meta_key, $direction = "DESC", $numeric = false)
    {
        if ($this->meta_key_order || $this->meta_key_order_numeric) {
            throw new \Exception("You only can order by one meta key");
        }

        if ($numeric) {

            $this->meta_key_order_numeric       = $meta_key;
            $this->order_by[ "meta_value_num" ] = $direction;
        } else {
            $this->meta_key_order           = $meta_key;
            $this->order_by[ "meta_value" ] = $direction;
        }


        return $this;
    }

    /**
     * The query builder must to return all the content
     * @return $this
     */
    public function withAnyLimit()
    {
        $this->posts_per_page = -1;
        $this->offset         = 0;

        return $this;
    }

    /**
     * @param $search
     *
     * @return $this
     */
    public function search($search)
    {
        $this->search_parameter = $search;

        return $this;
    }


    /**
     * @param $in_array array
     *
     * @return $this
     */
    public function inPostIDs($in_array)
    {
        $in_array = ! is_array($in_array) ? [$in_array] : $in_array;

        $this->in_array = $in_array;

        return $this;
    }

    /**
     * @param $in_array array
     *
     * @return $this
     */
    public function notInPostIDs($in_array)
    {
        $in_array = ! is_array($in_array) ? [$in_array] : $in_array;

        $this->not_in_array = $in_array;

        return $this;
    }


    /**
     * @param string                   $where_type
     * @param MetaQueryCollection|null $collection
     *
     * @return $this
     * @throws MainMetaQueryAlreadyCreatedException
     */
    public function createMetaQuery($where_type = "AND", MetaQueryCollection $collection = null)
    {
        if ($this->mainMetaQueryCollection) {
            throw new MainMetaQueryAlreadyCreatedException();
        }

        $this->mainMetaQueryCollection = new MetaQueryCollection($where_type);

        if ($collection) {
            $this->mainMetaQueryCollection->addCollection($collection);
        }

        return $this;

    }


    /**
     * @param string                       $where_type
     * @param TaxonomyQueryCollection|null $collection
     *
     * @return $this
     * @throws MainTaxonomyQueryAlreadyCreatedException
     */
    public function createTaxonomyQuery($where_type = "AND", TaxonomyQueryCollection $collection = null)
    {
        if ($this->mainTaxonomyQueryCollection) {
            throw new MainTaxonomyQueryAlreadyCreatedException();
        }

        $this->mainTaxonomyQueryCollection = new TaxonomyQueryCollection($where_type);

        if ($collection) {
            $this->mainTaxonomyQueryCollection->addCollection($collection);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function setAnyPostType()
    {
        $this->post_types = static::POST_TYPE_ANY;

        return $this;
    }

    /**
     * @return $this
     */
    public function setAnyPostStatus()
    {
        $this->post_status = static::POST_STATUS_ANY;

        return $this;
    }

    /**
     * @param $type
     *
     * @return $this
     */
    public function addPostType($type)
    {
        if ($this->post_types == static::POST_TYPE_ANY) {
            $this->post_types = [];
        }

        if (is_array($type)) {
            foreach ($type as $value) {
                $this->post_types[ $value ] = $value;
            }

        } else {

            $this->post_types[ $type ] = $type;
        }

        return $this;
    }


    /**
     * @param $status
     *
     * @return $this
     */
    public function addPostStatus($status)
    {
        if ($this->post_status == static::POST_STATUS_ANY) {
            $this->post_status = [];
        }

        if (is_array($status)) {
            foreach ($status as $value) {
                $this->post_status[ $value ] = $value;
            }

        } else {

            $this->post_status[ $status ] = $status;
        }

        return $this;
    }

    /**
     * @param $type
     *
     * @return $this
     */
    public function removePostType($type)
    {
        if (isset($this->post_types[ $type ])) {
            unset($this->post_types[ $type ]);
        }

        return $this;
    }

    /**
     * @param MetaQueryCollection $collection
     *
     * @return $this
     */
    public function addMetaQueryCollection(MetaQueryCollection $collection)
    {
        if ( ! $this->mainMetaQueryCollection) {
            $this->createMetaQuery();
        }

        $this->mainMetaQueryCollection->addCollection($collection);

        return $this;
    }


    /**
     * @param MetaQuery $metaQuery
     *
     * @return $this
     */
    public function addMetaQuery(MetaQuery $metaQuery)
    {
        if ( ! $this->mainMetaQueryCollection) {
            $this->createMetaQuery();
        }

        $this->mainMetaQueryCollection->add($metaQuery);

        return $this;
    }

    /**
     * @param TaxonomyQueryCollection $collection
     *
     * @return $this
     */
    public function addTaxonomyQueryCollection(TaxonomyQueryCollection $collection)
    {
        if ( ! $this->mainTaxonomyQueryCollection) {
            $this->createMetaQuery();
        }

        $this->mainTaxonomyQueryCollection->addCollection($collection);

        return $this;
    }


    /**
     * @param TaxonomyQuery $metaQuery
     *
     * @return $this
     */
    public function addTaxonomyQuery(TaxonomyQuery $metaQuery)
    {
        if ( ! $this->mainTaxonomyQueryCollection) {
            $this->createTaxonomyQuery();
        }

        $this->mainTaxonomyQueryCollection->add($metaQuery);

        return $this;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        $this->hydrateParametersArray();

        return $this->parameters;
    }

    /**
     * @return \WP_Query
     */
    public function getWPQuery()
    {
        return new \WP_Query($this->getParameters());
    }


    /**
     * @return array
     */
    public function getPosts()
    {
        $wp_query = $this->getWPQuery();

        return $wp_query->get_posts();
    }

    /**
     * @return array
     */
    public function getPostIDs()
    {
        $this->hydrateParametersArray();

        $this->parameters[ "fields" ] = "ids";

        $wp_query = $this->getWPQuery();

        return $wp_query->get_posts();
    }


    /**
     * @return void
     */
    private function hydrateParametersArray()
    {
        $this->parameters[ "post_type" ]   = $this->getPostTypeParametersArray();
        $this->parameters[ "post_status" ] = $this->getPostStatusParametersArray();

        if ($this->author) {
            $this->parameters[ "author" ] = $this->author;
        }

        if ($this->search_parameter) {
            $this->parameters[ "s" ] = $this->search_parameter;
        }

        if ($this->mainMetaQueryCollection) {
            $this->parameters[ "meta_query" ] = $this->getMetaParametersArray($this->mainMetaQueryCollection);
        }

        if ($this->mainTaxonomyQueryCollection) {
            $this->parameters[ "tax_query" ] = $this->getTaxonomyParametersArray($this->mainTaxonomyQueryCollection);
        }

        $this->hydrateLimitsParameters();

        $this->hydrateOrderParameters();

        $this->hydrateInParameters();
    }


    /**
     * @param MetaQueryCollection $collection
     * @param array               $return_array
     *
     * @return array
     */
    private function getMetaParametersArray(MetaQueryCollection $collection, $return_array = [])
    {

        $return_array[ "relation" ] = $collection->getRelationType();

        foreach ($collection as $meta) {
            if ($meta instanceof MetaQuery) {
                $return_array[] = [
                    "key"     => $meta->key,
                    "value"   => $meta->value,
                    "compare" => $meta->compare,
                    "type"    => $meta->type,
                ];

            } elseif ($meta instanceof MetaQueryCollection) {
                $return_array[] = $this->getMetaParametersArray($meta);
            }
        }

        return $return_array;
    }

    /**
     * @param TaxonomyQueryCollection $collection
     * @param array                   $return_array
     *
     * @return array
     */
    private function getTaxonomyParametersArray(TaxonomyQueryCollection $collection, $return_array = [])
    {

        $return_array[ "relation" ] = $collection->getRelationType();

        foreach ($collection as $tax) {
            if ($tax instanceof TaxonomyQuery) {
                $return_array[] = [
                    "taxonomy"         => $tax->taxonomy,
                    "field"            => $tax->field,
                    "terms"            => $tax->terms,
                    "include_children" => $tax->include_children,
                    "operator"         => $tax->operator,
                ];

            } elseif ($tax instanceof TaxonomyQueryCollection) {
                $return_array[] = $this->getTaxonomyParametersArray($tax);
            }
        }

        return $return_array;
    }


    /**
     * @return array|string
     */
    private function getPostTypeParametersArray()
    {
        if (is_array($this->post_types)) {
            return array_values($this->post_types);
        }

        return static::POST_TYPE_ANY;
    }


    /**
     * @return array|string
     */
    private function getPostStatusParametersArray()
    {
        if (is_array($this->post_status)) {
            return array_values($this->post_status);
        }

        return static::POST_STATUS_ANY;
    }

    /**
     * @return void
     */
    private function hydrateLimitsParameters()
    {

        if ($this->posts_per_page) {
            $this->parameters[ "posts_per_page" ] = $this->posts_per_page;
            $this->parameters[ "offset" ]         = (int)$this->offset;
        }

    }


    /**
     * @return void
     */
    private function hydrateOrderParameters()
    {

        if ($this->order_by) {

            $this->parameters[ "orderby" ] = $this->order_by;
            if ( ! is_array($this->order_by)) {
                $this->parameters[ "order" ] = $this->order_direction ? $this->order_direction : "DESC";
            }
        }

        if ($this->meta_key_order || $this->meta_key_order_numeric) {

            if ($this->meta_key_order) {
                $this->parameters[ "meta_key" ] = $this->meta_key_order;

            } else {
                $this->parameters[ "meta_key" ] = $this->meta_key_order_numeric;
            }

        }

    }

    /**
     * @return void
     */
    private function hydrateInParameters()
    {

        if (is_array($this->in_array)) {
            $this->parameters[ "post__in" ] = $this->in_array;
        }

        if (is_array($this->not_in_array)) {
            $this->parameters[ "post__not_in" ] = $this->not_in_array;
        }

    }

}
