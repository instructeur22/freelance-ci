@extends('layouts.app')

@section('title', 'Privacy Policy')

@section('content')
<h1>Privacy Policy</h1>
<p class="text-muted" style="color:#6b7280;margin-bottom:2rem">Last updated: June 2026</p>

<div class="card">
    <h2>1. Information We Collect</h2>
    <p>We collect information you provide when registering: name, email address, phone number, and professional details. We also collect data about your usage of the Platform, including projects, messages, and transactions.</p>
</div>

<div class="card">
    <h2>2. How We Use Your Data</h2>
    <ul>
        <li>To provide and improve our services</li>
        <li>To process payments and manage escrow</li>
        <li>To verify your identity and prevent fraud</li>
        <li>To send notifications about your account and activity</li>
        <li>To comply with legal obligations</li>
    </ul>
</div>

<div class="card">
    <h2>3. Data Sharing</h2>
    <p>We share your information with Genius Pay for payment processing. We do not sell your personal data to third parties. Profile information you choose to make public (name, skills, portfolio) is visible to other users.</p>
</div>

<div class="card">
    <h2>4. Data Security</h2>
    <p>We implement industry-standard security measures including encryption at rest and in transit, secure authentication via Supabase, and regular security audits.</p>
</div>

<div class="card">
    <h2>5. Your Rights</h2>
    <p>You may access, correct, or delete your personal data at any time through your account settings. You may request a copy of your data by contacting us.</p>
</div>

<div class="card">
    <h2>6. Cookies</h2>
    <p>We use essential cookies for authentication and session management. No third-party tracking cookies are used without consent.</p>
</div>

<div class="card">
    <h2>7. Contact</h2>
    <p>For privacy-related inquiries, please <a href="{{ url('/contact') }}" style="color:#f97316;text-decoration:underline">contact our Data Protection Officer</a>.</p>
</div>
@endsection
