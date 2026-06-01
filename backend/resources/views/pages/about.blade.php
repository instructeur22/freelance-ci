@extends('layouts.app')

@section('title', 'About')

@section('content')
<div class="hero">
    <h1>About Freelance CI</h1>
    <p>Connecting talented freelancers with ambitious clients across Africa and beyond.</p>
</div>

<div class="card">
    <h2>Our Mission</h2>
    <p>Freelance CI is a marketplace built for the African freelance ecosystem. We empower freelancers to showcase their skills, find meaningful projects, and get paid securely — while giving clients access to vetted, high-quality talent.</p>
</div>

<div class="grid-3" style="margin-top:2rem">
    <div class="card">
        <h2>For Freelancers</h2>
        <p>Create your professional profile, bid on projects, manage contracts, and receive payments through Genius Pay — all in one place. Build your reputation with reviews and a verified badge.</p>
    </div>
    <div class="card">
        <h2>For Clients</h2>
        <p>Post projects with clear requirements, review proposals from qualified freelancers, manage milestones, and release payments only when work is delivered to your satisfaction.</p>
    </div>
    <div class="card">
        <h2>Secure & Transparent</h2>
        <p>Our escrow system protects both parties. Funds are held securely and released upon milestone completion. Dispute resolution is handled by our team to ensure fair outcomes.</p>
    </div>
</div>

<div class="card" style="margin-top:2rem">
    <h2>Our Values</h2>
    <ul>
        <li><strong>Trust</strong> — Verified profiles, transparent reviews, secure payments.</li>
        <li><strong>Excellence</strong> — Curated talent pool with skill-based matching.</li>
        <li><strong>Innovation</strong> — Mobile money payments, real-time messaging, smart contracts.</li>
        <li><strong>Community</strong> — Building a pan-African network of professionals.</li>
    </ul>
</div>
@endsection
