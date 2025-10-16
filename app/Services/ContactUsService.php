<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;

class ContactUsService
{
    public function getContactUs()
    {
        $contactUs = DB::table('contact_us')->get();
        return $contactUs;
    }

    public function createContactUs($data)
    {
        $data['is_resolved'] = false;
        $contactUs = DB::table('contact_us')->insert($data);
        if(!$contactUs)
        {
            return "Failed to create contact us.";
        }
        return null;
    }
}