<?php
/**
 * Created by Asier Marqués <asiermarques@gmail.com>
 * Date: 9/11/16
 * Time: 22:36
 */

namespace Simettric\WPQueryBuilder;
use Simettric\WPQueryBuilder\Exception\MainMetaQueryAlreadyCreatedException;
use Simettric\WPQueryBuilder\Exception\MainTaxonomyQueryAlreadyCreatedException;


class Builder
{
    const POST_TYPE_POST       = 'post';
    const POST_TYPE_PAGE       = 'page';
    const POST_TYPE_REVISION   = 'revision';
    const POST_TYPE_ATTACHMENT = 'attachment';
    const POST_TYPE_MENU_ITEM  = 'nav_menu_item';
    const POST_TYPE_ANY        = 'any';

    private $offset=0;
    private $posts_per_page;

    private $order_by;
    private $order_direction="DESC";

    /**
     * @var array
     */
    private $parameters=array();

    private $post_types=array();

    /**
     * @var MetaQueryCollection
     */
    private $mainMetaQueryCollection=null;

    /**
     * @var TaxonomyQueryCollection
     */
    private $mainTaxonomyQueryCollection=null;

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
     * @param $limit
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->posts_per_page = $limit;
        return $this;
    }


    /**
     * @param $offset
     * @return $this
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }


    /**
     * @param string $direction
     * @return $this
     */
    public function setOrderDirection($direction="DESC")
    {
        $this->order_direction = $direction;

        return $this;
    }


    /**
     * @param $order_by
     * @return $this
     */
    public function addOrderBy($order_by)
    {
        if(false===strpos($this->order_by, $order_by))
        {
            $this->order_by = trim($this->order_by . " " . $order_by);
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
        $this->offset = 0;

        return $this;
    }

    /**
     * @param $search
     * @return $this
     */
    public function search($search)
    {
        $this->search_parameter = $search;

        return $this;
    }


    /**
     * @param $in_array array
     * @return $this
     */
    public function inPostIDs($in_array)
    {
        $in_array = !is_array($in_array) ? array($in_array) : $in_array;

        $this->in_array = $in_array;

        return $this;
    }

    /**
     * @param $in_array array
     * @return $this
     */
    public function notInPostIDs($in_array)
    {
        $in_array = !is_array($in_array) ? array($in_array) : $in_array;

        $this->not_in_array = $in_array;

        return $this;
    }


    /**
     * @param string $where_type
     * @param MetaQueryCollection|null $collection
     * @throws MainMetaQueryAlreadyCreatedException
     */
    public function createMainMetaQuery($where_type="AND", MetaQueryCollection $collection=null)
    {
        if($this->mainMetaQueryCollection)
            throw new MainMetaQueryAlreadyCreatedException();

        $this->mainMetaQueryCollection = new MetaQueryCollection($where_type);

        if($collection)
            $this->mainMetaQueryCollection->addCollection($collection);

    }

    /**
     * @param string $where_type
     * @param TaxonomyQueryCollection|null $collection
     * @throws MainTaxonomyQueryAlreadyCreatedException
     */
    public function createMainTaxonomyQuery($where_type="AND", TaxonomyQueryCollection $collection=null)
    {
        if($this->mainTaxonomyQueryCollection)
            throw new MainTaxonomyQueryAlreadyCreatedException();

        $this->mainTaxonomyQueryCollection = new TaxonomyQueryCollection($where_type);

        if($collection)
            $this->mainTaxonomyQueryCollection->addCollection($collection);

    }

    /**
     * the query builder must to return all the content types
     */
    public function setAnyPostType()
    {
        $this->post_types = static::POST_TYPE_ANY;
    }


    /**
     * @param $type
     * @return $this
     */
    public function addPostType($type)
    {
        if($this->post_types == static::POST_TYPE_ANY)
        {
            $this->post_types = array();
        }

        if(is_array($type))
        {
            foreach ($type as $value)
            {
                $this->post_types[$value] = $value;
            }

        }else{

            $this->post_types[$type] = $type;
        }

        return $this;
    }

    /**
     * @param $type
     * @return $this
     */
    public function removePostType($type)
    {
        if(isset($this->post_types[$type]))
            unset($this->post_types[$type]);

        return $this;
    }

    /**
     * @param MetaQueryCollection $collection
     * @return $this
     */
    public function addMetaQueryCollection(MetaQueryCollection $collection)
    {
        if(!$this->mainMetaQueryCollection)
            $this->createMainMetaQuery();

        $this->mainMetaQueryCollection->addCollection($collection);

        return $this;
    }


    /**
     * @param MetaQuery $metaQuery
     * @return $this
     */
    public function addMetaQuery(MetaQuery $metaQuery)
    {
        if(!$this->mainMetaQueryCollection)
            $this->createMainMetaQuery();

        $this->mainMetaQueryCollection->add($metaQuery);

        return $this;
    }

    /**
     * @param TaxonomyQueryCollection $collection
     * @return $this
     */
    public function addTaxonomyQueryCollection(TaxonomyQueryCollection $collection)
    {
        if(!$this->mainTaxonomyQueryCollection)
            $this->createMainMetaQuery();

        $this->mainTaxonomyQueryCollection->addCollection($collection);

        return $this;
    }


    /**
     * @param TaxonomyQuery $metaQuery
     * @return $this
     */
    public function addTaxonomyQuery(TaxonomyQuery $metaQuery)
    {
        if(!$this->mainTaxonomyQueryCollection)
            $this->createMainTaxonomyQuery();

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

        $this->parameters["fields"] = "ids";

        $wp_query = $this->getWPQuery();

        return $wp_query->get_posts();
    }


    /**
     * @return void
     */
    private function hydrateParametersArray()
    {
        $this->parameters["post_type"]  = $this->getPostTypeParametersArray();

        if($this->search_parameter)
        {
            $this->parameters["s"] = $this->search_parameter;
        }

        if($this->mainMetaQueryCollection)
        {
            $this->parameters["meta_query"] = $this->getMetaParametersArray($this->mainMetaQueryCollection);
        }

        if($this->mainTaxonomyQueryCollection)
        {
            $this->parameters["tax_query"] = $this->getTaxonomyParametersArray($this->mainTaxonomyQueryCollection);
        }

        $this->hydrateLimitsParameters();

        $this->hydrateOrderParameters();

        $this->hydrateInParameters();
    }


    /**
     * @param MetaQueryCollection $collection
     * @param array $return_array
     * @return array
     */
    private function getMetaParametersArray(MetaQueryCollection $collection, $return_array=array())
    {

        $return_array["relation"] = $collection->getRelationType();

        foreach ($collection as $meta)
        {
            if($meta instanceof MetaQuery)
            {
                $return_array[] = array(
                    "key"     => $meta->key,
                    "value"   => $meta->value,
                    "compare" => $meta->compare
                );

            }else if($meta instanceof MetaQueryCollection)
            {
                $return_array[] = $this->getMetaParametersArray($meta);
            }
        }

        return $return_array;
    }

    /**
     * @param TaxonomyQueryCollection $collection
     * @param array $return_array
     * @return array
     */
    private function getTaxonomyParametersArray(TaxonomyQueryCollection $collection, $return_array=array())
    {

        $return_array["relation"] = $collection->getRelationType();

        foreach ($collection as $tax)
        {
            if($tax instanceof TaxonomyQuery)
            {
                $return_array[] = array(
                    "taxonomy"         => $tax->taxonomy,
                    "field"            => $tax->field,
                    "terms"            => $tax->terms,
                    "include_children" => $tax->include_children,
                    "operator"         => $tax->operator
                );

            }else if($tax instanceof TaxonomyQueryCollection)
            {
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
        if(is_array($this->post_types))
        {
            return array_values($this->post_types);
        }

        return static::POST_TYPE_ANY;
    }


    /**
     * @return void
     */
    private function hydrateLimitsParameters()
    {

        if($this->posts_per_page)
        {
            $this->parameters["posts_per_page"] = $this->posts_per_page;
            $this->parameters["offset"]         = (int) $this->offset;
        }

    }


    /**
     * @return void
     */
    private function hydrateOrderParameters()
    {

        if($this->order_by)
        {
            $this->parameters["order_by"] = $this->order_by;
            $this->parameters["order"]    = $this->order_direction?:"DESC";
        }

    }

    /**
     * @return void
     */
    private function hydrateInParameters()
    {

        if(is_array($this->in_array))
        {
            $this->parameters["post__in"] = $this->in_array;
        }

        if(is_array($this->not_in_array))
        {
            $this->parameters["post__not_in"] = $this->not_in_array;
        }

    }

}
