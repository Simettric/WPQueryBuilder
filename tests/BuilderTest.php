<?php

namespace Simettric\WPQueryBuilder\Test;
use Simettric\WPQueryBuilder\Builder;
use Simettric\WPQueryBuilder\MetaQuery;
use Simettric\WPQueryBuilder\MetaQueryCollection;

/**
 * Created by Asier Marqués <asiermarques@gmail.com>
 * Date: 10/11/16
 * Time: 1:09
 */
class BuilderTest extends \PHPUnit_Framework_TestCase
{


    function testMetaParameters()
    {
        $builder = new Builder();
        $builder->createMainMetaQuery();
        $builder->addMetaQuery(MetaQuery::create('test', 'value_test'));

        $parameters = $builder->getParameters();

        $this->assertArrayHasKey('meta_query', $parameters);

        $this->assertEquals("AND", $parameters["meta_query"]["relation"]);
        $this->assertEquals("test", $parameters["meta_query"][0]["key"]);

        $collection = new MetaQueryCollection('OR');
        $collection->add(MetaQuery::create('test', 'value_test'));
        $builder->addMetaQueryCollection($collection);

        $parameters = $builder->getParameters();

        $this->assertEquals("OR", $parameters["meta_query"][1]["relation"]);

    }

}
