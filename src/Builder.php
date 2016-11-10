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
    /**
     * @var array
     */
    private $parameters=array();

    /**
     * @var MetaQueryCollection
     */
    private $mainMetaQueryCollection=null;


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
     * @param MetaQueryCollection $collection
     */
    public function addMetaQueryCollection(MetaQueryCollection $collection)
    {
        if(!$this->mainMetaQueryCollection)
            $this->createMainMetaQuery();

        $this->mainMetaQueryCollection->addCollection($collection);
    }

    /**
     * @param MetaQuery $metaQuery
     */
    public function addMetaQuery(MetaQuery $metaQuery)
    {
        if(!$this->mainMetaQueryCollection)
            $this->createMainMetaQuery();

        $this->mainMetaQueryCollection->add($metaQuery);
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
        $this->parameters["meta_query"] = $this->hydrateMetaParametersArray($this->mainMetaQueryCollection);
    }


    /**
     * @param MetaQueryCollection $collection
     * @param array $meta_array
     * @return array
     */
    private function hydrateMetaParametersArray(MetaQueryCollection $collection, $meta_array=array())
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
                $meta_array[] = $this->hydrateMetaParametersArray($meta);
            }
        }

        return $meta_array;
    }

}
