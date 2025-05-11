{{-- resources/views/admin/holidays/_form.blade.php --}}
@csrf
<div>
    <x-input-label for="holiday_date" :value="__('日付')"/>
    <x-text-input id="holiday_date" class="block mt-1 w-full" type="date" name="holiday_date"
                  :value="old('holiday_date', isset($holiday) ? $holiday->holiday_date->format('Y-m-d') : '')" required
                  autofocus/>
    <x-input-error :messages="$errors->get('holiday_date')" class="mt-2"/>
</div>

<div class="mt-4">
    <x-input-label for="name" :value="__('休日名')"/>
    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $holiday->name ?? '')"
                  required/>
    <x-input-error :messages="$errors->get('name')" class="mt-2"/>
</div>

<div class="flex items-center justify-end mt-8 pt-6 border-t border-gray-200"> {{-- 少し上に余白と区切り線を追加 --}}
    <a href="{{ route('admin.holidays.index') }}"
       class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 mr-4">
        {{ __('キャンセル') }}
    </a>
    <x-primary-button>
        {{ isset($holiday) ? __('更新') : __('登録') }}
    </x-primary-button>
</div>
