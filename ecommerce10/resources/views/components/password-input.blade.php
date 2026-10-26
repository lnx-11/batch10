@props(['id', 'name', 'autocomplete'])

<div x-data="{ passwordVisible: false }" class="relative">
    <x-text-input
        {{ $attributes->merge([
            'id' => $id,
            'name' => $name,
            'type' => 'password',
            'autocomplete' => $autocomplete,
            'class' => 'pr-12',
        ]) }}
        x-bind:type="passwordVisible ? 'text' : 'password'"
    />
    <button
        type="button"
        class="absolute inset-y-0 right-0 flex items-center rounded-r-md px-3 text-gray-500 hover:text-[#5146e5] focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[#5146e5]"
        x-on:click="passwordVisible = !passwordVisible"
        x-bind:aria-label="passwordVisible ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
        x-bind:aria-pressed="passwordVisible.toString()"
        aria-controls="{{ $id }}"
    >
        <svg x-show="!passwordVisible" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-7 9.75-7 9.75 7 9.75 7-3.5 7-9.75 7-9.75-7-9.75-7Z" />
            <circle cx="12" cy="12" r="3" />
        </svg>
        <svg x-show="passwordVisible" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.58 10.59a2 2 0 0 0 2.83 2.83M9.88 5.1A10.7 10.7 0 0 1 12 4.88c6.25 0 9.75 7.12 9.75 7.12a16.4 16.4 0 0 1-3.04 3.83M6.23 6.23C3.7 7.94 2.25 12 2.25 12s3.5 7.12 9.75 7.12c1.2 0 2.26-.28 3.2-.72" />
        </svg>
    </button>
</div>
