@extends('layouts.app')

@section('title', 'Contact')

@section('content')
<div class="hero">
    <h1>Contact Us</h1>
    <p>We'd love to hear from you. Get in touch with our team.</p>
</div>

<div class="grid-3" style="margin-top:2rem">
    <div class="card" style="text-align:center">
        <h2>Email</h2>
        <p>support@freelance-ci.com</p>
        <p style="font-size:.875rem;color:#6b7280">We respond within 24 hours</p>
    </div>
    <div class="card" style="text-align:center">
        <h2>Phone</h2>
        <p>+225 01 02 03 04 05</p>
        <p style="font-size:.875rem;color:#6b7280">Mon-Fri, 8:00-18:00 GMT</p>
    </div>
    <div class="card" style="text-align:center">
        <h2>Address</h2>
        <p>Abidjan, Côte d'Ivoire</p>
        <p style="font-size:.875rem;color:#6b7280">Plateau, Rue des Commerce</p>
    </div>
</div>

<div class="card" style="margin-top:2rem;text-align:center">
    <h2>Need Help?</h2>
    <p>Visit our API documentation or check the platform's built-in help center for quick answers to common questions.</p>
    <div style="margin-top:1.5rem">
        <a href="{{ url('/docs') }}" class="btn btn-primary">API Documentation</a>
    </div>
</div>
@endsection
