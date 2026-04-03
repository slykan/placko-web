<x-filament-panels::page.simple>
    @if (filament()->hasRegistration())
        <x-slot name="subheading">
            {{ __('filament-panels::pages/auth/login.actions.register.before') }}
            {{ $this->registerAction }}
        </x-slot>
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

    <x-filament-panels::form id="form"
        @if(config('services.turnstile.key') && !app()->environment('local'))
        x-data="{ submitWithTurnstile(e) { e.preventDefault(); if(!window._tsTokenLogin){ return; } @this.set('turnstileToken', window._tsTokenLogin).then(() => @this.call('authenticate')); } }"
        x-on:submit="submitWithTurnstile($event)"
        @endif
        wire:submit="authenticate">
        {{ $this->form }}

        {{-- Cloudflare Turnstile --}}
        @if(config('services.turnstile.key') && !app()->environment('local'))
        <div wire:ignore>
            <div
                class="cf-turnstile"
                data-sitekey="{{ config('services.turnstile.key') }}"
                data-callback="plackoTsLogin"
                data-theme="light"
            ></div>
        </div>
        @endif

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    {{-- Google gumb --}}
    <div style="margin-top: 1rem;">
        <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.75rem;">
            <div style="flex:1; height:1px; background:#e5e7eb;"></div>
            <span style="font-size:0.75rem; color:#6b7280;">ili</span>
            <div style="flex:1; height:1px; background:#e5e7eb;"></div>
        </div>
        <a href="{{ route('google.redirect') }}"
           style="display:flex; align-items:center; justify-content:center; gap:0.625rem;
                  width:100%; padding:0.625rem 1rem; border:1px solid #d1d5db; border-radius:0.5rem;
                  background:#fff; color:#374151; font-size:0.875rem; font-weight:500;
                  text-decoration:none; transition:background 0.15s;"
           onmouseover="this.style.background='#f9fafb'"
           onmouseout="this.style.background='#fff'">
            <svg width="18" height="18" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Prijavi se s Google
        </a>
    </div>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
</x-filament-panels::page.simple>

@if(config('services.turnstile.key') && !app()->environment('local'))
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<script>
    window._tsTokenLogin = null;
    function plackoTsLogin(token) { window._tsTokenLogin = token; }
</script>
@endif
