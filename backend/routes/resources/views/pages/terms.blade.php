@extends('layouts.app')

@section('title', 'Terms of Service')

@section('content')
<h1>Terms of Service</h1>
<p class="text-muted" style="color:#6b7280;margin-bottom:2rem">Last updated: June 2026</p>

<div class="card">
    <h2>1. Acceptance of Terms</h2>
    <p>By accessing or using Freelance CI, you agree to be bound by these Terms of Service. If you do not agree, please do not use the platform.</p>
</div>

<div class="card">
    <h2>2. Definitions</h2>
    <ul>
        <li><strong>"Platform"</strong> refers to Freelance CI, operated by Freelance CI SARL.</li>
        <li><strong>"Client"</strong> refers to a user posting projects and hiring freelancers.</li>
        <li><strong>"Freelancer"</strong> refers to a user offering services and bidding on projects.</li>
        <li><strong>"Project"</strong> refers to work posted by a Client on the Platform.</li>
        <li><strong>"Contract"</strong> refers to the agreement between Client and Freelancer for a Project.</li>
    </ul>
</div>

<div class="card">
    <h2>3. User Accounts</h2>
    <p>You must register an account to use the Platform. You are responsible for maintaining the confidentiality of your credentials. You must provide accurate information and keep it updated.</p>
</div>

<div class="card">
    <h2>4. Fees & Payments</h2>
    <p>Platform fees are calculated as a percentage of each contract value. Freelancers may subscribe to paid plans (Pro, Expert) for additional features. All payments are processed via Genius Pay. Funds are held in escrow until milestones are delivered and accepted.</p>
</div>

<div class="card">
    <h2>5. Dispute Resolution</h2>
    <p>In the event of a dispute between Client and Freelancer, both parties agree to attempt resolution through the Platform's dispute system. If unresolved, Freelance CI may mediate and issue a final decision regarding escrow funds.</p>
</div>

<div class="card">
    <h2>6. Limitation of Liability</h2>
    <p>Freelance CI acts as a marketplace and intermediary. We are not responsible for the quality, safety, or legality of services provided. Our liability is limited to the fees paid for the specific transaction in question.</p>
</div>

<div class="card">
    <h2>7. Termination</h2>
    <p>Either party may terminate their account at any time. Freelance CI reserves the right to suspend or terminate accounts that violate these terms or engage in fraudulent activity.</p>
</div>

<div class="card">
    <h2>8. Contact</h2>
    <p>For questions about these terms, please <a href="{{ url('/contact') }}" style="color:#f97316;text-decoration:underline">contact us</a>.</p>
</div>
@endsection
