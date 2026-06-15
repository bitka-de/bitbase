@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <header class="auth-header">
        <p class="auth-kicker">Sicherer Bereich</p>
        <h1 class="auth-title">Willkommen zuruck</h1>
        <p class="auth-subtitle">Melde dich mit deinen Zugangsdaten an, um den Admin-Bereich zu erreichen.</p>
    </header>

    @if ($errors->any())
        <div class="alert alert-danger auth-alert">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf

        <div class="auth-field">
            <label for="name" class="label">Benutzername</label>
            <div class="auth-input-wrap">
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus placeholder="z. B. admin" class="input auth-input-with-icon">
                <x-heroicon-o-user class="auth-input-icon" aria-hidden="true" />
            </div>
        </div>

        <div class="auth-field">
            <label for="password" class="label">Passwort</label>
            <div class="auth-input-wrap">
                <input id="password" name="password" type="password" required placeholder="Dein Passwort" class="input auth-input-with-icon">
                <x-heroicon-o-lock-closed class="auth-input-icon" aria-hidden="true" />
            </div>
        </div>

        <div class="auth-actions">
            <button type="submit" class="btn btn-primary">
                <span>Anmelden</span>
            </button>
        </div>
    </form>

    <footer class="auth-footer">
        <a href="{{ route('home') }}" class="soft-link auth-back-link">Zur Startseite</a>
    </footer>
@endsection