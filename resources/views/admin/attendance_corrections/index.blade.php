{{-- resources/views/admin/attendance_corrections/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('打刻記録修正') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- フラッシュメッセージ --}}
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
            @if (session('info'))
                <div class="mb-4 font-medium text-sm text-blue-600 bg-blue-100 p-3 rounded-md shadow">
                    {{ session('info') }}
                </div>
            @endif


            {{-- 検索フォーム --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('admin.attendance_corrections.index') }}">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div>
                                <x-input-label for="user_name" :value="__('職員名')"/>
                                <x-text-input id="user_name" class="block mt-1 w-full" type="text" name="user_name"
                                              :value="$searchUserName"/>
                            </div>
                            <div>
                                <x-input-label for="date_from" :value="__('期間（開始）')"/>
                                <x-text-input id="date_from" class="block mt-1 w-full" type="date" name="date_from"
                                              :value="$searchDateFrom"/>
                            </div>
                            <div>
                                <x-input-label for="date_to" :value="__('期間（終了）')"/>
                                <x-text-input id="date_to" class="block mt-1 w-full" type="date" name="date_to"
                                              :value="$searchDateTo"/>
                            </div>
                            <div class="flex items-end">
                                <x-primary-button type="submit">
                                    {{ __('検索') }}
                                </x-primary-button>
                                <a href="{{ route('admin.attendance_corrections.index') }}"
                                   class="ml-3 inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                    {{ __('クリア') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>


            {{-- 打刻記録一覧 --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:p-8">
                    @if($attendances->isEmpty())
                        <p class="text-gray-500">{{ __('対象の打刻記録はありません。') }}</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('日付') }}
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('職員名') }}
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('出勤時刻') }}
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('退勤時刻') }}
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('出勤コメント') }}
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('退勤コメント') }}
                                    </th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">{{ __('操作') }}</span>
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($attendances as $attendance)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $attendance->attendance_date->isoFormat('Y年M月D日 (ddd)') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $attendance->user->name ?? __('不明なユーザー') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $attendance->clock_in_time ? $attendance->clock_in_time->format('H:i') : '--:--' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $attendance->clock_out_time ? $attendance->clock_out_time->format('H:i') : '--:--' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 max-w-xs truncate">
                                            {{ $attendance->clock_in_comment }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 max-w-xs truncate">
                                            {{ $attendance->clock_out_comment }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('admin.attendance_corrections.edit', $attendance) }}"
                                               class="text-indigo-600 hover:text-indigo-900">{{ __('修正') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $attendances->appends(request()->query())->links() }} {{-- ページネーションリンク (検索条件を維持) --}}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
