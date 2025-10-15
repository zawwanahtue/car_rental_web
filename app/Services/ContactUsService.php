<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;

class ContactUsService
{
    public function getContactUs($data)
    {
        $contactUs = DB::table('contact_us')->insertGetId($data);
        return $contactUs;
    }
}