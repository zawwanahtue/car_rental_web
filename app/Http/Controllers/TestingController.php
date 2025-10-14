<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Mail;
use App\Mail\Welcome;

class TestingController extends Controller
{
    public function mail() {
        $data['name'] = 'hi';
        Mail::to('yaza9036@gmail.com')->queue(new Welcome($data));
        return "mail sent";
    }
}