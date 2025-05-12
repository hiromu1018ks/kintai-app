<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 勤怠情報を表すモデル
 */
class Attendance extends Model
{
    /**
     * 複数代入可能な属性
     *
     * create()やupdate()メソッドなどで、一度に値を設定できるカラム名のリストです。
     * セキュリティのため、意図しないカラムへの値の書き込みを防ぎます。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id', // 勤怠記録を行うユーザーのID
        'attendance_date', // 勤怠日
        'clock_in_time', // 出勤時刻
        'clock_out_time', // 退勤時刻
        'clock_in_comment', // 出勤時のコメント
        'clock_out_comment', // 退勤時のコメント
        'clock_in_modified_by', // 出勤打刻を修正したユーザーのID
        'clock_in_modification_reason', // 出勤打刻の修正理由
        'clock_out_modified_by', // 退勤打刻を修正したユーザーのID
        'clock_out_modification_reason', // 退勤打刻の修正理由
    ];

    /**
     * 属性の型キャスト
     *
     * モデルの属性を取得・設定する際に、自動的に特定のデータ型に変換するための設定です。
     * 例えば、'attendance_date' は日付オブジェクトとして扱われます。
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attendance_date' => 'date', // 'attendance_date' カラムを Carbon の日付オブジェクトとして扱う
        'clock_in_time' => 'datetime', // 'clock_in_time' カラムを Carbon の日時オブジェクトとして扱う
        'clock_out_time' => 'datetime', // 'clock_out_time' カラムを Carbon の日時オブジェクトとして扱う
    ];

    /**
     * この勤怠記録に紐づくユーザー情報を取得します。
     *
     * User モデルとのリレーションを定義しています。
     * 'user_id' カラムを外部キーとして User モデルのレコードに紐づきます。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * この勤怠記録の出勤打刻を修正したユーザー情報を取得します。
     *
     * User モデルとのリレーションを定義しています。
     * 'clock_in_modified_by' カラムを外部キーとして User モデルのレコードに紐づきます。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clockInModifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'clock_in_modified_by');
    }

    /**
     * この勤怠記録の退勤打刻を修正したユーザー情報を取得します。
     *
     * User モデルとのリレーションを定義しています。
     * 'clock_out_modified_by' カラムを外部キーとして User モデルのレコードに紐づきます。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clockOutModifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'clock_out_modified_by');
    }

    // --- 時間計算用のアクセサを追加 ---

    /**
     * 紐づく勤務パターンの正規の休憩時間（分）を取得します。
     *
     * アクセサ (`getScheduledBreakMinutesAttribute`) を使用することで、
     * `$attendance->scheduled_break_minutes` のようにプロパティとしてアクセスできます。
     *
     * @return int 休憩時間（分）
     */
    public function getScheduledBreakMinutesAttribute(): int
    {
        // 勤怠記録に紐づくユーザー情報、またはユーザーに紐づく勤務パターンが存在しない場合
        if (!$this->user || !$this->user->workPattern) {
            return 0; // 休憩時間は0分として返します。
        }

        // ユーザーの勤務パターン情報を取得
        $workPattern = $this->user->workPattern;
        // 勤務パターンの休憩開始時刻と終了時刻を Carbon オブジェクトに変換
        $breakStart = Carbon::parse($workPattern->break_start_time);
        $breakEnd = Carbon::parse($workPattern->break_end_time);

        // 休憩終了時刻と開始時刻の差を分単位で計算して返します。
        return $breakEnd->diffInMinutes($breakStart);
    }

    /**
     * 紐づく勤務パターンの正規の所定労働時間（分）を取得します。
     *
     * アクセサ (`getScheduledWorkMinutesAttribute`) を使用することで、
     * `$attendance->scheduled_work_minutes` のようにプロパティとしてアクセスできます。
     *
     * @return int 所定労働時間（分）
     */
    public function getScheduledWorkMinutesAttribute(): int
    {
        // 勤怠記録に紐づくユーザー情報、またはユーザーに紐づく勤務パターンが存在しない場合
        if (!$this->user || !$this->user->workPattern) {
            return 0; // 所定労働時間は0分として返します。
        }

        // ユーザーの勤務パターン情報を取得
        $workPattern = $this->user->workPattern;
        // 勤務パターンの開始時刻と終了時刻を Carbon オブジェクトに変換
        $startTime = Carbon::parse($workPattern->start_time);
        $endTime = Carbon::parse($workPattern->end_time);

        // (勤務終了時刻 - 勤務開始時刻) - 正規の休憩時間 を計算して返します。
        return $endTime->diffInMinutes($startTime) - $this->scheduled_break_minutes;
    }

    /**
     * 実労働時間（分）を取得します (休憩時間を除く)。
     *
     * アクセサ (`getActualWorkMinutesAttribute`) を使用することで、
     * `$attendance->actual_work_minutes` のようにプロパティとしてアクセスできます。
     *
     * @return int 実労働時間（分）
     */
    public function getActualWorkMinutesAttribute(): int
    {
        // 出勤時刻または退勤時刻の打刻がない場合
        if (!$this->clock_in_time || !$this->clock_out_time) {
            return 0; // 実労働時間は0分として返します。
        }

        // 出勤時刻と退勤時刻を Carbon オブジェクトに変換
        $clockIn = Carbon::parse($this->clock_in_time);
        // clock_out_time の誤記を修正 (clock_end_time -> clock_out_time)
        $clockOut = Carbon::parse($this->clock_out_time);

        // 滞在時間（退勤時刻 - 出勤時刻）を分単位で計算
        $totalMinutesOnSite = $clockOut->diffInMinutes($clockIn);
        // 勤務パターンに基づいた正規の休憩時間を取得
        $actualBreakMinutes = $this->scheduled_break_minutes;

        // 実労働時間 = 滞在時間 - 休憩時間
        $workMinutes = $totalMinutesOnSite - $actualBreakMinutes;

        // 計算結果がマイナスになる場合は0を返す（例：休憩時間が滞在時間より長いなど）
        return max(0, $workMinutes);
    }

    /**
     * 所定外労働時間（分）を取得します (平日の通常残業を想定)。
     * 注意: これは最もシンプルな計算です。深夜労働、休日労働、早出残業などは別途考慮が必要です。
     *
     * アクセサ (`getRegularOvertimeMinutesAttribute`) を使用することで、
     * `$attendance->regular_overtime_minutes` のようにプロパティとしてアクセスできます。
     *
     * @return int 所定外労働時間（分）
     */
    public function getRegularOvertimeMinutesAttribute(): int
    {
        // 出勤時刻、退勤時刻、ユーザー情報、または勤務パターンのいずれかが存在しない場合
        if (!$this->clock_in_time || !$this->clock_out_time || !$this->user || !$this->user->workPattern) {
            return 0; // 所定外労働時間は0分として返します。
        }

        // 勤怠日を Carbon オブジェクトに変換
        $attendanceDate = Carbon::parse($this->attendance_date);

        // 勤怠日が土曜日、日曜日、または祝日テーブルに登録されている日付の場合
        if ($attendanceDate->isSaturday() || $attendanceDate->isSunday() || Holiday::where('holiday_date', $this->attendance_date)->exists()) {
            return 0; // 土日祝日の場合は、ここでは所定外労働時間として計算しません。
        }

        // 実労働時間（休憩除く）を取得
        $actualWorkMinutes = $this->actual_work_minutes;
        // 所定労働時間（休憩除く）を取得
        $scheduledWorkMinutes = $this->scheduled_work_minutes;

        // 所定外労働時間 = 実労働時間 - 所定労働時間
        $overtimeMinutes = $actualWorkMinutes - $scheduledWorkMinutes;

        // 計算結果がマイナスになる場合は0を返す（例：実労働時間が所定労働時間より短い）
        return max(0, $overtimeMinutes);
    }
}
