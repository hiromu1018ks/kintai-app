<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('休日詳細') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:p-8">
                    <p><strong>{{ __('日付') }}:</strong> {{ $holiday->holiday_date->isoFormat('Y年M月D日 (ddd)') }}</p>
                    <p><strong>{{ __('休日名') }}:</strong> {{ $holiday->name }}</p>
                    <div class="mt-6">
                        <a href="{{ route('admin.holidays.edit', $holiday) }}"
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
                            {{ __('編集') }}
                        </a>
                        <a href="{{ route('admin.holidays.index') }}"
                           class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('一覧に戻る') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
