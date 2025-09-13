<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                'min:3',
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'status' => [
                'required',
                Rule::in(Task::STATUSES),
            ],
            'due_at' => [
                'nullable',
                'date',
                'after_or_equal:today', // Nilai tambah: tidak boleh tanggal lampau
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'judul tugas',
            'description' => 'deskripsi',
            'status' => 'status',
            'due_at' => 'batas waktu',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul tugas wajib diisi.',
            'title.min' => 'Judul tugas minimal 3 karakter.',
            'title.max' => 'Judul tugas maksimal 255 karakter.',
            'description.max' => 'Deskripsi maksimal 2000 karakter.',
            'status.required' => 'Status tugas wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
            'due_at.date' => 'Format batas waktu tidak valid.',
            'due_at.after_or_equal' => 'Batas waktu tidak boleh tanggal lampau.',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        if ($this->expectsJson()) {
            $response = response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $validator->errors()
            ], 422);

            throw new \Illuminate\Validation\ValidationException($validator, $response);
        }

        parent::failedValidation($validator);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->filled('due_at') && $this->filled('status')) {
                try {
                    Carbon::parse($this->due_at);
                } catch (\Exception $e) {
                    $validator->errors()->add('due_at', 'Format tanggal tidak valid.');
                }
            }
        });
    }
}