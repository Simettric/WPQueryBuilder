<?php
/**
 * Created by Asier Marqués <asiermarques@gmail.com>
 * Date: 9/11/16
 * Time: 22:36
 */

namespace Simettric\WPQueryBuilder;
use Simettric\WPQueryBuilder\Exception\MainMetaQueryAlreadyCreatedException;


class Builder
{
    const POST_TYPE_POST       = 'post';
    const POST_TYPE_PAGE       = 'page';
    const POST_TYPE_REVISION   = 'revision';
    const POST_TYPE_ATTACHMENT = 'attachment';
    const POST_TYPE_MENU_ITEM  = 'nav_menu_item';
    const POST_TYPE_ANY        = 'any';

    /**
     * @var array
     */
    private $parameters=array();

    private $post_types=array();

    /**
     * @var MetaQueryCollection
     */
    private $mainMetaQueryCollection=null;


    public function __construct()
    {
        $this->post_types = static::POST_TYPE_ANY;
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
     * @return void
     */
    private function hydrateParametersArray()
    {
        $this->parameters["post_type"]  = $this->getPostTypeParametersArray();

        if($this->mainMetaQueryCollection)
        {
            $this->parameters["meta_query"] = $this->getMetaParametersArray($this->mainMetaQueryCollection);
        }
    }


    /**
     * @param MetaQueryCollection $collection
     * @param array $meta_array
     * @return array
     */
    private function getMetaParametersArray(MetaQueryCollection $collection, $meta_array=array())
    {

        $meta_array["relation"] = $collection->getRelationType();

        foreach ($collection as $meta)
        {
            if($meta instanceof MetaQuery)
            {
                $meta_array[] = array(
                    "key"     => $meta->key,
                    "value"   => $meta->value,
                    "compare" => $meta->compare
                );

            }else if($meta instanceof MetaQueryCollection)
            {
                $meta_array[] = $this->getMetaParametersArray($meta);
            }
        }

        return $meta_array;
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

}
