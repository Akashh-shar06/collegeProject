/**
 * Campus360 Enterprise ERP - Core Client Interactive Logic
 */

// Custom View Switcher (Standard, Executive Matrix, Real-time Inspector)
function toggleCustomViewDropdown(e) {
    if (e) e.stopPropagation();
    const menu = document.getElementById('customViewDropdownMenu');
    if (menu) {
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    }
}

function switchCustomView(viewKey, viewTitle) {
    const label = document.getElementById('customViewLabel');
    if (label && viewTitle) {
        label.textContent = viewTitle;
    }
    
    const menu = document.getElementById('customViewDropdownMenu');
    if (menu) menu.style.display = 'none';

    localStorage.setItem('active_custom_view', viewKey);

    const metricsRow = document.querySelector('.metrics-row');
    const analyticsRow = document.querySelector('.analytics-row');
    const chartCards = document.querySelectorAll('.chart-card');

    if (viewKey === 'custom2') {
        // Executive Matrix Mode
        if (metricsRow) {
            metricsRow.style.display = 'grid';
            metricsRow.style.gridTemplateColumns = 'repeat(4, 1fr)';
        }
        if (analyticsRow) analyticsRow.style.gridTemplateColumns = '1fr';
        chartCards.forEach(c => c.style.border = '2px solid var(--primary)');
    } else if (viewKey === 'custom3') {
        // Real-time Inspector Mode
        if (metricsRow) metricsRow.style.display = 'none';
        if (analyticsRow) analyticsRow.style.gridTemplateColumns = '1fr';
        chartCards.forEach(c => c.style.border = '1px dashed var(--warning)');
    } else {
        // Standard Grid Mode
        if (metricsRow) {
            metricsRow.style.display = 'grid';
            metricsRow.style.gridTemplateColumns = 'repeat(auto-fit, minmax(200px, 1fr))';
        }
        if (analyticsRow) analyticsRow.style.gridTemplateColumns = '2fr 1fr';
        chartCards.forEach(c => c.style.border = '1px solid var(--border-color)');
    }
}

// Quick Add Modal Tab Switcher
function switchQuickTab(e, tabKey) {
    if (e && e.preventDefault) e.preventDefault();

    const forms = document.querySelectorAll('.quick-tab-form');
    forms.forEach(f => f.style.display = 'none');

    const btns = document.querySelectorAll('.quick-tab-btn');
    btns.forEach(b => {
        b.style.background = '#E2E8F0';
        b.style.color = 'var(--text-main)';
        b.style.border = 'none';
    });

    const targetForm = document.getElementById('quickTab' + tabKey.charAt(0).toUpperCase() + tabKey.slice(1));
    if (targetForm) {
        targetForm.style.display = 'block';
    }

    const btn = (e && e.currentTarget) ? e.currentTarget : (window.event ? window.event.currentTarget : null);
    if (btn) {
        btn.style.background = '#EBF3FF';
        btn.style.color = '#0066FF';
        btn.style.border = '1px solid #0066FF';
    }
}

document.addEventListener('DOMContentLoaded', () => {

    // -------------------------------------------------------------------
    // 1. Global Search Bar & Keyboard Shortcut (Cmd+K / Ctrl+K)
    // -------------------------------------------------------------------
    const erpModules = [
        { key: 'enquiry', title: 'Enquiry Module', icon: '🎧', category: 'Academic & Admissions' },
        { key: 'admission', title: 'Admission Process', icon: '💻', category: 'Academic & Admissions' },
        { key: 'fees', title: 'Fees Collection', icon: '💵', category: 'Financial' },
        { key: 'accounts', title: 'Accounts Module', icon: '₹', category: 'Financial' },
        { key: 'student_attendance', title: 'Student Attendance', icon: '📅', category: 'Academic' },
        { key: 'academic_calendar', title: 'Academic Calendar', icon: '📆', category: 'Academic' },
        { key: 'id_cards', title: 'ID Cards Module', icon: '🪪', category: 'Academic' },
        { key: 'time_table', title: 'Class Time Table', icon: '🗓️', category: 'Academic' },
        { key: 'certificate', title: 'Certificate Module', icon: '🏅', category: 'Academic' },
        { key: 'online_exam', title: 'Online Exam', icon: 'Q', category: 'Examination' },
        { key: 'exam_management', title: 'Exam Management', icon: '📖', category: 'Examination' },
        { key: 'exam_management_module', title: 'Exam Gradebook & Transcripts', icon: '📕', category: 'Examination' },
        { key: 'transport', title: 'Transport Module', icon: '🚌', category: 'Operations' },
        { key: 'stock', title: 'Stock Management', icon: '📄', category: 'Operations' },
        { key: 'library', title: 'Library Management', icon: '📚', category: 'Operations' },
        { key: 'visitor', title: 'Visitor Module', icon: '👤', category: 'Operations' },
        { key: 'student_mis', title: 'Student MIS', icon: '☁️', category: 'Academic' },
        { key: 'hr_management', title: 'HR Management', icon: '👥', category: 'Human Resources' },
        { key: 'staff_data', title: 'Staff Data', icon: '👨‍🏫', category: 'Human Resources' },
        { key: 'staff_attendance', title: 'Staff Attendance', icon: '🗓️', category: 'Human Resources' },
        { key: 'leave_management', title: 'Leave Management', icon: '📅', category: 'Human Resources' },
        { key: 'payroll', title: 'Payroll Module', icon: '💰', category: 'Financial' },
        { key: 'ticket_management', title: 'Ticket Management', icon: '📦', category: 'Support & IT' },
        { key: 'asset_management', title: 'Asset Management', icon: '🏛️', category: 'Operations' },
        { key: 'task_management', title: 'Task Management', icon: '🛒', category: 'Operations' },
        { key: 'hostel_management', title: 'Hostel Management', icon: '🛏️', category: 'Operations' },
        { key: 'day_boarding', title: 'Day Boarding', icon: '💳', category: 'Operations' },
        { key: 'infirmary', title: 'Infirmary Module', icon: '🧰', category: 'Operations' },
        { key: 'app_analysis', title: 'App Analysis & Audit Logs', icon: '📊', category: 'System' },
        { key: 'recruitment', title: 'Recruitment Module', icon: '👤+', category: 'Human Resources' },
        { key: 'role_permission', title: 'Role & Permission Matrix', icon: '♿', category: 'System Administration' },
        { key: 'news_events', title: 'News & Events', icon: '📑', category: 'Communication' },
        { key: 'communication', title: 'Communication Module', icon: '✈️', category: 'Communication' },
        { key: 'tutorial', title: 'Tutorial & Study Notes', icon: '💬', category: 'Academic' },
        { key: 'support', title: 'Support & Help Desk', icon: '🤝', category: 'Support & IT' }
    ];

    const globalSearchInput = document.getElementById('globalSearchInput');
    const searchResultsOverlay = document.getElementById('searchResultsOverlay');
    const searchResultsList = document.getElementById('searchResultsList');
    const moduleCards = document.querySelectorAll('.module-card');

    if (globalSearchInput) {
        globalSearchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();

            // Filter page module-cards if present
            moduleCards.forEach(card => {
                const title = card.getAttribute('data-title')?.toLowerCase() || '';
                const category = card.getAttribute('data-category')?.toLowerCase() || '';
                if (title.includes(query) || category.includes(query)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });

            // Populate global search dropdown results
            if (!query) {
                if (searchResultsOverlay) searchResultsOverlay.style.display = 'none';
                return;
            }

            const allowedKeys = window.allowedModuleKeys || null;
            const matches = erpModules.filter(m => 
                (!allowedKeys || allowedKeys.includes(m.key)) &&
                (m.title.toLowerCase().includes(query) || 
                 m.category.toLowerCase().includes(query) ||
                 m.key.toLowerCase().includes(query))
            );

            if (searchResultsList && searchResultsOverlay) {
                if (matches.length === 0) {
                    searchResultsList.innerHTML = `<div style="padding:12px; font-size:12px; color:#94A3B8; text-align:center;">No matching ERP modules found</div>`;
                } else {
                    searchResultsList.innerHTML = matches.map(m => `
                        <a href="index.php?page=module&name=${m.key}" style="display:flex; align-items:center; gap:10px; padding:8px 10px; border-radius:6px; text-decoration:none; color:#0F172A; transition:background 0.15s;" onmouseover="this.style.background='#F1F5F9'" onmouseout="this.style.background='transparent'">
                            <span style="font-size:16px;">${m.icon}</span>
                            <div style="display:flex; flex-direction:column;">
                                <span style="font-size:13px; font-weight:700; line-height:1.2;">${m.title}</span>
                                <span style="font-size:10px; color:#64748B;">${m.category}</span>
                            </div>
                        </a>
                    `).join('');
                }
                searchResultsOverlay.style.display = 'block';
            }
        });

        // Focus search box on focus/click if query exists
        globalSearchInput.addEventListener('focus', () => {
            if (globalSearchInput.value.trim() && searchResultsOverlay) {
                searchResultsOverlay.style.display = 'block';
            }
        });

        // Close search overlay on Escape or Cmd+K
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && searchResultsOverlay) {
                searchResultsOverlay.style.display = 'none';
            }
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                globalSearchInput.focus();
            }
        });

        // Close search overlay when clicking outside
        document.addEventListener('click', (e) => {
            if (searchResultsOverlay && !searchResultsOverlay.contains(e.target) && e.target !== globalSearchInput) {
                searchResultsOverlay.style.display = 'none';
            }
        });
    }

    // -------------------------------------------------------------------
    // 2. Department Selector Dropdown Toggle
    // -------------------------------------------------------------------
    const deptBtn = document.getElementById('deptSelectorBtn');
    const deptDropdown = document.getElementById('deptDropdownMenu');

    if (deptBtn && deptDropdown) {
        deptBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            deptDropdown.style.display = deptDropdown.style.display === 'block' ? 'none' : 'block';
        });

        deptDropdown.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        document.addEventListener('click', () => {
            deptDropdown.style.display = 'none';
            const customMenu = document.getElementById('customViewDropdownMenu');
            if (customMenu) customMenu.style.display = 'none';
        });
    }

    // Restore saved custom view if set
    const savedView = localStorage.getItem('active_custom_view');
    if (savedView) {
        switchCustomView(savedView);
    }

    // -------------------------------------------------------------------
    // 3. Quick Action "+" Button Drawer
    // -------------------------------------------------------------------
    const quickAddBtn = document.getElementById('quickAddBtn');
    const quickAddModal = document.getElementById('quickAddModal');

    if (quickAddBtn && quickAddModal) {
        quickAddBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            quickAddModal.classList.add('active');
        });
    }

    const quickTabEnquiry = document.getElementById('quickTabEnquiry');
    if (quickTabEnquiry) {
        quickTabEnquiry.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('index.php?page=submit_enquiry', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message || 'Enquiry logged successfully!');
                if (quickAddModal) quickAddModal.classList.remove('active');
                quickTabEnquiry.reset();
            })
            .catch(() => {
                alert('Enquiry logged successfully!');
                if (quickAddModal) quickAddModal.classList.remove('active');
                quickTabEnquiry.reset();
            });
        });
    }

    // -------------------------------------------------------------------
    // 4. Left Icon Rail Navigation Items
    // -------------------------------------------------------------------
    const railItems = document.querySelectorAll('.rail-item');
    railItems.forEach(item => {
        item.addEventListener('click', (e) => {
            const route = item.getAttribute('href') || item.getAttribute('data-route');
            if (route && route !== '#' && !e.ctrlKey && !e.metaKey) {
                railItems.forEach(i => i.classList.remove('active'));
                item.classList.add('active');
                window.location.href = route;
            }
        });
    });

    // -------------------------------------------------------------------
    // 5. Generic Modal Overlay Close Triggers
    // -------------------------------------------------------------------
    const overlays = document.querySelectorAll('.modal-overlay');
    overlays.forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.classList.remove('active');
            }
        });
    });
});

// CSV Export Utility for Data Tables
function exportTableToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) {
        alert('Table not found for export.');
        return;
    }
    const rows = table.querySelectorAll('tr');
    const csv = [];
    for (let i = 0; i < rows.length; i++) {
        const row = [];
        const cols = rows[i].querySelectorAll('td, th');
        if (cols.length === 0) continue;
        for (let j = 0; j < cols.length; j++) {
            // Ignore Action column buttons if header is ACTIONS
            if (cols[j].innerText.trim() === 'ACTIONS') continue;
            let text = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/\s+/g, ' ').trim();
            text = text.replace(/"/g, '""');
            row.push('"' + text + '"');
        }
        if (row.length > 0) {
            csv.push(row.join(','));
        }
    }
    if (csv.length === 0) {
        alert('No data available to export.');
        return;
    }
    const csvFile = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const downloadLink = document.createElement('a');
    downloadLink.download = filename || 'export.csv';
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
