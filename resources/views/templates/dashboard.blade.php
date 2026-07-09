@extends('templates.app')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Executive Operations Dashboard</h1>
            <p>Unified delivery, growth, and reliability signals for daily decision-making.</p>
        </div>
    </div>

    <section class="metric-grid">
        <article class="metric-card mint">
            <div class="metric-icon"><i class="bi bi-currency-dollar"></i></div>
            <span>Net Revenue</span>
            <strong>$94.2K</strong>
            <small class="up"><i class="bi bi-arrow-up-right"></i> 9.4%</small>
        </article>
        <article class="metric-card blue">
            <div class="metric-icon"><i class="bi bi-people"></i></div>
            <span>Qualified Leads</span>
            <strong>1,284</strong>
            <small class="up"><i class="bi bi-arrow-up-right"></i> 6.1%</small>
        </article>
        <article class="metric-card amber">
            <div class="metric-icon"><i class="bi bi-stopwatch"></i></div>
            <span>Avg. Cycle Time</span>
            <strong>4.2d</strong>
            <small class="down"><i class="bi bi-arrow-down-right"></i> 3.5%</small>
        </article>
        <article class="metric-card violet">
            <div class="metric-icon"><i class="bi bi-shield-check"></i></div>
            <span>Retention</span>
            <strong>92.7%</strong>
            <small class="up"><i class="bi bi-arrow-up-right"></i> 1.8%</small>
        </article>
    </section>

    <div class="dashboard-layout">
        <section class="panel performance-panel">
            <div class="panel-head">
                <h2>Performance Curve</h2>
                <div class="segmented">
                    <button class="active">Month</button>
                    <button>Week</button>
                    <button>Day</button>
                </div>
            </div>
            <div class="mini-summary">
                <div><span>Revenue</span><strong>$94.2K</strong></div>
                <div><span>Cost</span><strong>$57.6K</strong></div>
                <div><span>Margin</span><strong>$36.6K</strong></div>
            </div>
            <div class="chart-box">
                <svg viewBox="0 0 900 260" role="img" aria-label="Performance chart">
                    <g class="grid-lines">
                        <line x1="34" x2="880" y1="45" y2="45" />
                        <line x1="34" x2="880" y1="95" y2="95" />
                        <line x1="34" x2="880" y1="145" y2="145" />
                        <line x1="34" x2="880" y1="195" y2="195" />
                    </g>
                    <polyline class="line-primary" points="38,170 85,160 132,154 179,164 226,158 273,142 320,137 367,128 414,122 461,116 508,105 555,110 602,113 649,100 696,92 743,78 790,86 837,72 878,62" />
                    <polyline class="line-secondary" points="38,205 85,200 132,196 179,202 226,197 273,188 320,182 367,178 414,176 461,168 508,162 555,165 602,160 649,154 696,149 743,144 790,150 837,136 878,130" />
                    <g class="months">
                        <text x="30" y="240">Jan</text><text x="105" y="240">Feb</text><text x="182" y="240">Mar</text><text x="260" y="240">Apr</text><text x="338" y="240">May</text><text x="416" y="240">Jun</text><text x="492" y="240">Jul</text><text x="570" y="240">Aug</text><text x="648" y="240">Sep</text><text x="726" y="240">Oct</text><text x="804" y="240">Nov</text><text x="862" y="240">Dec</text>
                    </g>
                </svg>
            </div>
        </section>

        <section class="panel activity-panel">
            <div class="panel-head">
                <div><h2>Recent Activity</h2><p>Last 2 hours</p></div>
                <a href="#">View</a>
            </div>
            <ul class="activity-list">
                <li><span class="dot green"></span> Alex Thompson completed purchase workflow update.</li>
                <li><span class="dot blue"></span> Sarah Wilson submitted dashboard UX revisions.</li>
                <li><span class="dot amber"></span> Storage usage crossed 80% on media bucket.</li>
                <li><span class="dot blue"></span> Deployment v3.2.1 passed production checks.</li>
                <li><span class="dot green"></span> New lead batch synced from CRM integrations.</li>
                <li><span class="dot red"></span> Billing retry required for invoice #INV-8043.</li>
            </ul>
        </section>

        <section class="panel transactions-panel">
            <div class="panel-head">
                <h2>Latest Transactions</h2>
                <a href="#">Open ledger</a>
            </div>
            <table class="nice-table">
                <thead>
                    <tr><th>ID</th><th>Account</th><th>Date</th><th>Amount</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <tr><td>#TXN-3112</td><td><span class="tiny-avatar teal">A</span> Alex Thompson</td><td>Feb 24, 2026</td><td>$2,140.00</td><td><span class="status ok">Completed</span></td></tr>
                    <tr><td>#TXN-3111</td><td><span class="tiny-avatar tan">M</span> Mia Rodriguez</td><td>Feb 24, 2026</td><td>$890.00</td><td><span class="status wait">Pending</span></td></tr>
                    <tr><td>#TXN-3110</td><td><span class="tiny-avatar slate">M</span> Mike Johnson</td><td>Feb 23, 2026</td><td>$3,420.00</td><td><span class="status ok">Completed</span></td></tr>
                    <tr><td>#TXN-3109</td><td><span class="tiny-avatar indigo">E</span> Emily Davis</td><td>Feb 23, 2026</td><td>$540.00</td><td><span class="status fail">Failed</span></td></tr>
                </tbody>
            </table>
        </section>

        <section class="panel donut-panel">
            <h2>Acquisition Mix</h2>
            <div class="donut-wrap">
                <div class="donut"><span>Total<br><strong>100%</strong></span></div>
            </div>
            <div class="mix-list">
                <div><span>Inbound</span><strong>38%</strong></div>
                <div><span>Outbound</span><strong>24%</strong></div>
                <div><span>Partners</span><strong>21%</strong></div>
                <div><span>Community</span><strong>17%</strong></div>
            </div>
        </section>

        <section class="panel targets-panel">
            <div class="panel-head"><h2>Sales Targets</h2><a href="#">View report</a></div>
            <div class="target-row"><span>Product Revenue</span><strong>74%</strong><div><i style="width:74%"></i></div></div>
            <div class="target-row blue"><span>Service Revenue</span><strong>61%</strong><div><i style="width:61%"></i></div></div>
            <div class="target-row violet"><span>Renewals</span><strong>83%</strong><div><i style="width:83%"></i></div></div>
        </section>

        <section class="panel stability-panel">
            <h2>System Stability</h2>
            <div class="target-row"><span>API Success</span><strong>99.4%</strong><div><i style="width:99%"></i></div></div>
            <div class="target-row blue"><span>Background Jobs</span><strong>97.8%</strong><div><i style="width:98%"></i></div></div>
            <div class="target-row amber"><span>Queue Throughput</span><strong>93.1%</strong><div><i style="width:93%"></i></div></div>
        </section>
    </div>
@endsection
