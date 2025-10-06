<?php
namespace App\Services;

use App\Services\FileService;
use Illuminate\Support\Facades\DB;
use Termwind\Components\Raw;

class CommonService
{
    public function ForeignKeyIsExit($table, $column, $id)
    {
        return DB::table($table)->where($column, $id)->exists();
    }

    public function isExit($table, $column, $value)
    {
        return DB::table($table)->where($column, $value)->exists();
    }
}