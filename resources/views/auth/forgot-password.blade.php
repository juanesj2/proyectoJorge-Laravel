@extends('layouts.app')

@section('content')
    <x-guest-layout>
    <img src="{{ asset('imagenes/logo_ENFOKA-sin-fondo.png') }}" alt="Logo" class="img-fluid mx-auto d-block mb-3" style="max-width: 150px;">
        <div class="mb-4 text-sm text-gray-600">
            {{ __('¿Olvidaste tu contraseña? No hay problema. Pon tu correo y la recuperaremos, pero intenta no olvidarla otra vez') }}
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-primary-button>
                    {{ __('Enviar Correo') }}
                </x-primary-button>
            </div>
        </form>
    </x-guest-layout>
@endsection