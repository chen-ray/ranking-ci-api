<?php
/***
 * Author: chen ray
 * Email: chenraygogo@gmail.com
 *
 **/

namespace App\Models;


use App\Entities\Breakdown;
use CodeIgniter\Model;

class BreakdownModel extends Model
{
    protected $table         = 'breakdowns';
    protected $returnType    = Breakdown::class;
    protected $allowedFields = [
        'name', 'description',
    ];
}