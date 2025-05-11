<?php

namespace App\Models;
// App\Models 名前空間に属することを示す
use Illuminate\Database\Eloquent\Model;

// Eloquent Model クラスをインポート

/**
 * 祝日モデル
 *
 * 祝日情報を扱う Eloquent モデルです。
 * データベースの 'holidays' テーブル（Laravelの命名規則によるデフォルト）と対応します。
 */
class Holiday extends Model
{
    /**
     * 複数代入可能な属性
     *
     * create メソッドや update メソッドなどで、一度に設定できる属性のリストです。
     * セキュリティのため、意図しない属性の更新を防ぎます。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'holiday_date', // 祝日の日付を格納するカラム
        'name',         // 祝日の名称を格納するカラム
    ];

    /**
     * 属性の型キャスト
     *
     * モデルの属性を特定のデータ型に自動的に変換するための定義です。
     *
     * @var array<string, string>
     */
    protected $casts = [
        'holiday_date' => 'date', // 'holiday_date' 属性を Carbon インスタンス（日付オブジェクト）として扱う
    ];
}
