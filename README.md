# WPQueryBuilder
A query builder for WordPress WP_Query, inspired by the Doctrine Query Builder

[![Build Status](https://travis-ci.org/Simettric/WPQueryBuilder.svg?branch=master)](https://travis-ci.org/Simettric/WPQueryBuilder)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/63480142-e1dd-40c8-ac7c-24dd82434297/big.png)](https://insight.sensiolabs.com/projects/63480142-e1dd-40c8-ac7c-24dd82434297)


INSTALLATION
=============

    composer require simettric/wp-query-builder

USAGE
=====

### 元数据查询


获取元数据 "color" 为 "blue" 或者 "size" 为 "XL" 的所有文章类型中的文章

           $builder = new Builder();
           $wp_query = $builder->createMetaQuery("OR")
                                ->addMetaQuery(MetaQuery::create('color', 'blue'))
                                ->addMetaQuery(MetaQuery::create('size', 'XL'))
                                ->getWPQuery();
                                
                                
获取元数据 "price" 大于等于 "10" 并且 "size" 为 "XL" 的所有文章类型中的文章
               
           $builder = new Builder();
           $wp_query = $builder->createMetaQuery("AND")
                                ->addMetaQuery(MetaQuery::create('price', 10, '>=', 'NUMERIC'))
                                ->addMetaQuery(MetaQuery::create('size', 'XL'))
                                ->getWPQuery();  
                                
                                
 Retrieve any post type where post meta price is equal or greater than 10 AND (size meta value equals to XL OR post meta color value equals to blue)                              
                                
           $builder = new Builder();
           $builder->createMetaQuery("AND")
                   ->addMetaQuery(MetaQuery::create('price', 10, '>=', 'NUMERIC'));
                        
           $condition = new MetaQueryCollection('OR');
           $condition->add(MetaQuery::create('color', 'blue'))
                     ->add(MetaQuery::create('size', 'XL'));
                     
                     
           $wp_query = $builder->addMetaQueryCollection($condition)
                               ->getWPQuery();  
                               
### 自定义分类法查询


Retrieve the contents under ("pets" OR "tools") values in the "category" taxonomy AND in under 'sweet' in "custom" taxonomy

           $builder = new Builder();
           $wp_query = $builder->createTaxonomyQuery("AND")
                                ->addTaxonomyQuery(TaxonomyQuery::create('category', 'slug', array('pets', 'tools')))
                                ->addTaxonomyQuery(TaxonomyQuery::create('custom', 'slug', array('sweet')))
                                ->getWPQuery();
                                
                                
Retrieve the contents under ("pets" OR "tools") values in the "category" taxonomy BUT exclude contents in their children

           $builder = new Builder();
           $wp_query = $builder->createTaxonomyQuery("AND")
                                ->addTaxonomyQuery(TaxonomyQuery::create('category', 'slug', array('pets', 'tools'), false))
                                ->getWPQuery();
                                
Retrieve the contents those are NOT under ("pets" OR "tools") values in the "category" taxonomy

           $builder = new Builder();
           $wp_query = $builder->createTaxonomyQuery("AND")
                                ->addTaxonomyQuery(TaxonomyQuery::create('category', 'slug', array('pets', 'tools'), true, 'NOT IN'))
                                ->getWPQuery();
                                
You can have nested relations too

          $builder = new Builder();
          
          $collection = new TaxonomyQueryCollection('OR');
          $collection->add(TaxonomyQuery::create('tag', 'slug', array('cats')));
          $collection->add(TaxonomyQuery::create('custom', 'slug', array('sweet')));
          
          $wp_query = $builder->createTaxonomyQuery("AND")
                               ->addTaxonomyQuery(TaxonomyQuery::create('category', 'slug', array('pets', 'tools')))
                               ->addTaxonomyQueryCollection($collection)
                               ->getWPQuery();     
                                                       

### 文章类型

Retrieve all PAGES

           $builder = new Builder();
           $wp_query = $builder->addPostType(Builder::POST_TYPE_PAGE)
                               ->getWPQuery();
           
Retrieve all CUSTOM POST TYPE

           $builder = new Builder();
           $wp_query = $builder->addPostType('your_custom')
                               ->getWPQuery();
           
Retrieve all CUSTOM POST TYPE and PAGES

           $builder = new Builder();
           $wp_query = $builder->addPostType('your_custom')
                               ->addPostType(Builder::POST_TYPE_PAGE)
                               ->getWPQuery();
                               
    
### 搜索

Search contents

            $builder = new Builder();
    
            $wp_query = $builder->search("search query")
                                ->getWPQuery();

     
### IN and NOT IN

Retrieve contents with ID in array of IDS

            $builder = new Builder();
    
            $wp_query = $builder->inPostIDs(array(1,2,3))
                                ->getWPQuery();
            
Retrieve contents with ID not in array of IDS

            $builder = new Builder();
    
            $wp_query = $builder->notInPostIDs(array(1,2,3))
                                ->getWPQuery();
      
      
### ORDERBY

Order contents by title descending

            $builder = new Builder();
    
            $wp_query = $builder->setOrderBy("title")
                                ->getWPQuery();
            
           
Order contents by date, ascending

            $builder = new Builder();
    
            $wp_query = $builder->setOrderBy("date")
                                ->setOrderDirection("ASC")
                                ->getWPQuery();
                                
                                
Order contents by title descending and date, ascending

            $builder = new Builder();
    
            $wp_query = $builder->addOrderBy("title", "DESC")
                                ->addOrderBy("date", "ASC")
                                ->getWPQuery();
                                
                                
Order contents by custom meta

            $builder = new Builder();
    
            $wp_query = $builder->setOrderByMeta("color", "DESC")
                                ->getWPQuery();
            
            
Order contents by custom numeric meta

            $builder = new Builder();
    
            $wp_query = $builder->setOrderByMeta("price", "ASC", true)
                                ->getWPQuery();
            
            
### LIMITS AND OFFSETS

Retrieve only 10 contents

            $builder = new Builder();
    
            $wp_query = $builder->setLimit(10)
                                ->getWPQuery();
            
           
Retrieve 20 contents starting from the 10th position

            $builder = new Builder();
    
            $wp_query = $builder->setLimit(20)
                                ->setOffset(10)
                                ->getWPQuery();

### RETRIEVING


Get the WPQuery object

            $builder = new Builder();
    
            $wp_query = $builder->getWPQuery();
            
            
Get the Posts array

            $builder = new Builder();
    
            $posts = $builder->getPosts();
            
            
Get the WPQuery parameters array

            $builder = new Builder();
    
            $params = $builder->getParameters();
            
            $query = new WP_Query($params);
            
  
Get an array containing only the post IDs. This is useful when you want to return all records without pagination from a large recordset in order to avoid memory issues.


            $builder = new Builder();
    
            $ids = $builder->getPostIDsOnly();
            
            $builder = new Builder();
            
            $wp_query = $builder->inPostIDs($ids)
                                ->getWPQuery();


