{{-- resources/views/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('勤怠打刻') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8"> {{-- 横幅を少し狭めてみる --}}
            {{-- フラッシュメッセージ表示エリア --}}
            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600 bg-green-100 p-3 rounded-md shadow">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 font-medium text-sm text-red-600 bg-red-100 p-3 rounded-md shadow">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg"> {{-- shadow-xlで影を濃く --}}
                <div class="p-6 sm:p-8">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <p class="text-lg font-medium text-gray-700">{{ Auth::user()->name }} さん</p>
                            <p class="text-sm text-gray-500">本日: {{ now()->isoFormat('Y年M月D日 (ddd)') }}</p>
                        </div>
                        <div class="text-3xl font-bold text-gray-800" id="current-time">
                            {{ now()->format('H:i:s') }}
                        </div>
                    </div>

                    {{-- 打刻アクションエリア --}}
                    {{-- 次の打刻が何かを判定して、フォームのアクションや表示を切り替える --}}
                    @php
                        $canClockIn = !$todaysAttendance || !$todaysAttendance->clock_in_time;
                        $canClockOut = $todaysAttendance && $todaysAttendance->clock_in_time && !$todaysAttendance->clock_out_time;
                        $nextAction = '';
                        $nextActionLabel = '';
                        $route = '';
                        $disabled = false;
                        $commentLabel = '';
                        $existingComment = '';

                        if ($canClockIn) {
                            $nextAction = 'clock_in';
                            $nextActionLabel = '出勤';
                            $route = route('attendances.clock_in');
                            $commentLabel = '出勤時コメント（任意）';
                            // $existingComment = old('comment', $todaysAttendance->clock_in_comment ?? ''); // 新規なので不要
                        } elseif ($canClockOut) {
                            $nextAction = 'clock_out';
                            $nextActionLabel = '退勤';
                            $route = route('attendances.clock_out');
                            $commentLabel = '退勤時コメント（任意）';
                            $existingComment = old('comment', $todaysAttendance->clock_out_comment ?? '');
                        } else {
                            // 既に出退勤済み
                            $nextActionLabel = '打刻完了';
                            $disabled = true;
                            $commentLabel = 'コメント'; // 表示のみ
                        }
                    @endphp

                    @if (!$disabled)
                        <form method="POST" action="{{ $route }}">
                            @csrf
                            <div class="mb-4">
                                <x-input-label for="comment" :value="$commentLabel"/>
                                <textarea id="comment" name="comment"
                                          class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                          rows="3">{{ $existingComment }}</textarea>
                                <x-input-error :messages="$errors->get('comment')" class="mt-2"/>
                            </div>

                            <div class="flex items-center justify-end">
                                <x-primary-button class="text-lg px-8 py-3"> {{-- ボタンサイズを少し大きく --}}
                                    {{ $nextActionLabel }} {{-- 打刻実行ボタン --}}
                                </x-primary-button>
                            </div>
                        </form>
                    @else
                        <div class="text-center py-4">
                            <p class="text-lg font-semibold text-green-600">{{ $nextActionLabel }}</p>
                            <p class="text-sm text-gray-500">本日の打刻は完了しています。</p>
                        </div>
                    @endif

                    {{-- 本日の打刻状況表示 --}}
                    <div class="mt-8 border-t pt-6">
                        <h3 class="text-md font-semibold text-gray-700 mb-3">本日の記録</h3>
                        @if ($todaysAttendance)
                            <div class="space-y-2 text-sm">
                                <p><span class="font-medium text-gray-600 w-20 inline-block">出勤:</span>
                                    {{ $todaysAttendance->clock_in_time ? $todaysAttendance->clock_in_time->format('H:i') : '---' }}
                                    @if($todaysAttendance->clock_in_comment)
                                        <span
                                            class="text-xs text-gray-500 block pl-20">- {{ $todaysAttendance->clock_in_comment }}</span>
                                    @endif
                                </p>
                                <p><span class="font-medium text-gray-600 w-20 inline-block">退勤:</span>
                                    {{ $todaysAttendance->clock_out_time ? $todaysAttendance->clock_out_time->format('H:i') : '---' }}
                                    @if($todaysAttendance->clock_out_comment)
                                        <span
                                            class="text-xs text-gray-500 block pl-20">- {{ $todaysAttendance->clock_out_comment }}</span>
                                    @endif
                                </p>
                            </div>
                        @else
                            <p class="text-sm text-gray-500">本日の打刻記録はありません。</p>
                        @endif
                    </div>
                    <div class="mt-4">
                        <p><span class="font-medium text-gray-600 w-36 inline-block">本日の時間外(概算):</span>
                            @if($todaysAttendance && $todaysAttendance->clock_out_time)
                                {{-- 退勤後でないと計算できないため --}}
                                {{ floor($todaysOvertimeMinutes / 60) }}時間 {{ $todaysOvertimeMinutes % 60 }}分
                            @else
                                ---
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScriptで現在時刻を更新 --}}
    <script>
        // ... (時刻表示用のJavaScriptは変更なし) ...
        setInterval( function () {
            const now = new Date();
            const hours = String( now.getHours() ).padStart( 2, '0' );
            const minutes = String( now.getMinutes() ).padStart( 2, '0' );
            const seconds = String( now.getSeconds() ).padStart( 2, '0' );
            document.getElementById( 'current-time' ).textContent = hours + ":" + minutes + ":" + seconds;
        }, 1000 );
    </script>
</x-app-layout>
