<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>

<main class="dashboard-viewport">
    <!-- Top Metric Summary Cards Row (Image 1 & 3 Reference) -->
    <section class="metrics-row">
        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-title">Contact</span>
                <span class="metric-menu" data-title="Contact Enquiries">...</span>
            </div>
            <div class="metric-value"><?= number_format($metrics['enquiries'] ?? 324) ?></div>
            <div class="metric-sub">Last Update : Yesterday</div>
        </div>

        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-title">Ticket</span>
                <span class="metric-menu" data-title="Support Tickets">...</span>
            </div>
            <div class="metric-value"><?= number_format($metrics['tickets'] ?? 132) ?></div>
            <div class="metric-sub">Last Update : Yesterday</div>
        </div>

        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-title">Internal</span>
                <span class="metric-menu" data-title="Internal Notices">...</span>
            </div>
            <div class="metric-value"><?= number_format($metrics['notices'] ?? 102) ?></div>
            <div class="metric-sub">Compare of last month</div>
        </div>

        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-title">Pending Fee Dues</span>
                <span class="metric-menu" data-title="Fee Dues Summary">...</span>
            </div>
            <div class="metric-value">$<?= number_format($metrics['pending_fees'] ?? 31200) ?></div>
            <div class="metric-sub">Active Academic Term</div>
        </div>
    </section>

    <!-- Analytics Charts Row (Survey Statistic, Ticket by Category, Call Details Gauge) -->
    <section class="analytics-row">
        <!-- Left: Survey Statistic Bar Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title-group">
                    <h3>Survey Statistic</h3>
                    <p>Total of Survey</p>
                    <div class="chart-stat-big">34,402 <span style="font-size:13px; font-weight:500; color:var(--text-muted);">/ month</span></div>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:12px; border:1px solid var(--border-color); padding:4px 10px; border-radius:var(--radius-pill);">February 2026 ˅</span>
                    <span style="font-weight:bold; color:var(--text-light); cursor:pointer;">...</span>
                </div>
            </div>

            <div class="bar-chart-container">
                <div class="avg-line">
                    <span class="avg-tag">Avg</span>
                </div>
                <div class="bar-col" style="height: 40%;"></div>
                <div class="bar-col" style="height: 65%;"></div>
                <div class="bar-col" style="height: 50%;"></div>
                <div class="bar-col" style="height: 80%;"></div>
                <div class="bar-col" style="height: 45%;"></div>
                <div class="bar-col highlight" style="height: 90%;"></div>
                <div class="bar-col" style="height: 35%;"></div>
                <div class="bar-col" style="height: 70%;"></div>
                <div class="bar-col" style="height: 60%;"></div>
                <div class="bar-col" style="height: 85%;"></div>
                <div class="bar-col" style="height: 55%;"></div>
                <div class="bar-col" style="height: 75%;"></div>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:11px; color:var(--text-light); margin-top:8px;">
                <span>01-07</span>
                <span>07-15</span>
                <span>15-22</span>
                <span>22-28</span>
            </div>
        </div>

        <!-- Right: Ticket Category Progress & Donut Gauge -->
        <div style="display:flex; flex-direction:column; gap:20px;">
            <!-- Ticket by Category -->
            <div class="chart-card">
                <div class="chart-header">
                    <div>
                        <h3 style="font-size:14px; font-weight:700;">Ticket by Category</h3>
                        <span style="font-size:11px; color:var(--text-muted);">Total Interaction</span>
                        <div style="font-size:20px; font-weight:800; margin-top:2px;">430 <span style="font-size:11px; color:var(--text-muted); font-weight:500;">Client</span></div>
                    </div>
                    <span style="font-weight:bold; color:var(--text-light); cursor:pointer;">...</span>
                </div>

                <div class="segmented-progress">
                    <div class="segment-item" style="width: 40%; background-color: var(--success);"></div>
                    <div class="segment-item" style="width: 25%; background-color: var(--warning);"></div>
                    <div class="segment-item" style="width: 20%; background-color: var(--primary);"></div>
                    <div class="segment-item" style="width: 15%; background-color: var(--danger);"></div>
                </div>

                <div class="legend-list">
                    <div class="legend-row">
                        <span class="legend-label"><span class="legend-dot" style="background-color:var(--success);"></span> Payment</span>
                        <span class="legend-val">102</span>
                    </div>
                    <div class="legend-row">
                        <span class="legend-label"><span class="legend-dot" style="background-color:var(--warning);"></span> Document</span>
                        <span class="legend-val">80</span>
                    </div>
                    <div class="legend-row">
                        <span class="legend-label"><span class="legend-dot" style="background-color:var(--primary);"></span> Notification</span>
                        <span class="legend-val">70</span>
                    </div>
                    <div class="legend-row">
                        <span class="legend-label"><span class="legend-dot" style="background-color:var(--danger);"></span> False</span>
                        <span class="legend-val">50</span>
                    </div>
                </div>
            </div>

            <!-- Call Details Gauge (Semi-circular Donut Chart) -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3 style="font-size:14px; font-weight:700;">Call Details</h3>
                    <span style="font-weight:bold; color:var(--text-light); cursor:pointer;">...</span>
                </div>
                <div class="gauge-wrapper">
                    <svg class="gauge-svg" viewBox="0 0 100 50">
                        <path d="M 10 50 A 40 40 0 0 1 90 50" fill="none" stroke="#E2E8F0" stroke-width="8" stroke-linecap="round" />
                        <path d="M 10 50 A 40 40 0 0 1 40 13" fill="none" stroke="#10B981" stroke-width="8" stroke-linecap="round" />
                        <path d="M 43 11 A 40 40 0 0 1 70 16" fill="none" stroke="#8B5CF6" stroke-width="8" stroke-linecap="round" />
                        <path d="M 73 18 A 40 40 0 0 1 90 50" fill="none" stroke="#0066FF" stroke-width="8" stroke-linecap="round" />
                    </svg>
                    <div class="gauge-center-text">
                        <span>Total Chats Per Platform</span>
                        <strong>1800</strong>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Timetable Schedule Grid (Matching Zendenta Image 4 Reference) -->
    <?php $schedule = $schedule ?? []; ?>
    <section class="schedule-card">
        <div class="schedule-header">
            <div>
                <h3 style="font-size:16px; font-weight:700;">Class Timetable & Staff Reservations</h3>
                <span style="font-size:12px; color:var(--text-muted);"><?= count($schedule) ?> Scheduled Periods Today</span>
            </div>
            <div style="display:flex; gap:8px;">
                <span style="border:1px solid var(--border-color); padding:4px 12px; border-radius:var(--radius-pill); font-size:12px; font-weight:600;">Today</span>
                <span style="border:1px solid var(--border-color); padding:4px 12px; border-radius:var(--radius-pill); font-size:12px; font-weight:600; background:var(--primary-light); color:var(--primary);"><?= date('D, d M Y') ?></span>
            </div>
        </div>

        <div class="schedule-grid">
            <div class="grid-cell grid-cell-header">Time</div>
            <div class="grid-cell grid-cell-header">Dr. Soap Mactavish</div>
            <div class="grid-cell grid-cell-header">Dr. Cipeng Widjaja</div>
            <div class="grid-cell grid-cell-header">Dr. Putri Larasati</div>

            <?php foreach ($schedule as $slot): ?>
            <div class="grid-cell time-label"><?= explode('-', $slot['time_slot'])[0] ?></div>
            <div class="grid-cell">
                <?php if (isset($slot['first_name']) && strpos($slot['first_name'], 'Soap') !== false): ?>
                    <div class="event-pill event-pink">
                        <span class="status-badge status-<?= strtolower($slot['status'] ?? 'finished') ?>"><?= $slot['status'] ?? 'Finished' ?></span>
                        <strong><?= htmlspecialchars($slot['subject_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <span style="font-size:10px; opacity:0.8;"><?= $slot['time_slot'] ?> | <?= $slot['room_no'] ?></span>
                    </div>
                <?php else: ?>
                    <div class="event-pill event-pink">
                        <span class="status-badge status-finished">Finished</span>
                        <strong><?= htmlspecialchars($slot['subject_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <span style="font-size:10px; opacity:0.8;"><?= $slot['time_slot'] ?> | Room <?= $slot['room_no'] ?></span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="grid-cell">
                <?php if (isset($slot['first_name']) && strpos($slot['first_name'], 'Cipeng') !== false): ?>
                    <div class="event-pill event-green">
                        <span class="status-badge status-<?= strtolower($slot['status'] ?? 'finished') ?>"><?= $slot['status'] ?? 'Finished' ?></span>
                        <strong><?= htmlspecialchars($slot['subject_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <span style="font-size:10px; opacity:0.8;"><?= $slot['time_slot'] ?> | <?= $slot['room_no'] ?></span>
                    </div>
                <?php else: ?>
                    <span style="color:var(--text-light); font-style:italic; font-size:11px;">Scheduled</span>
                <?php endif; ?>
            </div>
            <div class="grid-cell">
                <span style="color:var(--text-light); font-style:italic; font-size:11px;">Not Available</span>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- 36-Module ERP Launcher Grid Partial -->
    <?php require BASE_PATH . '/app/Views/dashboard/partials/module_grid.php'; ?>
</main>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
