<?php
/***
 * Author: chen ray
 * Email: chenraygogo@gmail.com
 *
 **/

namespace App\Controllers;


use CodeIgniter\HTTP\ResponseInterface;

class Week extends BaseApi
{
    public function index()
    {
        $db = db_connect();
        $builder = $db->table('weeks');
        $builder->select('id, display, year, week');
        $builder->orderBy('id', 'desc');
        $builder->limit(10);
        $query   = $builder->get();  // Produces: SELECT * FROM mytable
        $results    = $query->getResult();
        return $this->respond($results);
    }

    public function publicationIds(): ResponseInterface
    {
        $db = db_connect();
        $builder = $db->table('rankings');
        $builder->select('ranking_publication_id');
        $builder->distinct();
        $builder->orderBy('ranking_publication_id', 'desc');
        $query   = $builder->get();  // Produces: SELECT * FROM mytable
        $results    = $query->getResult();
        return $this->respond($results);
    }
}