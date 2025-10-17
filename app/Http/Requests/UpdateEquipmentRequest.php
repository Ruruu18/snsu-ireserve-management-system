<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEquipmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasAdminAccess();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $equipmentId = $this->route('equipment')?->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('equipment', 'name')->ignore($equipmentId)],
            'description' => 'nullable|string|max:1000',
            'category' => ['required', 'string', Rule::in([
                'Computers & Laptops',
                'Audio & Visual',
                'Laboratory Equipment',
                'Sports Equipment',
                'Tools & Machinery',
                'Office Equipment',
                'Other'
            ])],
            'serial_number' => ['nullable', 'string', 'max:100', Rule::unique('equipment', 'serial_number')->ignore($equipmentId)],
            'total_quantity' => 'required|integer|min:1|max:1000',
            'status' => ['required', Rule::in(['available', 'unavailable', 'under_maintenance'])],
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Equipment name is required',
            'name.unique' => 'Equipment with this name already exists',
            'category.required' => 'Category is required',
            'category.in' => 'Invalid category selected',
            'serial_number.unique' => 'Equipment with this serial number already exists',
            'total_quantity.required' => 'Quantity is required',
            'total_quantity.min' => 'Quantity must be at least 1',
            'total_quantity.max' => 'Maximum quantity is 1000 units',
            'status.required' => 'Status is required',
            'status.in' => 'Invalid status selected',
            'image.image' => 'File must be an image',
            'image.mimes' => 'Image must be jpeg, png, or jpg',
            'image.max' => 'Image size must not exceed 2MB',
        ];
    }
}
