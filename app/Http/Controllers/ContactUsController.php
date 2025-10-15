<?php
namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Services\ContactUsService;
use Illuminate\Http\Request;

class ContactUsController extends Controller
{
    protected $helper;
    protected $contactUsService;
    public function index(Helper $helper, ContactUsService $contactUsService)
    { 
        $this->helper = $helper;
        $this->contactUsService = $contactUsService;
    }

    public function getContactUs()
    {
        $contactUs = $this->contactUsService->getContactUs();
        if(!$contactUs) {
            return $this->helper->PostMan(null, 404, $contactUs);
        }
        return $this->helper->PostMan($contactUs, 200, "Contact Us Retrieved Successfully");
    }

    public function createContactUs(Request $request)
    {
    }

    public function updateContactUs(Request $request)
    {
    }  

    public function deleteContactUs(Request $request)
    {
    }
}