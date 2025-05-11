<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $holidays = Holiday::orderBy('holiday_date', 'desc')->paginate(10);
        return view('admin.holidays.index', compact('holidays'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.holidays.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedDate = $request->validate([
            'holiday_date' => 'required|date|unique:holidays,holiday_date',
            'name' => 'required|string|max:255',
        ]);

        Holiday::create($validatedDate);

        return redirect()->route('admin.holidays.index')
            ->with('status', '休日を登録しました。');
    }

    /**
     * Display the specified resource.
     */
    public function show(Holiday $holiday)
    {
        return view('admin.holidays.show', compact('holiday'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Holiday $holiday)
    {
        return view('admin.holidays.edit', compact('holiday'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Holiday $holiday)
    {
        // バリデーション (unique制約は自分自身のレコードを除外する必要がある)
        $validatedData = $request->validate([
            'holiday_date' => 'required|date|unique:holidays,holiday_date,' . $holiday->id,
            'name' => 'required|string|max:255',
        ]);

        $holiday->update($validatedData);

        return redirect()->route('admin.holidays.index')
            ->with('status', '休日を更新しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return redirect()->route('admin.holidays.index')
            ->with('status', '休日を削除しました。');
    }
}
