<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Tidak ada authorization khusus untuk demo
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
                // Update lebih fleksibel: boleh tanggal lampau (untuk task yang sudah ada)
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
        ];
    }

    /**
     * Handle a failed validation attempt.
     * Override untuk custom JSON response di Ajax
     */
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

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Custom validation untuk update
            if ($this->filled('due_at')) {
                try {
                    Carbon::parse($this->due_at);
                } catch (\Exception $e) {
                    $validator->errors()->add('due_at', 'Format tanggal tidak valid.');
                }
            }

            if ($this->filled('due_at') && $this->status !== Task::STATUS_DONE) {
                $dueAt = Carbon::parse($this->due_at);
                if ($dueAt->isPast()) {
                    $validator->after(function ($validator) {
                    });
                }
            }
        });
    }
}