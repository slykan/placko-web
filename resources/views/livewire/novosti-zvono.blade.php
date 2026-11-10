<div x-data="{ novo: {{ $neprocitano }} }" class="flex items-center">
    <x-filament::dropdown placement="bottom-end" width="sm" teleport>
        <x-slot name="trigger">
            <button
                type="button"
                x-on:click="if (novo) { novo = 0; $wire.oznaciProcitano() }"
                class="relative flex h-9 w-9 items-center justify-center rounded-full bg-white text-gray-500 shadow-sm ring-1 ring-gray-200 transition dark:bg-white/5 hover:bg-gray-50 hover:text-gray-700 dark:text-gray-400 dark:ring-white/20 dark:hover:bg-white/5 dark:hover:text-gray-200"
                aria-label="Novosti u plačko.app"
                title="Novosti u plačko.app"
            >
                <x-filament::icon icon="heroicon-o-bell" class="h-5 w-5" />
                <span
                    x-show="novo"
                    x-cloak
                    x-text="novo"
                    style="position:absolute; top:-4px; right:-4px; min-width:18px; height:18px; padding:0 5px; border-radius:9999px; background:#dc2626; color:#fff; font-size:11px; font-weight:700; line-height:18px; text-align:center; box-shadow:0 0 0 2px #fff;"
                ></span>
            </button>
        </x-slot>

        <div class="px-4 py-2">
            <p class="text-sm font-semibold text-gray-950 dark:text-white">Novo u plačko.app</p>
        </div>

        <x-filament::dropdown.list>
            @forelse($novosti as $n)
                <div class="flex items-start gap-3 rounded-md px-2 py-2">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm text-gray-950 dark:text-white {{ $n['novo'] ? 'font-semibold' : '' }}">
                            @if($n['novo'])<span style="display:inline-block;width:7px;height:7px;border-radius:9999px;background:#dc2626;margin-right:5px;vertical-align:middle;"></span>@endif{{ $n['title'] }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $n['datum'] }}</p>
                    </div>
                    <x-filament::button
                        tag="a"
                        size="xs"
                        color="gray"
                        :href="route('novosti.show', $n['slug'])"
                        target="_blank"
                    >
                        Više
                    </x-filament::button>
                </div>
            @empty
                <p class="px-2 py-2 text-sm text-gray-500">Još nema novosti.</p>
            @endforelse
        </x-filament::dropdown.list>

        <x-filament::dropdown.list>
            <x-filament::dropdown.list.item
                tag="a"
                :href="url('/novosti?kategorija=novosti')"
                target="_blank"
                icon="heroicon-o-newspaper"
            >
                Sve novosti
            </x-filament::dropdown.list.item>
            {{-- Ponuda push obavijesti samo dok ih preglednik još nije dopustio (vidi partials/push) --}}
            <div x-data="{ ponudi: window.plackoPush?.stanje() === 'nije-ukljuceno' }" x-show="ponudi" x-cloak>
                <x-filament::dropdown.list.item
                    tag="button"
                    icon="heroicon-o-bell-alert"
                    x-on:click="window.plackoPush.ukljuci().finally(() => ponudi = window.plackoPush.stanje() === 'nije-ukljuceno')"
                >
                    Javi mi novosti obaviješću
                </x-filament::dropdown.list.item>
            </div>
        </x-filament::dropdown.list>
    </x-filament::dropdown>
</div>
