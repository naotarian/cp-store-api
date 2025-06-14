<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCouponScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // 認証はミドルウェアで処理済み
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'schedule_name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'max_acquisitions' => 'nullable|integer|min:1',
            'valid_from' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'required|boolean',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'schedule_name.required' => 'スケジュール名を入力してください',
            'schedule_name.max' => 'スケジュール名は255文字以内で入力してください',
            'start_time.required' => '開始時間を入力してください',
            'start_time.date_format' => '開始時間の形式が正しくありません（HH:MM）',
            'end_time.required' => '終了時間を入力してください',
            'end_time.date_format' => '終了時間の形式が正しくありません（HH:MM）',
            'end_time.after' => '終了時間は開始時間より後に設定してください',
            'max_acquisitions.integer' => '取得上限数は数値で入力してください',
            'max_acquisitions.min' => '取得上限数は1以上で入力してください',
            'valid_from.required' => '有効開始日を入力してください',
            'valid_from.date' => '有効開始日の形式が正しくありません',
            'valid_until.date' => '有効終了日の形式が正しくありません',
            'valid_until.after_or_equal' => '有効終了日は開始日以降の日付を選択してください',
            'is_active.required' => 'スケジュール状態を選択してください',
            'is_active.boolean' => 'スケジュール状態の値が正しくありません',
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
            'schedule_name' => 'スケジュール名',
            'start_time' => '開始時間',
            'end_time' => '終了時間',
            'max_acquisitions' => '取得上限数',
            'valid_from' => '有効開始日',
            'valid_until' => '有効終了日',
            'is_active' => 'スケジュール状態',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => 'バリデーションエラーが発生しました',
                'errors' => $validator->errors()->toArray()
            ], 422)
        );
    }
}
