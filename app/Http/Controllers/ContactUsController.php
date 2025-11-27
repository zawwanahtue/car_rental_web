<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Services\ContactUsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ContactUsController extends Controller
{
    protected $contactUsService;
    protected $helper;

    public function __construct(ContactUsService $contactUsService, Helper $helper)
    {
        $this->contactUsService = $contactUsService;
        $this->helper = $helper;
    }

    // Public: Create contact us
    public function createContactUs(Request $request)
    {
        $rules = [
            'title'       => 'required|string|max:255',
            'email'       => 'required|string|email|max:255',
            'phone'       => 'required|string',
            'description' => 'required|string'
        ];

        $validate = $this->helper->validate($request, $rules);
        if (!is_null($validate)) {
            return $this->helper->PostMan(null, 422, $validate);
        }

        $result = $this->contactUsService->createContactUs($request->all());
        if (is_null($result)) {
            return $this->helper->PostMan(null, 201, "Contact us successfully created");
        }

        return $this->helper->PostMan(null, 500, $result);
    }

    // ADMIN: Assign to staff
    public function assignContactUs(Request $request)
    {
        $rules = [
            'contact_us_id' => 'required|integer|exists:contact_us,contact_us_id',
            'staff_id'      => 'required|string|exists:users,user_id,user_type_id,2'
        ];

        $validate = $this->helper->validate($request, $rules);
        if (!is_null($validate)) {
            return $this->helper->PostMan(null, 422, $validate);
        }

        $contactId = $request->contact_us_id;
        $staffId   = $request->staff_id;

        $result = $this->contactUsService->assignToStaff($contactId, $staffId);

        if (is_string($result)) {
            return $this->helper->PostMan(null, 400, $result);
        }

        return $this->helper->PostMan(null, 200, "Contact us assigned to staff successfully");
    }

    // ADMIN: Manually resolve
    public function resolveContactUs($contactId)
    {
        $result = $this->contactUsService->markAsResolved($contactId);

        if (is_string($result)) {
            return $this->helper->PostMan(null, 404, $result);
        }

        return $this->helper->PostMan(null, 200, "Resolved successfully");
    }

    // ADMIN: List with filters
    public function getContactUsAdmin(Request $request)
    {
        $data = [
            'first'            => max(1, (int)($request->first ?? 1)),
            'max'              => min(100, max(1, (int)($request->max ?? 20))),
            'sort_by_time_asc' => filter_var($request->sort_by_time_asc ?? 'false', FILTER_VALIDATE_BOOLEAN),
            'resolve'          => $request->has('resolve') ? filter_var($request->resolve, FILTER_VALIDATE_BOOLEAN) : null,
            'assigned'         => $request->has('assigned') ? filter_var($request->assigned, FILTER_VALIDATE_BOOLEAN) : null,
        ];

        $result = $this->contactUsService->getContactUsAdmin($data);

        // Only wrap once — remove extra "data" key
        return $this->helper->PostMan($result, 200, "data retrieved successfully");
    }

    public function getContactUsStaff(Request $request)
    {
        $staffId = Auth::id();

        $data = [
            'first'            => max(1, (int)($request->first ?? 1)),
            'max'              => min(100, max(1, (int)($request->max ?? 20))),
            'sort_by_time_asc' => filter_var($request->sort_by_time_asc ?? 'false', FILTER_VALIDATE_BOOLEAN),
            'resolve'          => $request->has('resolve') ? filter_var($request->resolve, FILTER_VALIDATE_BOOLEAN) : null,
        ];

        $result = $this->contactUsService->getContactUsStaff($staffId, $data);

        // Only wrap once — remove extra "data" key
        return $this->helper->PostMan($result, 200, "data retrieved successfully");
    }
}