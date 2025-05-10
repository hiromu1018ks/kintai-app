<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();

        // 本日の打刻記録を取得

        $todaysAttendance = Attendance::where('user_id', $user->id)
            ->where('attendance_date', $today)
            ->first();

        return view('dashboard', [
            'user' => $user,
            'todaysAttendance' => $todaysAttendance,
        ]);
    }
}
