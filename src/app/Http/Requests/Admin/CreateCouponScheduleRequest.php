<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateCouponScheduleRequest extends FormRequest
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
            'coupon_id' => 'required|string|exists:coupons,id',
            'schedule_name' => 'required|string|max:255',
            'day_type' => 'required|in:daily,weekdays,weekends,custom',
            'custom_days' => 'required_if:day_type,custom|array',
            'custom_days.*' => 'integer|min:0|max:6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'max_acquisitions' => 'nullable|integer|min:1|max:1000',
            'valid_from' => 'required|date|after_or_equal:today',
            'valid_until' => 'nullable|date|after:valid_from',
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
            'coupon_id.required' => 'クーポンを選択してください',
            'coupon_id.exists' => '選択されたクーポンが見つかりません',
            'schedule_name.required' => 'スケジュール名を入力してください',
            'schedule_name.max' => 'スケジュール名は255文字以内で入力してください',
            'day_type.required' => '実行タイミングを選択してください',
            'day_type.in' => '無効な実行タイミングが選択されています',
            'custom_days.required_if' => 'カスタムタイミングの場合は曜日を選択してください',
            'custom_days.array' => '曜日の選択が正しくありません',
            'custom_days.*.integer' => '無効な曜日が選択されています',
            'custom_days.*.min' => '無効な曜日が選択されています',
            'custom_days.*.max' => '無効な曜日が選択されています',
            'start_time.required' => '開始時間を入力してください',
            'start_time.date_format' => '開始時間の形式が正しくありません（HH:MM）',
            'end_time.required' => '終了時間を入力してください',
            'end_time.date_format' => '終了時間の形式が正しくありません（HH:MM）',
            'end_time.after' => '終了時間は開始時間より後に設定してください',
            'max_acquisitions.integer' => '取得上限数は数値で入力してください',
            'max_acquisitions.min' => '取得上限数は1以上で入力してください',
            'max_acquisitions.max' => '取得上限数は1000以下で入力してください',
            'valid_from.required' => '有効開始日を入力してください',
            'valid_from.date' => '有効開始日の形式が正しくありません',
            'valid_from.after_or_equal' => '有効開始日は今日以降の日付を選択してください',
            'valid_until.date' => '有効終了日の形式が正しくありません',
            'valid_until.after' => '有効終了日は開始日より後の日付を選択してください',
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
            'coupon_id' => '対象クーポン',
            'schedule_name' => 'スケジュール名',
            'day_type' => '実行タイミング',
            'custom_days' => '実行曜日',
            'start_time' => '開始時間',
            'end_time' => '終了時間',
            'max_acquisitions' => '取得上限数',
            'valid_from' => '有効開始日',
            'valid_until' => '有効終了日',
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
