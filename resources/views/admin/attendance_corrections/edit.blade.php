{{-- resources/views/admin/attendance_corrections/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('打刻記録修正') }} - {{ $attendance->user->name ?? __('不明なユーザー') }}
            ({{ $attendance->attendance_date->isoFormat('Y年M月D日') }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            {{-- バリデーションエラー表示 --}}
            <x-validation-errors class="mb-4"/>

            {{-- フラッシュメッセージ --}}
            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600 bg-green-100 p-3 rounded-md shadow">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('info'))
                <div class="mb-4 font-medium text-sm text-blue-600 bg-blue-100 p-3 rounded-md shadow">
                    {{ session('info') }}
                </div>
            @endif


            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:p-8">
                    <form method="POST" action="{{ route('admin.attendance_corrections.update', $attendance) }}">
                        @csrf
                        @method('PUT')

                        {{-- 出勤時刻 --}}
                        <div class="mt-4">
                            <x-input-label for="clock_in_time" :value="__('出勤時刻 (HH:MM)')"/>
                            <x-text-input id="clock_in_time" class="block mt-1 w-full" type="time" name="clock_in_time"
                                          :value="old('clock_in_time', $attendance->clock_in_time ? $attendance->clock_in_time->format('H:i') : '')"/>
                            <x-input-error :messages="$errors->get('clock_in_time')" class="mt-2"/>
                        </div>

                        {{-- 出勤コメント --}}
                        <div class="mt-4">
                            <x-input-label for="clock_in_comment" :value="__('出勤時コメント')"/>
                            <textarea id="clock_in_comment" name="clock_in_comment"
                                      class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                      rows="2">{{ old('clock_in_comment', $attendance->clock_in_comment) }}</textarea>
                            <x-input-error :messages="$errors->get('clock_in_comment')" class="mt-2"/>
                        </div>

                        {{-- 退勤時刻 --}}
                        <div class="mt-4">
                            <x-input-label for="clock_out_time" :value="__('退勤時刻 (HH:MM)')"/>
                            <x-text-input id="clock_out_time" class="block mt-1 w-full" type="time"
                                          name="clock_out_time"
                                          :value="old('clock_out_time', $attendance->clock_out_time ? $attendance->clock_out_time->format('H:i') : '')"/>
                            <x-input-error :messages="$errors->get('clock_out_time')" class="mt-2"/>
                        </div>

                        {{-- 退勤コメント --}}
                        <div class="mt-4">
                            <x-input-label for="clock_out_comment" :value="__('退勤時コメント')"/>
                            <textarea id="clock_out_comment" name="clock_out_comment"
                                      class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                      rows="2">{{ old('clock_out_comment', $attendance->clock_out_comment) }}</textarea>
                            <x-input-error :messages="$errors->get('clock_out_comment')" class="mt-2"/>
                        </div>

                        {{-- 修正理由 --}}
                        <div class="mt-4">
                            <x-input-label for="modification_reason" :value="__('修正理由 (必須)')"/>
                            <textarea id="modification_reason" name="modification_reason"
                                      class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                      rows="3" required>{{ old('modification_reason') }}</textarea>
                            <x-input-error :messages="$errors->get('modification_reason')" class="mt-2"/>
                        </div>

                        <div class="flex items-center justify-end mt-6 pt-6 border-t">
                            <a href="{{ route('admin.attendance_corrections.index') }}"
                               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 mr-4">
                                {{ __('キャンセル') }}
                            </a>
                            <x-primary-button>
                                {{ __('更新する') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
