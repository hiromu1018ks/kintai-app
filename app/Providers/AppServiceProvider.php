<?php

namespace App\Providers;
// このファイルが App\Providers 名前空間に属することを示します。

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

// App\Models 名前空間の User モデルをインポートします。これにより、このファイル内で User クラスを短縮名で利用できます。
// Illuminate\Support\Facades 名前空間の Gate ファサードをインポートします。これにより、認可機能を簡単に利用できます。
// Illuminate\Support 名前空間の ServiceProvider クラスをインポートします。これは、すべてのサービスプロバイダの基底クラスです。

/**
 * アプリケーションサービスプロバイダクラス
 *
 * このクラスは、アプリケーションのサービスを登録したり、起動時の処理を定義したりします。
 * Laravelのサービスコンテナやイベントリスナーなどを設定する中心的な場所の一つです。
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * アプリケーションのサービスを登録します。
     *
     * このメソッドは、サービスコンテナへのサービスの結合（バインディング）などを行います。
     * 依存性の注入（DI）の設定などがここに含まれます。
     * このメソッドは、すべてのサービスプロバイダの登録処理が完了した後に呼び出されます。
     * 現状、このメソッド内には具体的な処理は記述されていません。
     *
     * @return void このメソッドは値を返しません。
     */
    public function register(): void
    {
        // TODO: 必要に応じて、サービスコンテナへの登録処理を記述します。
    }

    /**
     * アプリケーションのサービスを起動します。
     *
     * このメソッドは、すべてのサービスプロバイダの `register` メソッドが呼び出された後に実行されます。
     * イベントリスナーの登録、ルートの定義、ビューコンポーザの登録など、
     * アプリケーションがリクエストを処理する準備ができた後に行うべき処理を記述します。
     *
     * @return void このメソッドは値を返しません。
     */
    public function boot(): void
    {
        // Gate ファサードを使用して、'viewAdminFeatures' という名前の認可ポリシーを定義します。
        // このポリシーは、ユーザーが管理者向けの機能にアクセスできるかどうかを判断するために使用されます。
        Gate::define('viewAdminFeatures', function (User $user) {
            // 第1引数にはポリシー名 'viewAdminFeatures' を指定します。
            // 第2引数にはクロージャ（無名関数）を指定し、このクロージャが認可のロジックを定義します。
            // クロージャは、現在認証されている App\Models\User オブジェクトを引数として受け取ります。

            // ユーザーオブジェクト ($user) に紐づく role (役割) が存在し、
            // かつ、その role の name (名前) が 'システム管理者' である場合に true を返します。
            // これにより、'システム管理者' のロールを持つユーザーのみが 'viewAdminFeatures' の権限を持つことになります。
            // $user->role は、User モデルに定義されている role() リレーションシップを通じて Role モデルのインスタンスを取得します。
            // $user->role->name は、取得した Role モデルの name プロパティを参照します。
            return $user->role && $user->role->name === 'システム管理者';
        });
    }
}
