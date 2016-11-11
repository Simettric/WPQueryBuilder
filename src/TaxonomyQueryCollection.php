<?php
/**
 * Created by Asier Marqués <asiermarques@gmail.com>
 * Date: 10/11/16
 * Time: 0:06
 */

namespace Simettric\WPQueryBuilder;


class TaxonomyQueryCollection implements \Iterator
{

    private $position     = 0;
    private $tax_queries = array();

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
     * @param TaxonomyQuery $query
     * @return $this
     */
    public function add(TaxonomyQuery $query)
    {
        $this->tax_queries[] = $query;

        return $this;
    }

    /**
     * @param TaxonomyQueryCollection $collection
     */
    public function addCollection(TaxonomyQueryCollection $collection)
    {
        $this->tax_queries[] = $collection;
    }


    /**
     * @return mixed
     */
    public function current()
    {
        return $this->tax_queries[$this->position];
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
        return isset($this->tax_queries[$this->position]);
    }

    /**
     * @return mixed
     */
    public function rewind()
    {
        $this->position = 0;
    }
}
