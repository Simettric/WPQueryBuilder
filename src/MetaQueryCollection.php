<?php
/**
 * Created by Asier Marqués <asiermarques@gmail.com>
 * Date: 10/11/16
 * Time: 0:06
 */

namespace Simettric\WPQueryBuilder;


class MetaQueryCollection implements \Iterator
{

    private $position     = 0;
    private $meta_queries = array();

    private $where_type_relation;


    public function __construct($where_type="AND")
    {
        $this->where_type_relation = $where_type;
    }

    /**
     * @return string
     */
    public function getRelationType()
    {
        return $this->where_type_relation;
    }

    /**
     * @param MetaQuery $query
     */
    public function add(MetaQuery $query)
    {
        $this->meta_queries[] = $query;
    }

    /**
     * @param MetaQueryCollection $collection
     */
    public function addCollection(MetaQueryCollection $collection)
    {
        $this->meta_queries[] = $collection;
    }


    /**
     * @return mixed
     */
    public function current()
    {
        return $this->meta_queries[$this->position];
    }

    /**
     * @return mixed
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * @return mixed
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @return mixed
     */
    public function valid()
    {
        return isset($this->meta_queries[$this->position]);
    }

    /**
     * @return mixed
     */
    public function rewind()
    {
        $this->position = 0;
    }
}