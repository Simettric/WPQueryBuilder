<?php
/**
 * Created by Asier Marqués <asiermarques@gmail.com>
 * Date: 9/11/16
 * Time: 23:47
 */

namespace Wenprise\WPQueryBuilder;


class TaxonomyQuery
{

    public $taxonomy;

    public $field;

    public $terms = [];

    public $include_children = true;

    /**
     * @var string string ('IN', 'NOT IN', 'AND', 'EXISTS' and 'NOT EXISTS')
     */
    public $operator = "IN";


    /**
     * @param        $taxonomy
     * @param        $field
     * @param        $terms
     * @param bool   $include_children
     * @param string $operator ('IN', 'NOT IN', 'AND', 'EXISTS' and 'NOT EXISTS')
     *
     * @return TaxonomyQuery
     */
    public static function create($taxonomy, $field, $terms = [], $include_children = true, $operator = "IN")
    {

        if ( ! is_array($terms)) {
            $terms = [$terms];
        }

        $instance                   = new TaxonomyQuery();
        $instance->taxonomy         = $taxonomy;
        $instance->field            = $field;
        $instance->terms            = $terms;
        $instance->include_children = $include_children;
        $instance->operator         = $operator;

        return $instance;
    }


}
