{{-- resources/views/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ダッシュボード') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                {{-- resources/views/dashboard.blade.php の適切な場所に追加 --}}
                @if (session('status'))
                    <div class="mb-4 font-medium text-sm text-green-600 bg-green-100 p-3 rounded-md">
                        {{ session('status') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-4 font-medium text-sm text-red-600 bg-red-100 p-3 rounded-md">
                        {{ session('error') }}
                    </div>
                @endif
                <div class="p-6 text-gray-900">
                    <p>{{ $user->name }} さん、こんにちは！</p>
                    <p>現在時刻: <span id="current-time">{{ now()->format('H:i:s') }}</span></p>

                    {{-- 打刻エリア --}}
                    <div class="mt-4">
                        <form method="POST" action="{{ route('attendances.clock_in') }}" class="inline-block">
                            @csrf
                            <x-primary-button :disabled="$todaysAttendance && $todaysAttendance->clock_in_time">
                                {{ __('出勤') }}
                            </x-primary-button>
                            @if ($todaysAttendance && $todaysAttendance->clock_in_time)
                                <span
                                    class="ml-2 text-sm text-gray-600">出勤済み: {{ $todaysAttendance->clock_in_time->format('H:i') }}</span>
                            @endif
                        </form>

                        <form method="POST" action="{{ route('attendances.clock_out') }}" class="inline-block ml-4">
                            @csrf
                            <x-primary-button
                                :disabled="!$todaysAttendance || !$todaysAttendance->clock_in_time || ($todaysAttendance && $todaysAttendance->clock_out_time)">
                                {{ __('退勤') }}
                            </x-primary-button>
                            @if ($todaysAttendance && $todaysAttendance->clock_out_time)
                                <span
                                    class="ml-2 text-sm text-gray-600">退勤済み: {{ $todaysAttendance->clock_out_time->format('H:i') }}</span>
                            @endif
                        </form>
                    </div>

                    {{-- 打刻コメント入力エリア --}}
                    {{-- （これは出勤/退勤ボタンと連動させるか、別途モーダルなどで入力させるかなど検討） --}}
                    <div class="mt-4">
                        <form method="POST" action="{{-- コメント保存用ルート（未作成） --}}">
                            @csrf
                            <div>
                                <x-input-label for="attendance_comment" :value="__('打刻時コメント（任意）')"/>
                                {{-- 修正箇所 --}}
                                <textarea id="attendance_comment" name="attendance_comment"
                                          class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                          rows="2"></textarea>
                            </div>
                            <div class="mt-2">
                                {{-- コメント保存ボタンなど --}}
                            </div>
                        </form>
                    </div>

                    {{-- 本日の打刻状況表示 --}}
                    <div class="mt-6">
                        <h3 class="text-lg font-medium text-gray-900">本日の打刻状況</h3>
                        @if ($todaysAttendance)
                            <p>
                                出勤: {{ $todaysAttendance->clock_in_time ? $todaysAttendance->clock_in_time->format('Y-m-d H:i:s') : '未打刻' }}
                                @if($todaysAttendance->clock_in_comment)
                                    (コメント: {{ $todaysAttendance->clock_in_comment }})
                                @endif
                            </p>
                            <p>
                                退勤: {{ $todaysAttendance->clock_out_time ? $todaysAttendance->clock_out_time->format('Y-m-d H:i:s') : '未打刻' }}
                                @if($todaysAttendance->clock_out_comment)
                                    (コメント: {{ $todaysAttendance->clock_out_comment }})
                                @endif
                            </p>
                        @else
                            <p>本日の打刻記録はありません。</p>
                        @endif
                    </div>

                    {{-- JavaScriptで現在時刻を更新（任意） --}}
                    <script>
                        setInterval( function () {
                            const now = new Date();
                            const hours = String( now.getHours() ).padStart( 2, '0' );
                            const minutes = String( now.getMinutes() ).padStart( 2, '0' );
                            const seconds = String( now.getSeconds() ).padStart( 2, '0' );
                            document.getElementById( 'current-time' ).textContent = hours + ":" + minutes + ":" + seconds;
                        }, 1000 );
                    </script>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
