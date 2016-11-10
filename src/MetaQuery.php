<?php
/**
 * Created by Asier Marqués <asiermarques@gmail.com>
 * Date: 9/11/16
 * Time: 23:47
 */

namespace Simettric\WPQueryBuilder;


class MetaQuery
{

    public $key;

    public $value;

    public $compare;

    public $type;


    /**
     * @param $key
     * @param $value
     * @param string $compare ( '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'EXISTS' and 'NOT EXISTS')
     * @param string $type ('NUMERIC', 'BINARY', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED', You can also specify precision and scale for the 'DECIMAL' and 'NUMERIC' types (for example, 'DECIMAL(10,5)' or 'NUMERIC(10)' are valid)
     * @return MetaQuery
     */
    static function create($key, $value, $compare="=", $type="CHAR")
    {
        $instance = new MetaQuery();
        $instance->key     = $key;
        $instance->value   = $value;
        $instance->compare = $compare;
        $instance->type    = $type;

        return $instance;
    }




}