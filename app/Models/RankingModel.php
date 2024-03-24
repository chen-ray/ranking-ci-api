<?php

namespace App\Models;

use App\Entities\Ranking;
use CodeIgniter\Model;

/***
 * Author: chen ray
 * Email: chenraygogo@gmail.com
 *
 **/

class RankingModel extends Model
{
    protected $table         = 'rankings';
    protected $allowedFields = [
        'username', 'email', 'password',
    ];
    protected $returnType    = Ranking::class;
    protected bool $useTimestamps = true;
}