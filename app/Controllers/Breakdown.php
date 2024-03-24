<?php
/***
 * Author: chen ray
 * Email: chenraygogo@gmail.com
 *
 **/

namespace App\Controllers;

use App\Models\BreakdownModel;
use App\Models\RankingModel;
use Config\Services;

class Breakdown extends BaseApi
{
    public function index($rankingId = null) {

        if(! $rankingId) {
            return $this->failNotFound();
        }
        $model  = new BreakdownModel();
        $rows   = $model->where('ranking_id', $rankingId)->findAll();

        //$rows   = $query->getResult();
        log_message('debug', 'breakdowns count=>' . count($rows)  );
        if (count($rows) == 0) {
            log_message('info', 'no breakdown, sync');
            $this->handle($rankingId);
            $model  = new BreakdownModel();
            $rows   = $model->where('ranking_id', $rankingId)->findAll();
        }
        return $this->respond(['data' =>$rows]);
    }

    public function handle($rankingId)
    {
        $model  = new RankingModel();
        $ranking    = $model->find($rankingId);
        if($ranking == null) {
            log_message('error', 'rankings table not data');
            return;
        }
        $client = Services::curlrequest();

        //$data = '{"rankId":2,"catId":7,"playerData":{"id":14109006,"ranking_publication_id":2408,"ranking_category_id":7,"player1_id":87442,"player2_id":null,"team_id":null,"p1_country":"KOR","p2_country":null,"confederation_id":2,"match_party_id":92819,"team_ms":null,"team_ws":null,"team_md":null,"team_wd":null,"team_xd":null,"team_sc":null,"team_tc":null,"team_uc":null,"team_total_points":null,"rank":1,"rank_previous":1,"qual":null,"points":"113314.0000","tournaments":17,"close":null,"rank_change":0,"win":null,"lose":null,"prize_money":null,"player1_model":{"id":87442,"code":"F01113A4-2115-4611-8923-85E45C4A2193","first_name":"Se Young","last_name":"AN","name_type_id":1,"slug":"an-se-young","name_display":"AN Se Young","name_initials":"AS","name_short1":"AN S Y ","name_short2":"AN","name_locked":0,"active":1,"profile_type":0,"avatar_id":null,"last_crawled_at":"2023-03-12 17:16:40","last_cache_updated_at":null,"old_member_id":87442,"gender_id":2,"date_of_birth":"2002-02-05 00:00:00","nationality":"KOR","country":"KOR","country_id":null,"creator_id":null,"ordering":null,"status":1,"para":0,"preferred_name":1,"is_deleted":0,"language":0,"image_profile_id":42062,"image_hero_id":42063,"created_at":"2023-03-13T12:41:18.000000Z","updated_at":"2023-10-20T01:27:04.000000Z","extranet_id":"87442","name_display_bold":"<span class=\"name-2\">AN</span> <span class=\"name-1\">Se Young</span>","country_model":{"id":2,"name":"Korea","code_iso3":"KOR","custom_code":"KOR","flag_name":"korea.png","flag_name_svg":"south-korea.svg","flag_url":"/uploads/flag/korea.png","ma_id":102,"confed_id":2,"currency_code":"","currency_name":"","currency_symbol":"","language_name":"Korean","nationality":"Korean","status":1,"is_deleted":0,"created_at":"2014-07-29T10:30:58.000000Z","updated_at":"2017-08-22T05:47:36.000000Z","created_by":null,"updated_by":1,"extranet_id":"2"}},"player2_model":null}}';
        $data = [
            "rankId"    => (int)$ranking->confederation_id,
            //"rankId"    => 2,
            "catId"     => (int)$ranking->ranking_category_id,
            "playerData"    => [
                "rank"      => (int)$ranking->rank,
                "rank_previous" => (int)$ranking->rank_previous,
                "id"        => (int)$ranking->id,
                "ranking_category_id"   => (int)$ranking->ranking_category_id,
                "ranking_publication_id"=> (int)$ranking->ranking_publication_id,
                "player1_id"            => (int)$ranking->player1_id,
                "player2_id"            => $ranking->player2_id ? (int)$ranking->player2_id : NULL,
            ]
        ];

        $response   = $client->post('https://extranet-lv.bwfbadminton.com/api/vue-rankingbreakdown',
            [
                'headers'   => [
                    'Authorization' => 'Bearer 2|NaXRu9JnMpSdb8l86BkJxj6gzKJofnhmExwr8EWkQtHoattDAGimsSYhpM22a61e1crjTjfIGTKfhzxA',
                    'Content-Type'  => 'application/json;charset=UTF-8',
                    'Accept'        => 'application/json, text/plain, */*'
                ],
                'json' => $data,
                'debug' => '/var/www/ranking-ci-api/writable/logs/curl_log.txt'
            ]
        );

        if($response->getStatusCode() == 200){
            $body   = $response->getBody();
            $json   = json_decode($body, true);
            $at         = date('Y-m-d H:i:s');
            foreach ($json as $key => $item) {
                $json[$key]['ranking_id'] = $ranking->id;
                $json[$key]['created_at'] = $at;
                $json[$key]['updated_at'] = $at;
            }
            $db = db_connect();
            $builder = $db->table('breakdowns');
            $builder->upsertBatch($json);
        } else {
            log_message('error', 'return status not 2xx');
            log_message('error', 'code:'. $response->getStatusCode());
            log_message('error', $response->getReasonPhrase());
        }
    }
}