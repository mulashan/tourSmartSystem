@extends('templates.app')

@php
    $title = $title ?? match ($page) {
        'invoice-list' => 'Invoice Ledger',
        'invoice-view' => 'Invoice #INV-2024-001',
        'pricing' => 'Choose the plan that matches your operating model',
        default => $title ?? 'Utility Page',
    };
@endphp

@section('content')
    @if($page === 'invoice-list')
        <section class="utility-hero">
            <div>
                <span class="eyebrow">Billing Center</span>
                <h1>Invoice Ledger</h1>
                <p>Monitor invoice performance, collection velocity, and outstanding risk from one operational board.</p>
                <div class="utility-tags"><span><i class="bi bi-calendar"></i> Cycle: February 2026</span><span><i class="bi bi-check-circle"></i> Collection rate: 92.6%</span><span><i class="bi bi-activity"></i> 38 invoices in progress</span></div>
            </div>
            <div class="hero-actions"><button class="outline-btn"><i class="bi bi-file-earmark-spreadsheet"></i> Export</button><button class="outline-btn"><i class="bi bi-envelope"></i> Reminders</button><button class="nice-action"><i class="bi bi-plus"></i> New Invoice</button></div>
        </section>

        <section class="invoice-metrics">
            <article><i class="bi bi-receipt"></i><span>Total Invoices</span><strong>248</strong></article>
            <article><i class="bi bi-cash-stack green-box"></i><span>Collected</span><strong>$45,250</strong></article>
            <article><i class="bi bi-hourglass-split amber-box"></i><span>Pending</span><strong>$12,800</strong></article>
            <article><i class="bi bi-exclamation-triangle red-box"></i><span>Overdue</span><strong>$3,450</strong></article>
        </section>

        <div class="invoice-layout">
            <section class="panel invoice-register">
                <div class="panel-head">
                    <div><h2>Invoice Register</h2><p>Track due dates, owners, and payment status in real time.</p></div>
                    <div class="toolbar-search"><i class="bi bi-search"></i><input placeholder="Search invoice, client, amount..."><button>All status <i class="bi bi-chevron-down"></i></button></div>
                </div>
                <table class="nice-table">
                    <thead><tr><th>Invoice</th><th>Client</th><th>Owner</th><th>Due Date</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        @foreach([
                            ['#INV-2024-001', 'Acme Corporation', 'M. Johnson', 'Feb 14, 2024', '$5,103.00', 'Paid', 'AC'],
                            ['#INV-2024-002', 'Tech Industries', 'S. Patel', 'Feb 17, 2024', '$2,750.00', 'Pending', 'TI'],
                            ['#INV-2024-003', 'Global Services', 'A. Moore', 'Jan 25, 2024', '$8,420.00', 'Overdue', 'GS'],
                            ['#INV-2024-004', 'StartUp Media', 'J. Lee', 'Feb 19, 2024', '$1,200.00', 'Paid', 'SM'],
                            ['#INV-2024-005', 'Design Co.', 'K. Barnes', 'Feb 21, 2024', '$3,850.00', 'Draft', 'DC'],
                        ] as [$invoice, $client, $owner, $due, $amount, $status, $initial])
                            <tr>
                                <td><a href="{{ url('utility/invoices/view') }}">{{ $invoice }}</a></td>
                                <td><span class="client-initial">{{ $initial }}</span><strong>{{ $client }}</strong></td>
                                <td>{{ $owner }}</td>
                                <td>{{ $due }}</td>
                                <td><strong>{{ $amount }}</strong></td>
                                <td><span class="status {{ strtolower($status) }}">{{ $status }}</span></td>
                                <td class="row-actions"><i class="bi bi-eye"></i><i class="bi bi-download"></i></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="table-footer"><span>Showing 1-5 of 248 invoices</span><div class="pager"><button disabled><i class="bi bi-chevron-left"></i></button><button class="active">1</button><button>2</button><button>3</button><button><i class="bi bi-chevron-right"></i></button></div></div>
            </section>

            <aside class="side-stack">
                <section class="panel collection-health"><h2>Collection Health</h2>@foreach([['Paid', '$45,250', 68, 'green'], ['Pending', '$12,800', 21, 'amber'], ['Overdue', '$3,450', 11, 'red']] as [$label, $value, $width, $tone])<div class="health-row {{ $tone }}"><div><span>{{ $label }}</span><strong>{{ $value }}</strong></div><p><i style="width: {{ $width }}%"></i></p></div>@endforeach</section>
                <section class="panel due-list"><h2>Upcoming Due</h2>@foreach(['#INV-2024-008' => 'BlueTech · $2,240 · Due in 2 days', '#INV-2024-011' => 'Orbit Labs · $4,950 · Due in 3 days', '#INV-2024-015' => 'Helix Works · $1,860 · Due in 4 days'] as $invoice => $meta)<article><strong>{{ $invoice }}</strong><span>{{ $meta }}</span></article>@endforeach</section>
                <section class="panel quick-actions"><h2>Quick Actions</h2>@foreach(['Send reminders', 'Reconcile payments', 'Export monthly report', 'Billing settings'] as $action)<button><i class="bi bi-envelope"></i>{{ $action }}</button>@endforeach</section>
            </aside>
        </div>
    @elseif($page === 'invoice-view')
        <section class="utility-hero">
            <div>
                <span class="eyebrow">Invoice Detail</span>
                <h1>Invoice #INV-2024-001</h1>
                <p>Issued Jan 15, 2024 · Due Feb 14, 2024 · Last activity 2 days ago</p>
                <div class="invoice-summary-grid"><div><span>Total</span><strong>$5,103.00</strong></div><div><span>Amount Paid</span><strong class="text-success">$5,103.00</strong></div><div><span>Balance</span><strong>$0.00</strong></div><div><span>Status</span><strong class="status paid d-block">Paid</strong></div></div>
            </div>
            <div class="hero-actions"><button class="outline-btn"><i class="bi bi-send"></i> Send</button><button class="outline-btn"><i class="bi bi-printer"></i> Print</button><button class="nice-action"><i class="bi bi-download"></i> PDF</button></div>
        </section>

        <div class="invoice-layout">
            <section class="panel invoice-paper">
                <div class="invoice-paper-head"><div><span class="nice-logo-mark">N</span><strong>NiceAdmin</strong><small>123 Business Street, New York, NY</small></div><div><h2>INVOICE</h2><span>#INV-2024-001</span></div></div>
                <div class="invoice-addresses">
                    <article><span>From</span><strong>NiceAdmin</strong><p>123 Business Street<br>New York, NY 10001<br>billing@example.com</p></article>
                    <article><span>Bill To</span><strong>Acme Corporation</strong><p>456 Client Avenue<br>San Francisco, CA 94102<br>accounts@acme.com</p></article>
                </div>
                <table class="nice-table line-items"><thead><tr><th>Description</th><th>Qty</th><th>Unit</th><th>Amount</th></tr></thead><tbody>@foreach([['Website Design & Development','Custom responsive website with CMS integration.','1','$3,500.00','$3,500.00'],['Logo Design','Brand identity package with multiple variations.','1','$800.00','$800.00'],['SEO Optimization','On-page setup and search optimization.','1','$500.00','$500.00'],['Maintenance & Support','Monthly support package (3 months).','3','$150.00','$450.00']] as [$name,$desc,$qty,$unit,$amount])<tr><td><strong>{{ $name }}</strong><small>{{ $desc }}</small></td><td>{{ $qty }}</td><td>{{ $unit }}</td><td><strong>{{ $amount }}</strong></td></tr>@endforeach</tbody></table>
                <div class="invoice-totals">@foreach(['Subtotal' => '$5,250.00', 'Discount (10%)' => '-$525.00', 'Tax (8%)' => '$378.00', 'Total' => '$5,103.00', 'Amount Paid' => '-$5,103.00', 'Balance Due' => '$0.00'] as $label => $value)<div><span>{{ $label }}</span><strong>{{ $value }}</strong></div>@endforeach</div>
                <div class="invoice-notes"><strong>Notes</strong><p>Thank you for your business. If you have any questions about this invoice, contact our billing team.</p></div>
            </section>
            <aside class="side-stack">
                <section class="panel"><h2>Payment Details</h2>@foreach(['Method' => 'Credit Card', 'Card' => '**** **** **** 4242', 'Paid On' => 'Jan 20, 2024', 'Txn ID' => 'TXN-8472619'] as $k => $v)<div class="kv-row"><span>{{ $k }}</span><strong>{{ $v }}</strong></div>@endforeach</section>
                <section class="panel client-card"><h2>Client</h2><div><span class="client-initial">AC</span><span><strong>Acme Corporation</strong><small>accounts@acme.com</small></span></div><button class="outline-btn">View Profile</button><button class="outline-btn">All Invoices</button></section>
                <section class="panel"><h2>Activity Timeline</h2>@foreach(['Payment received' => 'Jan 20, 2024 · 2:30 PM', 'Invoice sent to client' => 'Jan 15, 2024 · 10:15 AM', 'Invoice created' => 'Jan 15, 2024 · 9:00 AM'] as $event => $time)<div class="feed-row"><span class="dot blue"></span><div><strong>{{ $event }}</strong><small>{{ $time }}</small></div></div>@endforeach</section>
            </aside>
        </div>
    @elseif($page === 'pricing')
        <section class="pricing-hero panel">
            <div>
                <span class="eyebrow">NiceAdmin Plans</span>
                <h1>Choose the plan that matches your operating model</h1>
                <p>Scale from a lightweight workspace to a fully governed enterprise environment with automation, analytics, and billing controls.</p>
                <div class="billing-toggle"><button class="active">Monthly</button><button>Yearly</button><span>Save 20%</span></div>
                <div class="utility-tags"><span><i class="bi bi-check-circle"></i> No setup fee</span><span><i class="bi bi-check-circle"></i> Cancel anytime</span><span><i class="bi bi-check-circle"></i> Secure billing portal</span></div>
            </div>
            <aside><h2>Included in every plan</h2>@foreach(['Shared workspace dashboards','Security baseline controls','Automatic cloud backups','Product support access'] as $feature)<p><i class="bi bi-check2-circle"></i> {{ $feature }}</p>@endforeach<button class="outline-btn w-100">Talk to Sales</button></aside>
        </section>

        <section class="pricing-grid">
            @foreach([
                ['Starter', 'For individuals and early teams', '$0', '/mo', ['3 workspaces', 'Basic analytics', '1 GB storage', 'Community support'], 'Start Free', false],
                ['Professional', 'For scaling product teams', '$29', '/user/mo', ['Unlimited workspaces', 'Advanced analytics', '50 GB storage', 'API + webhooks', 'Priority support'], 'Start 14-Day Trial', true],
                ['Enterprise', 'For large regulated organizations', '$99', '/user/mo', ['Everything in Professional', 'Unlimited storage', 'SSO + SCIM', 'Dedicated success manager', '24/7 phone support'], 'Contact Sales', false],
            ] as [$name, $desc, $price, $suffix, $features, $cta, $recommended])
                <article class="panel price-card {{ $recommended ? 'recommended' : '' }}">
                    @if($recommended)<em>Recommended</em>@endif
                    <h2>{{ $name }}</h2><p>{{ $desc }}</p><strong>{{ $price }}<small>{{ $suffix }}</small></strong>
                    @foreach($features as $feature)<span><i class="bi bi-check"></i>{{ $feature }}</span>@endforeach
                    <button class="{{ $recommended ? 'nice-action' : 'outline-btn' }} w-100">{{ $cta }}</button>
                    @if($recommended)<small>No credit card required</small>@endif
                </article>
            @endforeach
        </section>

        <div class="pricing-bottom">
            <section class="panel feature-matrix"><div class="panel-head"><h2>Feature Matrix</h2><span>Per-user pricing shown</span></div><table class="nice-table"><thead><tr><th>Capability</th><th>Starter</th><th>Professional</th><th>Enterprise</th></tr></thead><tbody>@foreach([['Projects','3','Unlimited','Unlimited'],['Custom Dashboard Builder','—','✓','✓'],['API Access','—','✓','✓'],['Team Seats','1','Up to 10','Unlimited'],['SLA','—','99.9%','99.99%'],['Support','Email','Priority','Dedicated']] as $row)<tr>@foreach($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>@endforeach</tbody></table></section>
            <section class="panel faq-card"><h2>Common Questions</h2>@foreach(['Can we switch plans later?' => 'Yes. Upgrades apply immediately and downgrades start at the next billing cycle.', 'Can annual plans be canceled?' => 'Yes. You can migrate to monthly billing after your current annual term.', 'Do you support invoice billing?' => 'Annual Professional and Enterprise subscriptions can pay by invoice.'] as $q => $a)<article><strong>{{ $q }}</strong><p>{{ $a }}</p></article>@endforeach<button class="outline-btn w-100">Read Full FAQ</button></section>
        </div>
    @else
        <section class="page-hero">
            <div><h1>{{ $title }}</h1><p>This utility menu is connected and ready for detailed content.</p></div>
            <a class="nice-action" href="{{ url('dashboard') }}"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
        </section>
        <section class="panel placeholder-panel"><i class="bi bi-window-sidebar"></i><h2>{{ $title }}</h2><p>Route imeunganishwa kwenye sidebar. Content halisi inaweza kuongezwa hapa.</p></section>
    @endif
@endsection
