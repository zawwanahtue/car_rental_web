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

    public function generateTicketNumber()
    {
        $date = now()->format('Ymd');
        $countToday = DB::table('bookings')
            ->whereDate('created_at', now())
            ->count() + 1;
        $ticketNumber = 'TCK-' . $date . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);
        return $ticketNumber;
    }

    public function getTicketNumber()
    {
        do {
            $ticketNumber = $this->generateTicketNumber();
            $isExist = $this->isExit('bookings', 'ticket_number', $ticketNumber);
        } while ($isExist);

        return $ticketNumber;
    }
}