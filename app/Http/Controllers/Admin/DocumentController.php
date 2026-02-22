<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DocumentController extends Controller
{
    public function receipt(Visit $visit)
    {
        $visit->load(['patient', 'doctor', 'treatmentRecord', 'payments']);
        return Inertia::render('Admin/Documents/Receipt', [
            'visit' => $visit,
            'users' => User::orderBy('name')->get(['id', 'name']),
            'clinic' => [
                'name_th' => 'นวคลินิกการแพทย์แผนไทยสาขานวดไทย',
                'name_en' => 'NAVA Clinic Thai Traditional Medicine',
                'address_1' => '541/13 ต.หนองหอย อ.เมือง จ.เชียงใหม่ 50000',
                'address_2' => '541/13 NONG HOI, MUANG, CHIANGMAI 50000',
                'phone' => 'โทรศัพท์ / Tel (062) 2781007',
                'license' => 'ใบอนุญาตให้ประกอบกิจการสถานพยาบาล\nที่ 50108000964\nเลขประจำตัวผู้เสียภาษี 3501500385684'
            ]
        ]);
    }

    public function medicalCertificate(Visit $visit)
    {
        $visit->load(['patient', 'doctor', 'treatmentRecord']);
        return Inertia::render('Admin/Documents/MedicalCertificate', [
            'visit' => $visit,
            'clinic' => [
                'name_th' => 'นวคลินิกการแพทย์แผนไทยสาขานวดไทย',
                'name_en' => 'NAVA Clinic Thai Traditional Medicine',
                'address_line' => '541/13 ต.หนองหอย อ.เมือง จ.เชียงใหม่ 50000 โทร 062 278 1007',
                'logo_url' => '/images/logo.png' // Assuming logo path
            ]
        ]);
    }
}
