<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('休日編集') }} : {{ $holiday->name }} ({{ $holiday->holiday_date->isoFormat('Y年M月D日') }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:p-8">
                    <form method="POST" action="{{ route('admin.holidays.update', $holiday) }}">
                        @method('PUT')
                        @include('admin.holidays._form', ['holiday' => $holiday])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
