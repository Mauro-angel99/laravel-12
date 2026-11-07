@php
    $items = array_filter($section['items'], fn ($item) => $item['visible'] ?? true);
@endphp

{{-- Una sezione con tutte le voci nascoste non deve lasciare un titolo orfano. --}}
@if (count($items) > 0)
    <div class="{{ $class ?? '' }}">
        <p class="mb-1 px-3 text-xs font-medium uppercase tracking-wide text-gray-500">
            {{ __($section['label']) }}
        </p>

        <ul class="flex flex-col gap-0.5">
            @foreach ($items as $item)
                @php
                    $active = request()->routeIs($item['active'] ?? $item['route']);
                @endphp
                <li>
                    <a href="{{ route($item['route']) }}" @click="menuOpen = false"
                        @if ($active) aria-current="page" @endif
                        class="flex items-center gap-3 rounded-md px-3 py-2 text-sm transition-colors {{ $active ? 'bg-gray-200/70 font-medium text-gray-900' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900' }}">
                        <svg class="size-4 shrink-0 {{ $active ? 'text-copam-blue' : '' }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" aria-hidden="true">{!! $icons[$item['icon']] !!}</svg>
                        <span>{{ __($item['label']) }}</span>

                        @if ($item['badge'] ?? false)
                            <span x-show="$store.communications.unread > 0"
                                x-text="$store.communications.unread > 99 ? '99+' : $store.communications.unread"
                                @if ($unreadCommunications === 0) style="display: none" @endif
                                class="ml-auto min-w-5 shrink-0 rounded-full bg-red-600 px-1.5 py-0.5 text-center text-xs font-medium leading-none text-white">{{ $unreadCommunications }}</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
