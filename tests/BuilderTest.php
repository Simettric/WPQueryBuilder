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


    public function testMetaParameters()
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

    public function testPostTypeParameters()
    {
        $builder = new Builder();

        $parameters = $builder->getParameters();

        $this->assertArrayHasKey('post_type', $parameters);

        $this->assertContains(Builder::POST_TYPE_ANY, $parameters["post_type"]);


        $builder->addPostType(Builder::POST_TYPE_PAGE);
        $builder->addPostType(Builder::POST_TYPE_POST);
        $parameters = $builder->getParameters();

        $this->assertCount(2, $parameters["post_type"]);

        $builder->removePostType(Builder::POST_TYPE_PAGE);
        $parameters = $builder->getParameters();

        $this->assertCount(1, $parameters["post_type"]);

        $this->assertContains(Builder::POST_TYPE_POST, $parameters["post_type"]);


        $builder->setAnyPostType();

        $parameters = $builder->getParameters();

        $this->assertEquals(Builder::POST_TYPE_ANY, $parameters["post_type"]);


    }

}
