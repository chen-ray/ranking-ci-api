<?php

/***
 * Author: chen ray
 * Email: yingzi
 *
 **/

namespace App\Controllers;


use App\Models\RankingModel;

class Rankings extends BaseApi
{

    protected function resource($data) {
        if( !$data ) return [];
        $return = [];
        foreach ($data as $item) {
            $ranking = [
                'id'            => $item->id,
                'rank'          => $item->rank,
                'rank_change'   => $item->rank_change,
                'tournaments'   => $item->tournaments,
                'player1_name'  => $item->points,
                //'country_img'=> 'https://blog-cdn.chen-ray.cn/countries/' . $this->player1->withCountry->flag_name_svg,,
                'country_name'  => $item->points,
                'country_id'    => $item->points,
                'p1_country'    => $item->p1_country,
                'player1_birth' => $item->points,
            ];
            $return[] = $ranking;
        }

        return $return;
    }

    public function index()
    {
        $categoryId     = $this->get('category_id', 6);
        $p1Country      = $this->get('p1_country', 0);
        $publicationId  = $this->get('publication_id');
        $page           = (int)($this->get('page', 1));
        $perPage        = 20;

        if ( !$publicationId ) {
            $model  = new RankingModel();
            $ranking= $model->orderBy('id', 'desc')->first();
            $publicationId = $ranking->ranking_publication_id;
        }

        $db = db_connect();
        $builder = $db->table('rankings as r');
        $builder->join('players as p', 'r.player1_id = p.id', 'left');
        $builder->join('players as p2', 'r.player2_id = p2.id', 'left');
        $builder->where('ranking_publication_id', $publicationId)->where('ranking_category_id', $categoryId);
        if ($p1Country) {
            $builder->where('p1_country', $p1Country);
        }
        $builder2 = clone $builder;

        $builder->selectCount('r.id');
        $query = $builder->get()->getFirstRow();
        $total   = (int)$query->id;
        // 计算总页数
        $last_page = ceil($total / $perPage);

        $builder2->select(
            'r.id as id, r.rank, r.rank_change, tournaments, points, p1_country,  
            p.date_of_birth as player1_birth, p.country_id as country_id, p.name_display as player1_name,
            p2.name_display as player2_name, p2.date_of_birth as player2_birth', false);

        $builder2->orderBy('rank', 'asc');
        $offset = ($page - 1) * $perPage;
        $builder2->limit($perPage, $offset);
        //$sql = $builder2->getCompiledSelect();
        //log_message('debug', $sql);
        $query = $builder2->get();
        // current: 0, current_page: 0, from: 0, last: 0, last_page: 0, links: [],
        // next: 0, path: "", per_page: 0, prev: 0, to: 0, total: 0

        $meta = $this->makeMeta($page, $last_page, $offset, $perPage, $total);
        $rows   = $query->getResult();

        // 处理 country_img、country_name 没有处理
        foreach ($rows as &$row) {
            $img    = 'https://blog-cdn.chen-ray.cn/countries/' . $this->countries[$row->country_id]->flag_name_svg;
            $row->country_img = $img;
        }

        $data = [
            'data'  => $rows,
            'meta'  => $meta,
        ];
        return $this->respond($data, 200, 'ok');
    }
}