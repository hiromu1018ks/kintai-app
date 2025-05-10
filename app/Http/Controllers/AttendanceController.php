<?php

namespace App\Http\Controllers;
// このファイルが属する名前空間を定義します。コントローラークラスがまとめられています。

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// EloquentモデルであるAttendanceクラスをインポートします。データベースのattendancesテーブルと連携します。
// 日付と時刻を操作するためのCarbonライブラリをインポートします。
// HTTPリクエストの情報を扱うためのRequestクラスをインポートします。
// 認証関連の機能を提供するAuthファサードをインポートします。

/**
 * AttendanceControllerクラス
 * 勤怠打刻（出勤・退勤）に関する処理を担当するコントローラーです。
 */
class AttendanceController extends Controller // App\Http\Controllers\Controllerクラスを継承しています。
{
    /**
     * clockInメソッド
     * 出勤打刻処理を行います。
     *
     * @param Request $request HTTPリクエストオブジェクト。リクエストパラメータを含みます。
     * @return \Illuminate\Http\RedirectResponse ダッシュボード画面へリダイレクトします。処理結果に応じてメッセージを付与します。
     */
    public function clockIn(Request $request)
    {
        // 現在認証されているユーザーの情報を取得します。
        $user = Auth::user();
        // Carbonライブラリを使って、本日の日付を 'YYYY-MM-DD' 形式の文字列で取得します。
        $todayDateString = Carbon::today()->toDateString();
        // Carbonライブラリを使って、現在の日時を取得します。
        $now = Carbon::now();

        // 本日の打刻記録を取得または新規作成します。
        // user_idとattendance_dateが一致する最初のレコードを検索し、存在しない場合は新しいAttendanceモデルインスタンスを作成します。
        $attendance = Attendance::firstOrNew([
            'user_id' => $user->id, // 認証ユーザーのID
            'attendance_date' => $todayDateString, // 本日の日付
        ]);

        // 既に出勤打刻が記録されているか確認します。
        // $attendance->exists はレコードがデータベースに存在するかどうかを示します。
        // $attendance->clock_in_time は出勤時刻が記録されているかどうかを示します。
        if ($attendance->exists && $attendance->clock_in_time) {
            // 既に打刻済みの場合の処理
            // ダッシュボード画面にリダイレクトし、エラーメッセージをセッションに保存します。
            return to_route('dashboard')->with('error', 'すでに出勤打刻済みです');
        } else {
            // 未打刻または新規レコードの場合の処理
            // Attendanceモデルの各プロパティに値を設定します。
            $attendance->user_id = $user->id; // ユーザーID
            $attendance->attendance_date = $todayDateString; // 打刻日
            $attendance->clock_in_time = $now; // 現在時刻を出勤時刻として設定
            // $attendance->clock_in_comment = $request->input('comment'); // コメント機能実装時にコメントを解除・修正して、リクエストからコメントを取得・保存します。

            // 設定した内容でデータベースに保存（または更新）します。
            $attendance->save();

            // ダッシュボード画面にリダイレクトし、成功メッセージをセッションに保存します。
            return to_route('dashboard')->with('status', '出勤打刻しました');
        }
    }

    /**
     * clockOutメソッド
     * 退勤打刻処理を行います。
     *
     * @param Request $request HTTPリクエストオブジェクト。リクエストパラメータを含みます。
     * @return \Illuminate\Http\RedirectResponse ダッシュボード画面へリダイレクトします。処理結果に応じてメッセージを付与します。
     */
    public function clockOut(Request $request)
    {
        // 現在認証されているユーザーの情報を取得します。
        $user = Auth::user();
        // Carbonライブラリを使って、本日の日付オブジェクトを取得します。
        $todayDateString = Carbon::today()->toDateString();
        // Carbonライブラリを使って、現在の日時を取得します。
        $now = Carbon::now();

        // 認証ユーザーの今日の勤怠記録をデータベースから検索します。
        // user_idが一致し、かつattendance_dateが今日である最初のレコードを取得します。
        $attendance = Attendance::where('user_id', $user->id)
            ->where('attendance_date', $todayDateString) // Carbonオブジェクトを渡すと、自動的に 'YYYY-MM-DD' 形式で比較されます。
            ->first(); // 条件に一致する最初のレコードを取得します。見つからない場合はnullが返ります。

        // 勤怠記録が存在し、出勤打刻がされており、かつ退勤打刻がまだされていない場合
        if ($attendance && $attendance->clock_in_time && !$attendance->clock_out_time) {
            // 退勤時刻を現在の日時で設定します。
            $attendance->clock_out_time = $now;
            // $attendance->clock_out_comment = $request->input('comment'); // コメント機能実装時にコメントを解除・修正して、リクエストからコメントを取得・保存します。

            // 変更をデータベースに保存します。
            $attendance->save();
            // ダッシュボード画面にリダイレクトし、成功メッセージをセッションに保存します。
            return to_route('dashboard')->with('status', '退勤打刻しました');
        } elseif (!$attendance || !$attendance->clock_in_time) {
            // 勤怠記録が存在しない、または出勤打刻がされていない場合
            // ダッシュボード画面にリダイレクトし、エラーメッセージをセッションに保存します。
            return to_route('dashboard')->with('error', '先に出勤打刻をしてください');
        } else {
            // それ以外の場合（つまり、既に出勤打刻済みで、かつ退勤打刻も済んでいる場合）
            // ダッシュボード画面にリダイレクトし、エラーメッセージをセッションに保存します。
            return to_route('dashboard')->with('error', 'すでに退勤打刻済みです');
        }
    }
}
