<?php

namespace Simettric\WPQueryBuilder\Test;
use Simettric\WPQueryBuilder\Builder;
use Simettric\WPQueryBuilder\MetaQuery;
use Simettric\WPQueryBuilder\MetaQueryCollection;
use Simettric\WPQueryBuilder\TaxonomyQuery;
use Simettric\WPQueryBuilder\TaxonomyQueryCollection;

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

        $builder->addPostType([Builder::POST_TYPE_PAGE,Builder::POST_TYPE_POST]);
        $parameters = $builder->getParameters();

        $this->assertCount(2, $parameters["post_type"]);
        $this->assertContains(Builder::POST_TYPE_POST, $parameters["post_type"]);
        $this->assertContains(Builder::POST_TYPE_PAGE, $parameters["post_type"]);


    }

    public function testLimitsParameters()
    {
        $builder = new Builder();

        $parameters = $builder->getParameters();

        $this->assertArrayNotHasKey('posts_per_page', $parameters);

        $builder->setLimit(10);

        $parameters = $builder->getParameters();
        $this->assertEquals(10, $parameters["posts_per_page"]);
        $this->assertEquals(0, $parameters["offset"]);

        $builder->setLimit(8);
        $builder->setOffset(2);
        $parameters = $builder->getParameters();

        $this->assertEquals(8, $parameters["posts_per_page"]);
        $this->assertEquals(2, $parameters["offset"]);

        $builder->withAnyLimit();
        $parameters = $builder->getParameters();

        $this->assertEquals(-1, $parameters["posts_per_page"]);
        $this->assertEquals(0, $parameters["offset"]);
    }

    public function testOrderParameters()
    {
        $builder = new Builder();

        $parameters = $builder->addOrderBy('date')->getParameters();

        $this->assertArrayHasKey('order', $parameters);
        $this->assertArrayHasKey('orderby', $parameters);
        $this->assertEquals("DESC", $parameters["order"]);
        $this->assertEquals("date", $parameters["orderby"]);

        $parameters = $builder->addOrderBy('title')->setOrderDirection("ASC")->getParameters();

        $this->assertEquals("ASC", $parameters["order"]);
        $this->assertEquals("date title", $parameters["orderby"]);
    }

    public function testTaxonomyQueryParameter()
    {
        $builder = new Builder();
        $builder->createMainTaxonomyQuery();
        $builder->addTaxonomyQuery(TaxonomyQuery::create('category', 'slug', array('blue')));

        $parameters = $builder->getParameters();

        $this->assertArrayHasKey('tax_query', $parameters);

        $this->assertEquals("AND", $parameters["tax_query"]["relation"]);
        $this->assertEquals("category", $parameters["tax_query"][0]["taxonomy"]);
        $this->assertEquals("slug", $parameters["tax_query"][0]["field"]);
        $this->assertEquals("blue", $parameters["tax_query"][0]["terms"][0]);

        $collection = new TaxonomyQueryCollection('OR');
        $collection->add(TaxonomyQuery::create('tag', 'slug', array('pets')));
        $builder->addTaxonomyQueryCollection($collection);

        $parameters = $builder->getParameters();

        $this->assertEquals("OR", $parameters["tax_query"][1]["relation"]);

    }


    public function testSearchParameter()
    {
        $builder = new Builder();

        $parameters = $builder->search('test')->getParameters();

        $this->assertArrayHasKey('s', $parameters);
        $this->assertEquals("test", $parameters["s"]);
    }

    public function testInParameters()
    {
        $builder = new Builder();

        $parameters = $builder->inPostIDs(array(1, 2))->getParameters();

        $this->assertArrayHasKey('post__in', $parameters);
        $this->assertContains(1, $parameters["post__in"]);

        $parameters = $builder->notInPostIDs(array(3))->getParameters();

        $this->assertArrayHasKey('post__not_in', $parameters);
        $this->assertContains(3, $parameters["post__not_in"]);
    }

}
