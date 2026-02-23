<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClinicHoliday;
use App\Models\ClinicSchedule;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Settings/Index', [
            'schedules' => ClinicSchedule::orderBy('day_of_week')->get(),
            'holidays' => ClinicHoliday::orderBy('date')->get(),
        ]);
    }

    public function updateSchedule(Request $request)
    {
        $data = $request->validate([
            'schedules' => 'required|array',
            'schedules.*.id' => 'required|exists:clinic_schedules,id',
            'schedules.*.is_open' => 'required|boolean',
            'schedules.*.open_time' => 'required',
            'schedules.*.close_time' => 'required',
            'schedules.*.admin_booking_start_time' => 'required',
            'schedules.*.admin_booking_end_time' => 'required',
        ]);

        foreach ($data['schedules'] as $scheduleData) {
            ClinicSchedule::where('id', $scheduleData['id'])->update([
                'is_open' => $scheduleData['is_open'],
                'open_time' => $scheduleData['open_time'],
                'close_time' => $scheduleData['close_time'],
                'admin_booking_start_time' => $scheduleData['admin_booking_start_time'],
                'admin_booking_end_time' => $scheduleData['admin_booking_end_time'],
            ]);
        }

        return redirect()->back()->with('success', 'อัปเดตตารางเวลาเรียบร้อยแล้ว');
    }

    public function storeHoliday(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:clinic_holidays,date|after:today',
            'label' => 'required|string|max:255',
        ]);

        ClinicHoliday::create($validated);

        return redirect()->back()->with('success', 'เพิ่มวันหยุดเรียบร้อยแล้ว');
    }

    public function destroyHoliday(ClinicHoliday $holiday)
    {
        $holiday->delete();

        return redirect()->back()->with('success', 'ลบวันหยุดเรียบร้อยแล้ว');
    }
}
