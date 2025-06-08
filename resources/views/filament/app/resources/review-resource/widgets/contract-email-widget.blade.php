@php
$username = request()->user()->username;
$email_domain = config('app.email_forwading_domain');
$email = "{$username}@{$email_domain}";
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <h2 class="font-semibold">Upload contracts via Email</h2>
        <p class="text-sm text-gray-600">
            To upload contracts via email, forward or send an email with the attached PDF document to <span class="font-medium">{{$email}}</span>
        </p>
    </x-filament::section>
</x-filament-widgets::widget>
