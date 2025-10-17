<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cart_items' => 'required|array|min:1|max:10',
            'cart_items.*.equipment_id' => 'required|exists:equipment,id',
            'cart_items.*.quantity' => 'required|integer|min:1|max:10',
            'reservation_date' => 'required|date|after_or_equal:today|before:' . now()->addMonths(3)->toDateString(),
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'purpose' => 'required|string|min:10|max:500',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'cart_items.required' => 'Please add at least one item to your cart',
            'cart_items.min' => 'Please add at least one item to your cart',
            'cart_items.max' => 'Maximum 10 items allowed per reservation',
            'cart_items.*.equipment_id.required' => 'Equipment selection is required',
            'cart_items.*.equipment_id.exists' => 'Selected equipment does not exist',
            'cart_items.*.quantity.required' => 'Quantity is required',
            'cart_items.*.quantity.min' => 'Quantity must be at least 1',
            'cart_items.*.quantity.max' => 'Maximum quantity is 10 per item',
            'reservation_date.required' => 'Reservation date is required',
            'reservation_date.after_or_equal' => 'Reservation date must be today or later',
            'reservation_date.before' => 'Reservations can only be made up to 3 months in advance',
            'start_time.required' => 'Start time is required',
            'start_time.date_format' => 'Invalid time format',
            'end_time.required' => 'End time is required',
            'end_time.after' => 'End time must be after start time',
            'purpose.required' => 'Purpose is required',
            'purpose.min' => 'Please provide a detailed purpose (minimum 10 characters)',
            'purpose.max' => 'Purpose is too long (maximum 500 characters)',
            'notes.max' => 'Notes are too long (maximum 1000 characters)',
        ];
    }
}
