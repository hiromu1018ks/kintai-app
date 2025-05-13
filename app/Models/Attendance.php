<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// 日付と時刻を扱うためのCarbonライブラリをインポート
// モデルファクトリを利用するためのトレイトをインポート
// Eloquentモデルの基底クラスをインポート
// BelongsToリレーションシップを定義するためにインポート
// use App\Models\Holiday; // Holidayモデルを使用する場合、ここでインポートします（getRegularOvertimeMinutesAttributeメソッド内で使用）
// use App\Models\User; // Userモデルを使用する場合、ここでインポートします（リレーションシップで使用）

/**
 * Attendanceモデルクラス
 * 勤怠情報を扱います。
 */
class Attendance extends Model
{
    use HasFactory; // テストデータの生成などに便利なモデルファクトリ機能を利用可能にします

    /**
     * 複数代入可能な属性
     * createメソッドやupdateメソッドなどで一括して値を設定できるカラムを指定します。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id', // 勤怠記録に関連するユーザーのID
        'attendance_date', // 勤怠日
        'clock_in_time', // 出勤時刻
        'clock_out_time', // 退勤時刻
        'clock_in_comment', // 出勤時のコメント
        'clock_out_comment', // 退勤時のコメント
        'clock_in_modified_by', // 出勤時刻を修正したユーザーのID
        'clock_in_modification_reason', // 出勤時刻の修正理由
        'clock_out_modified_by', // 退勤時刻を修正したユーザーのID
        'clock_out_modification_reason', // 退勤時刻の修正理由
    ];

    /**
     * 属性の型キャスト
     * 特定のカラムのデータ型を自動的に変換するように指定します。
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attendance_date' => 'date', // attendance_dateカラムをCarbonのDateオブジェクトとして扱う
        'clock_in_time' => 'datetime', // clock_in_timeカラムをCarbonのDateTimeオブジェクトとして扱う
        'clock_out_time' => 'datetime', // clock_out_timeカラムをCarbonのDateTimeオブジェクトとして扱う
    ];

    /**
     * この勤怠記録が属するユーザーを取得します。
     * Userモデルとのリレーションシップを定義します（多対一）。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        // $this->belongsTo(User::class) は、AttendanceモデルがUserモデルに属することを意味します。
        // Laravelは規約に基づき、'user_id' カラムを外部キーとして使用します。
        return $this->belongsTo(User::class);
    }

    /**
     * 出勤時刻を修正したユーザーを取得します。
     * Userモデルとのリレーションシップを定義します（多対一）。
     * 'clock_in_modified_by' カラムを外部キーとして使用します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clockInModifier(): BelongsTo
    {
        // 第2引数で外部キーのカラム名を明示的に指定しています。
        return $this->belongsTo(User::class, 'clock_in_modified_by');
    }

    /**
     * 退勤時刻を修正したユーザーを取得します。
     * Userモデルとのリレーションシップを定義します（多対一）。
     * 'clock_out_modified_by' カラムを外部キーとして使用します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clockOutModifier(): BelongsTo
    {
        // 第2引数で外部キーのカラム名を明示的に指定しています。
        return $this->belongsTo(User::class, 'clock_out_modified_by');
    }

    /**
     * 2つのCarbonインスタンス間の時間差を分で計算します。
     * $laterTime が $earlierTime より前または同じ場合は0を返します。
     *
     * @param \Carbon\Carbon $laterTime   後の時刻を表すCarbonインスタンス
     * @param \Carbon\Carbon $earlierTime 前の時刻を表すCarbonインスタンス
     * @return int 時間差（分）
     */
    protected function calculateDifferenceInMinutes(Carbon $laterTime, Carbon $earlierTime): int
    {
        // lte (less than or equal) メソッドで、$laterTime が $earlierTime 以前かどうかを判定します。
        if ($laterTime->lte($earlierTime)) {
            return 0; // 後の時刻が前の時刻以前なら、差は0分とします。
        }
        // getTimestamp()メソッドでUNIXタイムスタンプ（秒）を取得し、差分を計算します。
        // その後、60で割ることで分に変換し、整数型にキャストします。
        return (int)(($laterTime->getTimestamp() - $earlierTime->getTimestamp()) / 60);
    }

    /**
     * 紐づく勤務パターンの正規の休憩時間（分）を取得するアクセサ。
     * 'scheduled_break_minutes' という属性としてアクセスできます。 (例: $attendance->scheduled_break_minutes)
     *
     * @return int 正規の休憩時間（分）
     */
    public function getScheduledBreakMinutesAttribute(): int
    {
        // 勤怠記録に紐づくユーザー情報、またはそのユーザーの勤務パターンが存在しない場合は、休憩0分とします。
        if (!$this->user || !$this->user->workPattern) {
            return 0;
        }

        $workPattern = $this->user->workPattern; // ユーザーの勤務パターンを取得

        try {
            // 休憩開始・終了時刻のパースには、打刻日を基準日として使用します。
            // これにより、日付をまたがない単純な時間差として休憩時間を計算できます。
            $baseDate = $this->attendance_date->toDateString(); // attendance_dateは$castsによりCarbonインスタンスになっています。
            $breakStart = Carbon::parse($baseDate . ' ' . $workPattern->break_start_time);
            $breakEnd = Carbon::parse($baseDate . ' ' . $workPattern->break_end_time);

            // Carbon::parseが失敗した場合や、休憩終了時刻が開始時刻以前の場合（無効な設定）は0分とします。
            if (!$breakStart->isValid() || !$breakEnd->isValid() || $breakEnd->lte($breakStart)) {
                return 0;
            }

            // calculateDifferenceInMinutesメソッドを使って、休憩時間を分で計算します。
            return $this->calculateDifferenceInMinutes($breakEnd, $breakStart);
        } catch (\Exception $e) {
            // Carbon::parseなどで例外が発生した場合（例: 時刻フォーマット不正）も、安全策として0分を返します。
            return 0;
        }
    }

    /**
     * 紐づく勤務パターンの正規の所定労働時間（分）を取得するアクセサ。
     * 'scheduled_work_minutes' という属性としてアクセスできます。 (例: $attendance->scheduled_work_minutes)
     *
     * @return int 正規の所定労働時間（分）
     */
    public function getScheduledWorkMinutesAttribute(): int
    {
        // 勤怠記録に紐づくユーザー情報、またはそのユーザーの勤務パターンが存在しない場合は、所定労働時間0分とします。
        if (!$this->user || !$this->user->workPattern) {
            return 0;
        }

        $workPattern = $this->user->workPattern; // ユーザーの勤務パターンを取得

        try {
            // 勤務開始・終了時刻のパースには、打刻日を基準日として使用します。
            $baseDate = $this->attendance_date->toDateString();
            $startTime = Carbon::parse($baseDate . ' ' . $workPattern->start_time);
            $endTime = Carbon::parse($baseDate . ' ' . $workPattern->end_time);

            // 終業時刻が始業時刻以前の場合（日付またぎ勤務はここでは考慮せず、単純な時間設定ミスとみなす）、所定労働時間は0分とします。
            if ($endTime->lte($startTime)) {
                return 0;
            }

            // 総拘束時間（分）を計算します。
            $totalScheduledMinutes = $this->calculateDifferenceInMinutes($endTime, $startTime);
            // 正規の休憩時間（分）を取得します。内部的に getScheduledBreakMinutesAttribute が呼び出されます。
            $breakMinutes = $this->scheduled_break_minutes;

            // 所定労働時間 = 総拘束時間 - 休憩時間
            $scheduledWork = $totalScheduledMinutes - $breakMinutes;

            // 計算結果がマイナスになる場合（休憩時間が総拘束時間より長いなど）は0を返します。
            return max(0, $scheduledWork);
        } catch (\Exception $e) {
            // Carbon::parseなどで例外が発生した場合も、安全策として0分を返します。
            return 0;
        }
    }

    /**
     * 実労働時間（分）を取得するアクセサ (休憩時間を除く)。
     * 'actual_work_minutes' という属性としてアクセスできます。 (例: $attendance->actual_work_minutes)
     *
     * @return int 実労働時間（分）
     */
    public function getActualWorkMinutesAttribute(): int
    {
        // 出勤時刻または退勤時刻が記録されていない場合は、実労働時間0分とします。
        if (!$this->clock_in_time || !$this->clock_out_time) {
            return 0;
        }

        try {
            // clock_in_time と clock_out_time は $casts によりCarbonインスタンスになっています。
            $clockIn = Carbon::parse($this->clock_in_time); //念のためパース
            $clockOut = Carbon::parse($this->clock_out_time); //念のためパース

            // 退勤時刻が出勤時刻以前の場合は、実労働時間0分とします。
            if ($clockOut->lte($clockIn)) {
                return 0;
            }

            // 実際に打刻された出勤時刻から退勤時刻までの総時間（分）を計算します。
            $totalMinutesOnSite = $this->calculateDifferenceInMinutes($clockOut, $clockIn);
            // 休憩時間を取得します。現在のロジックでは勤務パターンの正規休憩時間を使用しています。
            // 実際に取得した休憩時間を反映させたい場合は、この部分のロジックの変更が必要です。
            $actualBreakMinutes = $this->scheduled_break_minutes;

            // 実労働時間 = 総滞在時間 - 休憩時間
            $workMinutes = $totalMinutesOnSite - $actualBreakMinutes;

            // 計算結果がマイナスになる場合は0を返します。
            return max(0, $workMinutes);
        } catch (\Exception $e) {
            // Carbon::parseなどで例外が発生した場合も、安全策として0分を返します。
            return 0;
        }
    }

    /**
     * 所定外労働時間（分）を取得するアクセサ (平日の通常残業を想定)。
     * 'regular_overtime_minutes' という属性としてアクセスできます。 (例: $attendance->regular_overtime_minutes)
     *
     * @return int 所定外労働時間（分）
     */
    public function getRegularOvertimeMinutesAttribute(): int
    {
        // 出勤・退勤時刻がない、またはユーザー情報・勤務パターンがない場合は、所定外労働0分とします。
        if (!$this->clock_in_time || !$this->clock_out_time || !$this->user || !$this->user->workPattern) {
            return 0;
        }

        try {
            // clock_in_time と clock_out_time が有効なCarbonインスタンスにパースできるか確認します。
            // (これらは既に$castsでCarbonインスタンスになっているはずですが、念のためのチェック)
            Carbon::parse($this->clock_in_time);
            Carbon::parse($this->clock_out_time);
        } catch (\Exception $e) {
            // 不正な時刻フォーマットの場合は0を返します。
            return 0;
        }

        // attendance_date は $casts によりCarbonインスタンスになっています。
        $attendanceDateCarbon = $this->attendance_date;

        // 勤怠日が土曜日、日曜日、またはHolidayモデルで定義された祝日の場合は、ここでは計算対象外とし0分を返します。
        // (休日出勤の計算は別途行う想定)
        if ($attendanceDateCarbon->isSaturday() ||
            $attendanceDateCarbon->isSunday() ||
            Holiday::where('holiday_date', $attendanceDateCarbon->toDateString())->exists() // Holidayモデルを参照
           ) {
            return 0;
        }

        // 実労働時間（分）を取得します。内部的に getActualWorkMinutesAttribute が呼び出されます。
        $actualWorkMinutes = $this->actual_work_minutes;
        // 正規の所定労働時間（分）を取得します。内部的に getScheduledWorkMinutesAttribute が呼び出されます。
        $scheduledWorkMinutes = $this->scheduled_work_minutes;

        // 実労働時間が0以下の場合、または勤務パターンが設定されているにも関わらず所定労働時間が0以下と計算された場合
        // （例：休憩時間が長すぎる勤務パターン）は、時間外労働も0分とします。
        if ($actualWorkMinutes <= 0 || ($this->user->workPattern && $scheduledWorkMinutes <= 0)) {
            return 0;
        }

        // 勤務パターンが未設定の場合、$scheduledWorkMinutes は0になります。
        // この場合の扱い（実労働時間の全てを時間外とするか、エラーとするかなど）は要件によります。
        // 現在の実装では、勤務パターンがない場合は、$scheduledWorkMinutes が0となり、
        // $actualWorkMinutes がそのまま $overtimeMinutes になり得るため、
        // 上の $this->user->workPattern のチェックで0を返すようにしています。
        // もし勤務パターンなしでも時間外を計算したい場合は、この条件分岐の修正が必要です。

        // 所定外労働時間 = 実労働時間 - 所定労働時間
        $overtimeMinutes = $actualWorkMinutes - $scheduledWorkMinutes;

        // 計算結果がマイナスになる場合（実労働時間が所定労働時間に満たない場合）は0を返します。
        return max(0, $overtimeMinutes);
    }
}
