<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ContactUsService
{
    public function createContactUs($data)
    {
        $data['is_resolved'] = false;
        $inserted = DB::table('contact_us')->insert($data);
        return $inserted ? null : "Failed to create contact us.";
    }

    public function assignToStaff($contactId, $staffId)
    {
        // Double-check existence (defensive)
        $contact = DB::table('contact_us')
            ->where('contact_us_id', $contactId)
            ->first();

        if (!$contact) {
            return "Contact message not found";
        }

        // Prevent double assignment
        $alreadyAssigned = DB::table('tasks')
            ->where('task_type', 'support_ticket')
            ->where('contact_us_id', $contactId)
            ->exists();

        if ($alreadyAssigned) {
            return "This contact message is already assigned to a staff member";
        }

        DB::table('tasks')->insert([
            'task_type'         => 'support_ticket',
            'description'       => "Support Ticket: {$contact->title} - {$contact->email}",
            'status'            => 'pending',
            'assigned_staff_id' => $staffId,
            'contact_us_id'     => $contactId,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return true;
    }

    public function markAsResolved($contactId)
    {
        $contact = DB::table('contact_us')
            ->where('contact_us_id', $contactId)
            ->first();

        if (!$contact) {
            return "Contact message not found";
        }

        DB::transaction(function () use ($contactId) {
            DB::table('contact_us')
                ->where('contact_us_id', $contactId)
                ->update(['is_resolved' => 1, 'updated_at' => now()]);

            DB::table('tasks')
                ->where('task_type', 'support_ticket')
                ->where('contact_us_id', $contactId)
                ->update(['status' => 'completed', 'updated_at' => now()]);
        });

        return true;
    }

    public function getContactUsAdmin($filters)
    {
        $perPage = $filters['max'];
        $page    = $filters['first'];
        $offset  = ($page - 1) * $perPage;

        $query = DB::table('contact_us as c')
            ->leftJoin('tasks as t', function ($join) {
                $join->on('t.contact_us_id', '=', 'c.contact_us_id')
                    ->where('t.task_type', 'support_ticket');
            })
            ->leftJoin('users as staff', 't.assigned_staff_id', '=', 'staff.user_id') // UUID join
            ->leftJoin('photo_paths as pp', 'staff.photo_path_id', '=', 'pp.photo_path_id')
            ->select(
                'c.contact_us_id',
                'c.title',
                'c.email',
                'c.phone',
                'c.description',
                'c.is_resolved',
                'c.created_at',

                't.task_id',
                't.status as task_status',
                't.created_at as task_assigned_at',

                // Full Assigned Staff Info (UUID safe)
                'staff.user_id as assigned_staff_id',
                'staff.name as assigned_staff_name',
                'staff.phone as assigned_staff_phone',
                'staff.email as assigned_staff_email',
                DB::raw("IF(pp.photo_path IS NOT NULL, CONCAT('" . env('R2_URL') . "/', pp.photo_path), NULL) as assigned_staff_photo_url")
            );

        // Filters
        if ($filters['resolve'] !== null) {
            $query->where('c.is_resolved', $filters['resolve']);
        }

        if ($filters['assigned'] !== null) {
            $filters['assigned']
                ? $query->whereNotNull('t.task_id')
                : $query->whereNull('t.task_id');
        }

        // Sorting
        $query->orderBy('c.created_at', $filters['sort_by_time_asc'] ? 'asc' : 'desc');

        // Total count
        $total = (clone $query)->count();

        // Paginated items
        $items = $query->offset($offset)->limit($perPage)->get();

        return [
            'data'       => $items,
            'first'      => $page,
            'max'        => $perPage,
            'total'      => $total,
            'total_page' => $total > 0 ? ceil($total / $perPage) : 1
        ];
    }
}