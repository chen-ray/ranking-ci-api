<?php
/***
 * Author: chen ray
 * Email: chenraygogo@gmail.com
 *
 **/

namespace App\Controllers;


use CodeIgniter\Pager\Pager;
use CodeIgniter\RESTful\ResourceController;

class BaseApi extends ResourceController
{
    protected array $countries;
    public function __construct()
    {
        $this->getCountries();
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
    }

    protected function pageMeta(Pager $pager): array
    {
        //current: 0, current_page: 0, from: 0, last: 0, last_page: 0, links: [], next: 0,
        // path: "", per_page: 0, prev: 0, to: 0, total: 0
        return [
            'current'       => $pager->getCurrentPage(),
            'current_page'  => $pager->getCurrentPage(),
            //'from'          => $pager->get(),
            'last'          => $pager->getLastPage(),
            'next'          => $pager->getCurrentPage() + 1,
            'per_page'      => $pager->getPerPage(),
            'prev'          => $pager->getPerPage(),
            'total'         => $pager->getTotal(),
        ];
    }

    /**
     * @param $page int current page
     * @param $lastPage int
     * @param $offset int
     * @param $perPage int
     * @param $total int total rows
     * @return array
     */
    protected function makeMeta(int $page, int $lastPage, int $offset, int $perPage, int $total): array
    {
        return [
            'prev'      => $page > 1 ? $page - 1 : 1,
            'current'   => $page,
            'current_page'   => $page,
            'next'      => $page < $lastPage ?  $page + 1 : $lastPage,
            'last'      => $lastPage,
            'last_page' => $lastPage,
            'from'      => $offset,
            'to'        => $offset + $perPage,
            'total'     => $total,
            'per_page'  => $perPage
        ];
    }

    protected function get($index, $default=null) {
        $value = $this->request->getGetPost($index);
        if(!$value && $default !== null) {
            return $default;
        }
        return $value;
    }

    protected function getCountries() {

        if (! $this->countries = (array) cache('countries')) {
            $db = db_connect();
            $builder = $db->table('countries');
            $results = $builder->get()->getResult();
            $this->countries  = [];
            foreach ($results as $result) {
                $this->countries[$result->id]   = $result;
            }
            // Save into the cache for 60 minutes
            cache()->save('countries', $this->countries, 60*60);
        }
    }
}