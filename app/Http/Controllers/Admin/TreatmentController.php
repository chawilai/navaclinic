<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\TreatmentRecord;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TreatmentController extends Controller
{
    public function create(Booking $booking)
    {
        return Inertia::render('Admin/Treatment/Create', [
            'booking' => $booking->load(['user', 'doctor']),
            'previousRecord' => $booking->treatmentRecord,
        ]);
    }

    public function store(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'patient_name' => 'nullable|string|max:255',
            'id_card_number' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'age' => 'nullable|integer',
            'gender' => 'nullable|string',
            'race' => 'nullable|string',
            'nationality' => 'nullable|string',
            'religion' => 'nullable|string',
            'occupation' => 'nullable|string',
            'address' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string',
            'emergency_contact_phone' => 'nullable|string',
            'underlying_disease' => 'nullable|string',
            'surgery_history' => 'nullable|string',
            'drug_allergy' => 'nullable|string',
            'accident_history' => 'nullable|string',
            'weight' => 'nullable|numeric|between:0,500',
            'height' => 'nullable|numeric|between:0,300',
            'temperature' => 'nullable|numeric|between:0,100',
            'pulse_rate' => 'nullable|integer|between:0,300',
            'respiratory_rate' => 'nullable|integer|between:0,100',
            'blood_pressure' => 'nullable|string|max:20',
            'chief_complaint' => 'nullable|string',
            'physical_exam' => 'nullable|string',
            'massage_weight' => 'nullable|string|in:light,medium,heavy',
            'pain_level_before' => 'nullable|integer|between:0,10',
            'pain_level_after' => 'nullable|integer|between:0,10',
            'pain_areas' => 'nullable|array',
            'pain_areas.*.area' => 'required_with:pain_areas|string',
            'pain_areas.*.symptom' => 'nullable|string',
            'pain_areas.*.pain_level' => 'nullable|numeric|between:0,10',
            'pain_areas.*.pain_level_after' => 'nullable|numeric|between:0,10',
            'pain_areas.*.characteristic' => 'nullable|string',
        ], [
            'weight.numeric' => 'น้ำหนักต้องเป็นตัวเลข',
            'weight.between' => 'น้ำหนักต้องไม่เกิน 500 กิโลกรัม',
            'height.numeric' => 'ส่วนสูงต้องเป็นตัวเลข',
            'height.between' => 'ส่วนสูงต้องไม่เกิน 300 เซนติเมตร',
            'temperature.numeric' => 'อุณหภูมิต้องเป็นตัวเลข',
            'temperature.between' => 'อุณหภูมิต้องไม่เกิน 100 องศาเซลเซียส',
            'pulse_rate.integer' => 'ชีพจรต้องเป็นตัวเลขจำนวนเต็ม',
            'pulse_rate.between' => 'ชีพจรต้องไม่เกิน 300 ครั้งต่อนาที',
            'respiratory_rate.integer' => 'อัตราการหายใจต้องเป็นตัวเลขจำนวนเต็ม',
            'respiratory_rate.between' => 'อัตราการหายใจต้องไม่เกิน 100 ครั้งต่อนาที',
            'blood_pressure.max' => 'ความดันโลหิตต้องมีความยาวไม่เกิน 20 ตัวอักษร',
            'pain_level_before.integer' => 'ระดับความปวด (ก่อน) ต้องเป็นตัวเลขจำนวนเต็ม',
            'pain_level_before.between' => 'ระดับความปวด (ก่อน) ต้องอยู่ระหว่าง 0 ถึง 10',
            'pain_level_after.integer' => 'ระดับความปวด (หลัง) ต้องเป็นตัวเลขจำนวนเต็ม',
            'pain_level_after.between' => 'ระดับความปวด (หลัง) ต้องอยู่ระหว่าง 0 ถึง 10',
            'pain_areas.*.pain_level.numeric' => 'ระดับความปวด (ก่อน) ต้องเป็นตัวเลข',
            'pain_areas.*.pain_level.between' => 'ระดับความปวด (ก่อน) ต้องอยู่ระหว่าง 0 ถึง 10',
            'pain_areas.*.pain_level_after.numeric' => 'ระดับความปวด (หลัง) ต้องเป็นตัวเลข',
            'pain_areas.*.pain_level_after.between' => 'ระดับความปวด (หลัง) ต้องอยู่ระหว่าง 0 ถึง 10',
        ]);


        $record = $booking->treatmentRecord()->updateOrCreate(
            ['booking_id' => $booking->id],
            $validated
        );

        if ($request->input('save_action') === 'next') {
            return redirect()->route('admin.treatment.details', $record->id)
                ->with('success', 'Intake saved. Proceeding to Plan.');
        }

        if ($request->input('save_action') === 'exit') {
            return redirect()->route('admin.bookings.show', $booking->id)
                ->with('success', 'Intake & Examination saved successfully.');
        }

        return back()->with('success', 'Intake & Examination saved successfully.');
    }
    public function createForVisit(\App\Models\Visit $visit)
    {
        return Inertia::render('Admin/Treatment/Create', [
            'visit' => $visit->load(['patient', 'doctor']),
            'previousRecord' => $visit->treatmentRecord,
            'isVisit' => true,
        ]);
    }

    public function storeForVisit(Request $request, \App\Models\Visit $visit)
    {
        $validated = $request->validate([
            'patient_name' => 'nullable|string|max:255',
            'id_card_number' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'age' => 'nullable|integer',
            'gender' => 'nullable|string',
            'race' => 'nullable|string',
            'nationality' => 'nullable|string',
            'religion' => 'nullable|string',
            'occupation' => 'nullable|string',
            'address' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string',
            'emergency_contact_phone' => 'nullable|string',
            'underlying_disease' => 'nullable|string',
            'surgery_history' => 'nullable|string',
            'drug_allergy' => 'nullable|string',
            'accident_history' => 'nullable|string',
            'weight' => 'nullable|numeric|between:0,500',
            'height' => 'nullable|numeric|between:0,300',
            'temperature' => 'nullable|numeric|between:0,100',
            'pulse_rate' => 'nullable|integer|between:0,300',
            'respiratory_rate' => 'nullable|integer|between:0,100',
            'blood_pressure' => 'nullable|string|max:20',
            'chief_complaint' => 'nullable|string',
            'physical_exam' => 'nullable|string',
            'massage_weight' => 'nullable|string|in:light,medium,heavy',
            'pain_level_before' => 'nullable|integer|between:0,10',
            'pain_level_after' => 'nullable|integer|between:0,10',
            'pain_areas' => 'nullable|array',
            'pain_areas.*.area' => 'required_with:pain_areas|string',
            'pain_areas.*.symptom' => 'nullable|string',
            'pain_areas.*.pain_level' => 'nullable|numeric|between:0,10',
            'pain_areas.*.pain_level_after' => 'nullable|numeric|between:0,10',
            'pain_areas.*.characteristic' => 'nullable|string',
        ], [
            'weight.numeric' => 'น้ำหนักต้องเป็นตัวเลข',
            'weight.between' => 'น้ำหนักต้องไม่เกิน 500 กิโลกรัม',
            'height.numeric' => 'ส่วนสูงต้องเป็นตัวเลข',
            'height.between' => 'ส่วนสูงต้องไม่เกิน 300 เซนติเมตร',
            'temperature.numeric' => 'อุณหภูมิต้องเป็นตัวเลข',
            'temperature.between' => 'อุณหภูมิต้องไม่เกิน 100 องศาเซลเซียส',
            'pulse_rate.integer' => 'ชีพจรต้องเป็นตัวเลขจำนวนเต็ม',
            'pulse_rate.between' => 'ชีพจรต้องไม่เกิน 300 ครั้งต่อนาที',
            'respiratory_rate.integer' => 'อัตราการหายใจต้องเป็นตัวเลขจำนวนเต็ม',
            'respiratory_rate.between' => 'อัตราการหายใจต้องไม่เกิน 100 ครั้งต่อนาที',
            'blood_pressure.max' => 'ความดันโลหิตต้องมีความยาวไม่เกิน 20 ตัวอักษร',
            'pain_level_before.integer' => 'ระดับความปวด (ก่อน) ต้องเป็นตัวเลขจำนวนเต็ม',
            'pain_level_before.between' => 'ระดับความปวด (ก่อน) ต้องอยู่ระหว่าง 0 ถึง 10',
            'pain_level_after.integer' => 'ระดับความปวด (หลัง) ต้องเป็นตัวเลขจำนวนเต็ม',
            'pain_level_after.between' => 'ระดับความปวด (หลัง) ต้องอยู่ระหว่าง 0 ถึง 10',
            'pain_areas.*.pain_level.numeric' => 'ระดับความปวด (ก่อน) ต้องเป็นตัวเลข',
            'pain_areas.*.pain_level.between' => 'ระดับความปวด (ก่อน) ต้องอยู่ระหว่าง 0 ถึง 10',
            'pain_areas.*.pain_level_after.numeric' => 'ระดับความปวด (หลัง) ต้องเป็นตัวเลข',
            'pain_areas.*.pain_level_after.between' => 'ระดับความปวด (หลัง) ต้องอยู่ระหว่าง 0 ถึง 10',
        ]);


        $record = $visit->treatmentRecord()->updateOrCreate(
            ['visit_id' => $visit->id],
            $validated
        );

        if ($request->input('save_action') === 'next') {
            return redirect()->route('admin.treatment.details', $record->id)
                ->with('success', 'Intake saved. Proceeding to Plan.');
        }

        if ($request->input('save_action') === 'exit') {
            return redirect()->route('admin.visits.show', $visit->id)
                ->with('success', 'Intake & Examination saved successfully.');
        }

        return back()->with('success', 'Intake & Examination saved successfully.');
    }

    public function details(TreatmentRecord $treatmentRecord)
    {
        $treatmentRecord->load(['booking.user', 'booking.doctor', 'visit.patient', 'visit.doctor']);

        // Determine the parent entity (Booking or Visit)
        $entity = $treatmentRecord->visit ?? $treatmentRecord->booking;
        $isVisit = (bool) $treatmentRecord->visit_id;

        return Inertia::render('Admin/Treatment/Details', [
            'treatmentRecord' => $treatmentRecord,
            'entity' => $entity,
            'isVisit' => $isVisit,
        ]);
    }

    public function updateDetails(Request $request, TreatmentRecord $treatmentRecord)
    {
        $validated = $request->validate([
            'diagnosis' => 'required|string',
            'treatment_details' => 'required|string',
            'massage_weight' => 'nullable|string|in:light,medium,heavy',
            'pain_level_before' => 'nullable|integer|between:0,10',
            'pain_level_after' => 'nullable|integer|between:0,10',
            'notes' => 'nullable|string',
            'price' => 'nullable|numeric|min:0', // Final Price
            'treatment_fee' => 'nullable|numeric|min:0', // Base Price
            'discount_type' => 'nullable|string|in:amount,percent',
            'discount_value' => 'nullable|numeric|min:0',
            'doctor_commission' => 'nullable|numeric|min:0',
            'tip' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|in:cash,transfer',

        ], [
            'diagnosis.required' => 'กรุณาระบุการวินิจฉัยโรค',
            'treatment_details.required' => 'กรุณาระบุรายละเอียดการรักษา',
            'pain_level_before.integer' => 'ระดับความปวด (ก่อน) ต้องเป็นตัวเลขจำนวนเต็ม',
            'pain_level_before.between' => 'ระดับความปวด (ก่อน) ต้องอยู่ระหว่าง 0 ถึง 10',
            'pain_level_after.integer' => 'ระดับความปวด (หลัง) ต้องเป็นตัวเลขจำนวนเต็ม',
            'pain_level_after.between' => 'ระดับความปวด (หลัง) ต้องอยู่ระหว่าง 0 ถึง 10',
            'price.numeric' => 'ยอดสุทธิต้องเป็นตัวเลข',
            'price.min' => 'ยอดสุทธิต้องไม่ติดลบ',
            'treatment_fee.numeric' => 'ค่ารักษาต้องเป็นตัวเลข',
            'treatment_fee.min' => 'ค่ารักษาต้องไม่ติดลบ',
            'discount_value.numeric' => 'ส่วนลดต้องเป็นตัวเลข',
            'discount_value.min' => 'ส่วนลดต้องไม่ติดลบ',
            'doctor_commission.numeric' => 'ค่ามือหมอต้องเป็นตัวเลข',
            'doctor_commission.min' => 'ค่ามือหมอต้องไม่ติดลบ',
            'tip.numeric' => 'ทิปต้องเป็นตัวเลข',
            'tip.min' => 'ทิปต้องไม่ติดลบ',
        ]);

        // Update Financials if present
        if ($treatmentRecord->visit) {
            $treatmentRecord->visit->update([
                'price' => $request->price ?? 0,
                'treatment_fee' => $request->treatment_fee ?? 0,
                'doctor_commission' => $request->doctor_commission ?? 0,
                'discount_type' => $request->discount_type ?? 'amount',
                'discount_value' => $request->discount_value ?? 0,
                'tip' => $request->tip ?? 0,
                'payment_method' => $request->payment_method ?? 'transfer',
            ]);
        } elseif ($treatmentRecord->booking) {
            // Fallback for Booking (Only supports price as per schema for now)
            $treatmentRecord->booking->update([
                'price' => $request->price ?? 0,
                'payment_method' => $request->payment_method ?? 'transfer',
            ]);
        }

        $treatmentRecord->update(collect($validated)->except('price')->toArray());

        if ($request->input('save_action') === 'stay') {
            return back()->with('success', 'Treatment Plan saved successfully.');
        }

        $route = $treatmentRecord->visit_id
            ? route('admin.visits.show', $treatmentRecord->visit_id)
            : route('admin.bookings.show', $treatmentRecord->booking_id);

        return redirect($route)->with('success', 'Treatment Record completed successfully.');
    }
}
