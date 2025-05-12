<?php

namespace App\Http\Controllers\Admin;
// 名前空間の定義: このコントローラーが属する場所を示します。

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// App\Http\Controllers\Controllerクラスをインポートします。これは基底コントローラークラスです。
// App\Models\Attendanceモデルをインポートします。勤怠データを扱います。
// Carbonライブラリをインポートします。日付と時刻の操作に使います。
// Illuminate\Http\Requestクラスをインポートします。HTTPリクエストの情報を扱います。
// Illuminate\Support\Facades\Authファサードをインポートします。認証関連の機能を使います。

/**
 * 管理者向けの勤怠修正コントローラークラス
 *
 * このコントローラーは、管理者による勤怠記録の閲覧、編集、更新機能を提供します。
 */
class AttendanceCorrectionController extends Controller
{
    /**
     * 勤怠記録の一覧を表示します。
     *
     * ユーザー名や日付範囲での検索機能も提供します。
     *
     * @param Request $request HTTPリクエストオブジェクト
     * @return \Illuminate\Contracts\View\View 勤怠記録一覧ビュー
     */
    public function index(Request $request)
    {
        // リクエストから検索条件を取得します。
        $searchUserName = $request->input('user_name'); // 検索するユーザー名
        $searchDateFrom = $request->input('date_from'); // 検索する開始日
        $searchDateTo = $request->input('date_to');     // 検索する終了日

        // 勤怠記録を取得するためのクエリビルダを初期化します。
        // 'user'リレーションを事前にロードし（Eager Loading）、N+1問題を回避します。
        // attendance_dateの降順、次にuser_idの昇順で並び替えます。
        $query = Attendance::with('user')
            ->orderBy('attendance_date', 'desc')
            ->orderBy('user_id', 'asc');

        // ユーザー名による検索条件がある場合
        if ($searchUserName) {
            // 'user'リレーション先の'name'カラムで部分一致検索を行います。
            $query->whereHas('user', function ($q) use ($searchUserName) {
                $q->where('name', 'like', '%' . $searchUserName . '%');
            });
        }

        // 開始日の検索条件がある場合
        if ($searchDateFrom) {
            // attendance_dateが指定された開始日以降である記録を検索します。
            $query->where('attendance_date', '>=', $searchDateFrom);
        }

        // 終了日の検索条件がある場合
        if ($searchDateTo) {
            // attendance_dateが指定された終了日以前である記録を検索します。
            $query->where('attendance_date', '<=', $searchDateTo);
        }

        // 検索条件に基づいて勤怠記録を取得し、1ページあたり15件でページネーションします。
        $attendances = $query->paginate(15);

        // 取得した勤怠記録をビューに渡して表示します。
        return view('admin.attendance_corrections.index', compact('attendances', 'searchUserName', 'searchDateFrom', 'searchDateTo'));
    }

    /**
     * 指定された勤怠記録の編集画面を表示します。
     *
     * @param Attendance $attendance 編集対象の勤怠記録モデルインスタンス (ルートモデルバインディングにより自動的に取得されます)
     * @return \Illuminate\Contracts\View\View 勤怠記録編集ビュー
     */
    public function edit(Attendance $attendance)
    {
        // 編集対象の勤怠記録をビューに渡して表示します。
        return view('admin.attendance_corrections.edit', compact('attendance'));
    }

    /**
     * 指定された勤怠記録を更新します。
     *
     * バリデーションを行い、変更があった場合にのみデータベースを更新します。
     *
     * @param Request $request HTTPリクエストオブジェクト
     * @param Attendance $attendance 更新対象の勤怠記録モデルインスタンス (ルートモデルバインディングにより自動的に取得されます)
     * @return \Illuminate\Http\RedirectResponse 更新結果に応じてリダイレクトします。
     */
    public function update(Request $request, Attendance $attendance)
    {
        // リクエストデータをバリデーションします。
        $validatedData = $request->validate([
            'clock_in_time' => 'nullable|date_format:H:i', // 出勤時刻: 空でもOK、H:i形式（例: 09:00）であること
            'clock_out_time' => 'nullable|date_format:H:i', // 退勤時刻: 空でもOK、H:i形式
            'clock_in_comment' => 'nullable|string|max:255', // 出勤コメント: 空でもOK、文字列、最大255文字
            'clock_out_comment' => 'nullable|string|max:255', // 退勤コメント: 空でもOK、文字列、最大255文字
            'modification_reason' => 'required|string|max:255', // 修正理由: 必須、文字列、最大255文字
        ]);

        // 勤怠記録の対象日付を取得します（時刻部分は除去）。
        $attendanceDate = $attendance->attendance_date->toDateString(); // 例: "2023-10-27"

        // 出勤時刻が入力されている場合
        if ($request->filled('clock_in_time')) {
            // 勤怠日付と入力された時刻を組み合わせてCarbonオブジェクトに変換し、出勤時刻として設定します。
            $attendance->clock_in_time = Carbon::parse($attendanceDate . ' ' . $validatedData['clock_in_time']);
        } else {
            // 出勤時刻が空で送信された場合は、nullを設定します。
            $attendance->clock_in_time = null;
        }

        // 退勤時刻が入力されている場合
        if ($request->filled('clock_out_time')) {
            // 勤怠日付と入力された時刻を組み合わせてCarbonオブジェクトに変換し、退勤時刻として設定します。
            $attendance->clock_out_time = Carbon::parse($attendanceDate . ' ' . $validatedData['clock_out_time']);
        } else {
            // 退勤時刻が空で送信された場合は、nullを設定します。（打刻なしの状態に戻すため）
            $attendance->clock_out_time = null;
        }

        // 出勤コメントと退勤コメントをバリデーション済みデータで更新します。
        $attendance->clock_in_comment = $validatedData['clock_in_comment'];
        $attendance->clock_out_comment = $validatedData['clock_out_comment'];

        // 変更があったかどうかを追跡するフラグ
        $modified = false;

        // 出勤時刻または出勤コメントが変更された場合
        if ($attendance->isDirty('clock_in_time') || $attendance->isDirty('clock_in_comment')) {
            $attendance->clock_in_modified_by = Auth::id(); // 出勤関連の修正者IDとして現在認証中のユーザーIDを設定
            $attendance->clock_in_modification_reason = $validatedData['modification_reason']; // 出勤関連の修正理由を設定
            $modified = true; // 変更フラグを立てる
        }

        // 退勤時刻または退勤コメントが変更された場合
        if ($attendance->isDirty('clock_out_time') || $attendance->isDirty('clock_out_comment')) {
            $attendance->clock_out_modified_by = Auth::id(); // 退勤関連の修正者IDとして現在認証中のユーザーIDを設定
            $attendance->clock_out_modification_reason = $validatedData['modification_reason']; // 退勤関連の修正理由を設定
            $modified = true; // 変更フラグを立てる
        }

        // もし、出勤・退勤それぞれではなく、レコード全体で単一の修正者・理由カラムで管理する場合のコメントアウト例
        // (例: last_modified_by, last_modification_reason のようなカラムを `attendances` テーブルに追加した場合)
        // if ($modified) { // $modified は上記のロジックで true になっているはず
        //     $attendance->last_modified_by = Auth::id();
        //     $attendance->last_modification_reason = $validatedData['modification_reason'];
        // }

        // 何かしらの変更があった場合 (上記で$modifiedがtrueになったか、それ以外の属性が変更された場合も含む)
        if ($modified || $attendance->isDirty()) {
            $attendance->save(); // 変更をデータベースに保存します。
            // 勤怠記録一覧ページにリダイレクトし、成功メッセージを表示します。
            return redirect()->route('admin.attendance_corrections.index')
                ->with('status', '打刻記録を更新しました。');
        }

        // 変更点がなかった場合
        // 勤怠記録編集ページにリダイレクトし、情報メッセージを表示します。
        return redirect()->route('admin.attendance_corrections.edit', $attendance)
            ->with('info', '変更点はありませんでした。');
    }
}
