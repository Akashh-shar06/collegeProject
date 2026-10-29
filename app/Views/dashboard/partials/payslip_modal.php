<!-- GLOBAL PAYROLL SLIP MODAL & PDF GENERATOR -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
/* PAYSLIP EXACT CARD STYLING */
.payslip-modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(4px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 20px;
    box-sizing: border-box;
}

.payslip-modal-overlay.active {
    display: flex !important;
}

.payslip-exact-card {
    position: relative;
    background-color: #94A398; /* Muted Sage Green */
    border-radius: 28px;
    padding: 30px;
    box-sizing: border-box;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    color: #2D3748;
    overflow: hidden;
    width: 100%;
    max-width: 740px;
    margin: 0 auto;
    box-shadow: 0 20px 40px rgba(0,0,0,0.25);
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

/* Corner Ribbons - Top Right */
.ribbon-box-tr {
    position: absolute;
    top: 0;
    right: 0;
    width: 160px;
    height: 160px;
    overflow: hidden;
    pointer-events: none;
    z-index: 2;
}

.ribbon-box-tr .ribbon-shape {
    position: absolute;
    transform: rotate(-40deg);
}

.ribbon-box-tr .ribbon-1 {
    width: 220px;
    height: 22px;
    background-color: #8A9D92;
    top: 15px;
    right: -45px;
}

.ribbon-box-tr .ribbon-2 {
    width: 220px;
    height: 32px;
    background-color: #235F64;
    top: 40px;
    right: -35px;
}

.ribbon-box-tr .ribbon-3 {
    width: 220px;
    height: 24px;
    background-color: #317A80;
    top: 76px;
    right: -25px;
}

/* Corner Ribbons - Bottom Left */
.ribbon-box-bl {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 160px;
    height: 160px;
    overflow: hidden;
    pointer-events: none;
    z-index: 2;
}

.ribbon-box-bl .ribbon-shape {
    position: absolute;
    transform: rotate(-40deg);
}

.ribbon-box-bl .ribbon-1 {
    width: 220px;
    height: 22px;
    background-color: #8A9D92;
    bottom: 15px;
    left: -45px;
}

.ribbon-box-bl .ribbon-2 {
    width: 220px;
    height: 32px;
    background-color: #235F64;
    bottom: 40px;
    left: -35px;
}

.ribbon-box-bl .ribbon-3 {
    width: 220px;
    height: 24px;
    background-color: #317A80;
    bottom: 76px;
    left: -25px;
}

/* Inner Frame */
.payslip-inner-frame {
    position: relative;
    z-index: 3;
    background-color: #FAF8F5; /* Off-white Linen */
    border: 1.5px solid #85968B;
    padding: 34px 38px 28px 38px;
    box-sizing: border-box;
}

.payslip-header-title {
    font-size: 30px;
    font-weight: 900;
    letter-spacing: 2px;
    text-align: center;
    color: #2D3748;
    margin-bottom: 2px;
    text-transform: uppercase;
    font-family: 'Trebuchet MS', 'Arial Black', sans-serif;
}

.payslip-header-company {
    font-size: 20px;
    font-weight: 800;
    letter-spacing: 1px;
    text-align: center;
    color: #2D3748;
    margin-bottom: 28px;
    text-transform: uppercase;
    font-family: 'Trebuchet MS', sans-serif;
}

/* Employee Details Grid */
.payslip-emp-grid {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 18px;
}

.payslip-section-heading {
    font-size: 14px;
    font-weight: 800;
    color: #2D3748;
    margin-bottom: 8px;
}

.payslip-info-row {
    font-size: 13px;
    line-height: 1.55;
    color: #4A5568;
}

.payslip-info-row .info-label {
    color: #4A5568;
    font-weight: 500;
}

.payslip-info-row .info-val {
    color: #4A5568;
    font-weight: 500;
}

.payslip-info-row .info-label-bold {
    font-weight: 800;
    color: #2D3748;
}

.payslip-info-row .info-val-bold {
    font-weight: 800;
    color: #2D3748;
}

.payslip-emp-right {
    text-align: right;
}

.payslip-divider {
    border-bottom: 1.5px solid #94A398;
    margin: 18px 0;
}

/* Table Breakdown */
.payslip-breakdown-heading {
    font-size: 14px;
    font-weight: 800;
    text-align: center;
    color: #2D3748;
    margin-bottom: 16px;
}

.payslip-table {
    width: 100%;
    border-collapse: collapse;
    border: 1.5px solid #94A398;
    background: transparent;
}

.payslip-table tr {
    border-bottom: 1px solid #B2C0B6;
}

.payslip-table tr:last-child {
    border-bottom: none;
}

.payslip-table td {
    padding: 7px 14px;
    font-size: 13px;
    color: #334155;
}

.payslip-table .td-desc {
    width: 65%;
    border-right: 1.5px solid #94A398;
    color: #4A5568;
}

.payslip-table .td-amount {
    width: 35%;
    text-align: right;
    color: #2D3748;
}

.payslip-table .tr-total td {
    padding: 10px 14px;
    font-weight: 800;
}

.payslip-table .td-desc-total {
    font-weight: 800;
    color: #2D3748;
    border-right: 1.5px solid #94A398;
}

.payslip-table .td-amount-total {
    font-weight: 800;
    color: #2D3748;
    text-align: right;
}

/* Signatures */
.payslip-signatures-grid {
    display: flex;
    justify-content: space-between;
    gap: 40px;
    margin-top: 10px;
}

.sig-col {
    flex: 1;
}

.sig-title {
    font-size: 13px;
    font-weight: 800;
    color: #2D3748;
    text-align: center;
    margin-bottom: 6px;
}

.sig-box {
    height: 45px;
}

.sig-line {
    border-bottom: 1px solid #94A398;
    margin-bottom: 8px;
}

.sig-date-row {
    font-size: 12px;
    color: #4A5568;
    padding-left: 2px;
}

@media print {
    body * {
        visibility: hidden !important;
    }
    #payslipCard, #payslipCard * {
        visibility: visible !important;
    }
    #payslipCard {
        position: fixed !important;
        left: 50% !important;
        top: 20px !important;
        transform: translateX(-50%) !important;
        width: 100% !important;
        max-width: 740px !important;
        margin: 0 !important;
        box-shadow: none !important;
        border-radius: 28px !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .payslip-modal-overlay {
        position: static !important;
        background: transparent !important;
        padding: 0 !important;
    }
    .no-print {
        display: none !important;
    }
    @page {
        size: A4 portrait;
        margin: 10mm;
    }
}
</style>

<div class="payslip-modal-overlay" id="payslipModal">
    <div class="modal-card" style="max-width: 820px; width: 98%; padding: 0; background: transparent; box-shadow: none; border: none;">
        
        <!-- Controls Toolbar (Hidden when printing) -->
        <div class="no-print" style="background: #1E293B; color: #FFF; padding: 14px 20px; border-radius: 14px 14px 0 0; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 22px;">🧾</span>
                <div>
                    <h4 style="margin: 0; font-size: 15px; font-weight: 700; color: #FFF;">Enterprise Payroll Slip Portal</h4>
                    <div style="font-size: 11px; color: #94A3B8;">Campus360 Enterprise ERP • Official Disbursement Record</div>
                </div>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <button type="button" class="btn btn-secondary" onclick="togglePayslipEditor()" style="font-size: 12px; padding: 6px 12px; background: #334155; color: #F8FAFC; border: 1px solid #475569; border-radius: 6px; cursor: pointer;">✏️ Adjust Breakdown</button>
                <button type="button" class="btn btn-secondary" onclick="printPayslip()" style="font-size: 12px; padding: 6px 12px; background: #0284C7; color: #FFF; border: none; font-weight: 700; border-radius: 6px; cursor: pointer;">🖨️ Print Payslip</button>
                <button type="button" class="btn btn-primary" onclick="downloadPayslipPDF()" id="downloadPdfBtn" style="font-size: 12px; padding: 6px 14px; background: #16A34A; color: #FFF; border: none; font-weight: 700; border-radius: 6px; cursor: pointer;">📥 Download PDF</button>
                <button type="button" onclick="closePayslipModal()" style="background: none; border: none; color: #94A3B8; font-size: 24px; cursor: pointer; margin-left: 6px; line-height: 1;">&times;</button>
            </div>
        </div>

        <!-- Optional Adjust / Editor Panel (Hidden when printing) -->
        <div id="payslipEditorPanel" class="no-print" style="display: none; background: #0F172A; color: #E2E8F0; padding: 16px 20px; font-size: 12px; border-bottom: 1px solid #334155;">
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 12px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 4px; color: #94A3B8;">Company / Institution Name</label>
                    <input type="text" id="edit_company_name" value="INGOUDE COMPANY" class="form-control" oninput="updatePayslipFromEditor()" style="width:100%; padding:6px 10px; font-size:12px; background:#1E293B; color:#FFF; border:1px solid #475569; border-radius:6px;">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 4px; color: #94A3B8;">Payroll Period</label>
                    <input type="text" id="edit_payroll_period" value="April 2025" class="form-control" oninput="updatePayslipFromEditor()" style="width:100%; padding:6px 10px; font-size:12px; background:#1E293B; color:#FFF; border:1px solid #475569; border-radius:6px;">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 4px; color: #94A3B8;">Issue Date</label>
                    <input type="text" id="edit_issue_date" value="May 1, 2025" class="form-control" oninput="updatePayslipFromEditor()" style="width:100%; padding:6px 10px; font-size:12px; background:#1E293B; color:#FFF; border:1px solid #475569; border-radius:6px;">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 4px; color: #94A3B8;">Currency Symbol</label>
                    <select id="edit_currency" onchange="updatePayslipFromEditor()" style="width:100%; padding:6px 10px; font-size:12px; background:#1E293B; color:#FFF; border:1px solid #475569; border-radius:6px;">
                        <option value="$">$ (USD)</option>
                        <option value="₹">₹ (INR)</option>
                        <option value="€">€ (EUR)</option>
                        <option value="£">£ (GBP)</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;">
                <div>
                    <label style="display: block; font-size: 11px; color: #94A3B8;">Basic Salary</label>
                    <input type="number" step="0.01" id="edit_basic_salary" class="form-control" oninput="recalcPayslipTotals()" style="width:100%; padding:4px 8px; font-size:12px; background:#1E293B; color:#FFF; border:1px solid #475569; border-radius:4px;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; color: #94A3B8;">Transportation Allow.</label>
                    <input type="number" step="0.01" id="edit_trans_allow" class="form-control" oninput="recalcPayslipTotals()" style="width:100%; padding:4px 8px; font-size:12px; background:#1E293B; color:#FFF; border:1px solid #475569; border-radius:4px;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; color: #94A3B8;">Meal Allowance</label>
                    <input type="number" step="0.01" id="edit_meal_allow" class="form-control" oninput="recalcPayslipTotals()" style="width:100%; padding:4px 8px; font-size:12px; background:#1E293B; color:#FFF; border:1px solid #475569; border-radius:4px;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; color: #94A3B8;">Overtime</label>
                    <input type="number" step="0.01" id="edit_overtime" class="form-control" oninput="recalcPayslipTotals()" style="width:100%; padding:4px 8px; font-size:12px; background:#1E293B; color:#FFF; border:1px solid #475569; border-radius:4px;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; color: #94A3B8;">Bonus</label>
                    <input type="number" step="0.01" id="edit_bonus" class="form-control" oninput="recalcPayslipTotals()" style="width:100%; padding:4px 8px; font-size:12px; background:#1E293B; color:#FFF; border:1px solid #475569; border-radius:4px;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; color: #94A3B8;">Deductions (-)</label>
                    <input type="number" step="0.01" id="edit_deductions" class="form-control" oninput="recalcPayslipTotals()" style="width:100%; padding:4px 8px; font-size:12px; background:#1E293B; color:#FFF; border:1px solid #475569; border-radius:4px;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; color: #94A3B8;">Tax (-)</label>
                    <input type="number" step="0.01" id="edit_tax" class="form-control" oninput="recalcPayslipTotals()" style="width:100%; padding:4px 8px; font-size:12px; background:#1E293B; color:#FFF; border:1px solid #475569; border-radius:4px;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; color: #94A3B8; font-weight:700;">Bank & Account</label>
                    <input type="text" id="edit_bank_account" class="form-control" oninput="updatePayslipFromEditor()" style="width:100%; padding:4px 8px; font-size:12px; background:#1E293B; color:#FFF; border:1px solid #475569; border-radius:4px;">
                </div>
            </div>
        </div>

        <!-- PAYROLL SLIP PRINT/PDF CARD CONTAINER -->
        <div id="payslipPrintContainer" style="background: #FFF; padding: 24px; border-radius: 0 0 14px 14px; max-height: 82vh; overflow-y: auto;">
            
            <div id="payslipCard" class="payslip-exact-card">
                <!-- Top-Right Geometric Ribbon Shapes -->
                <div class="ribbon-box-tr">
                    <div class="ribbon-shape ribbon-1"></div>
                    <div class="ribbon-shape ribbon-2"></div>
                    <div class="ribbon-shape ribbon-3"></div>
                </div>

                <!-- Bottom-Left Geometric Ribbon Shapes -->
                <div class="ribbon-box-bl">
                    <div class="ribbon-shape ribbon-1"></div>
                    <div class="ribbon-shape ribbon-2"></div>
                    <div class="ribbon-shape ribbon-3"></div>
                </div>

                <!-- Inner Payload Card Frame -->
                <div class="payslip-inner-frame">
                    <!-- Title Header -->
                    <div class="payslip-header-title">PAYROLL SLIP</div>
                    <div class="payslip-header-company" id="ps_company_name">INGOUDE COMPANY</div>

                    <!-- Employee Details Grid -->
                    <div class="payslip-emp-grid">
                        <div class="payslip-emp-left">
                            <div class="payslip-section-heading">Employee Information</div>
                            <div class="payslip-info-row">
                                <span class="info-label">Employee Name: </span>
                                <span class="info-val" id="ps_emp_name">Morgan Maxwell</span>
                            </div>
                            <div class="payslip-info-row">
                                <span class="info-label">Employee ID: </span>
                                <span class="info-val" id="ps_emp_id">1234567890</span>
                            </div>
                            <div class="payslip-info-row">
                                <span class="info-label">Position: </span>
                                <span class="info-val" id="ps_emp_position">Sales Executive</span>
                            </div>
                            <div class="payslip-info-row">
                                <span class="info-label">Department: </span>
                                <span class="info-val" id="ps_emp_dept">Sales & Marketing</span>
                            </div>
                            <div class="payslip-info-row">
                                <span class="info-label">Bank Account: </span>
                                <span class="info-val" id="ps_emp_bank">1234567890- Fauget Bank</span>
                            </div>
                        </div>

                        <div class="payslip-emp-right">
                            <div class="payslip-info-row" style="text-align: right;">
                                <span class="info-label-bold">Payroll Period: </span>
                                <span class="info-val-bold" id="ps_period">April 2025</span>
                            </div>
                            <div class="payslip-info-row" style="text-align: right;">
                                <span class="info-label-bold">Issue Date: </span>
                                <span class="info-val-bold" id="ps_issue_date">May 1, 2025</span>
                            </div>
                        </div>
                    </div>

                    <!-- Horizontal Separator -->
                    <div class="payslip-divider"></div>

                    <!-- Salary Breakdown Header & Table -->
                    <div class="payslip-breakdown-heading" id="ps_breakdown_title">Salary Breakdown ($)</div>

                    <table class="payslip-table">
                        <tbody>
                            <tr>
                                <td class="td-desc">Basic Salary</td>
                                <td class="td-amount" id="ps_basic_salary">1,500.00</td>
                            </tr>
                            <tr>
                                <td class="td-desc">Transportation Allow.</td>
                                <td class="td-amount" id="ps_trans_allow">150.00</td>
                            </tr>
                            <tr>
                                <td class="td-desc">Meal Allowance</td>
                                <td class="td-amount" id="ps_meal_allow">100.00</td>
                            </tr>
                            <tr>
                                <td class="td-desc">Overtime</td>
                                <td class="td-amount" id="ps_overtime">75.00</td>
                            </tr>
                            <tr>
                                <td class="td-desc">Bonus</td>
                                <td class="td-amount" id="ps_bonus">200.00</td>
                            </tr>
                            <tr>
                                <td class="td-desc">Deductions</td>
                                <td class="td-amount" id="ps_deductions">- 80.00</td>
                            </tr>
                            <tr>
                                <td class="td-desc">Tax</td>
                                <td class="td-amount" id="ps_tax">- 95.00</td>
                            </tr>
                            <tr class="tr-total">
                                <td class="td-desc-total">Total Paid</td>
                                <td class="td-amount-total" id="ps_total_paid">1,850.00</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Horizontal Separator -->
                    <div class="payslip-divider" style="margin-top: 28px; margin-bottom: 20px;"></div>

                    <!-- Signatures Section -->
                    <div class="payslip-signatures-grid">
                        <div class="sig-col">
                            <div class="sig-title">Received by:</div>
                            <div class="sig-box"></div>
                            <div class="sig-line"></div>
                            <div class="sig-date-row">Date:</div>
                        </div>
                        <div class="sig-col">
                            <div class="sig-title">Authorized by:</div>
                            <div class="sig-box"></div>
                            <div class="sig-line"></div>
                            <div class="sig-date-row">Date:</div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
let currentPayslipData = {
    currency: '$',
    companyName: 'INGOUDE COMPANY',
    empName: 'Morgan Maxwell',
    empId: '1234567890',
    position: 'Sales Executive',
    department: 'Sales & Marketing',
    bankAccount: '1234567890- Fauget Bank',
    period: 'April 2025',
    issueDate: 'May 1, 2025',
    basicSalary: 1500.00,
    transAllow: 150.00,
    mealAllow: 100.00,
    overtime: 75.00,
    bonus: 200.00,
    deductions: 80.00,
    tax: 95.00
};

function showPayslipModal(empId, name, designation, salaryNum, department, phone, bankAccount, period, currency) {
    const rawSal = parseFloat(String(salaryNum || '').replace(/[^0-9.]/g, ''));
    const baseSal = (!isNaN(rawSal) && rawSal > 0) ? rawSal : 1850.00;
    
    // Auto-compute breakdown components matching exact total baseSal
    let basic = Math.round(baseSal * 0.75 * 100) / 100;
    let trans = Math.round(baseSal * 0.08 * 100) / 100;
    let meal = Math.round(baseSal * 0.05 * 100) / 100;
    let overtime = Math.round(baseSal * 0.04 * 100) / 100;
    let bonus = Math.round(baseSal * 0.08 * 100) / 100;
    let deductions = Math.round(baseSal * 0.05 * 100) / 100;
    let tax = Math.round(baseSal * 0.05 * 100) / 100;

    // Adjust basic so net total equals baseSal exactly
    let sumCalculated = basic + trans + meal + overtime + bonus - deductions - tax;
    let diff = baseSal - sumCalculated;
    basic = Math.round((basic + diff) * 100) / 100;

    const today = new Date();
    const formattedIssueDate = today.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
    const formattedPeriod = period || (today.toLocaleDateString('en-US', { month: 'long', year: 'numeric' }));

    currentPayslipData = {
        currency: currency || '$',
        companyName: 'INGOUDE COMPANY',
        empName: name || 'Morgan Maxwell',
        empId: empId || '1234567890',
        position: designation || 'Sales Executive',
        department: department || 'Sales & Marketing',
        bankAccount: bankAccount || (empId ? `${empId}- Fauget Bank` : '1234567890- Fauget Bank'),
        period: formattedPeriod,
        issueDate: formattedIssueDate,
        basicSalary: basic,
        transAllow: trans,
        mealAllow: meal,
        overtime: overtime,
        bonus: bonus,
        deductions: deductions,
        tax: tax
    };

    populatePayslipUI();

    const modal = document.getElementById('payslipModal');
    if (modal) {
        modal.classList.add('active');
    }
}

function showPayslipModalFromRecord(record) {
    if (!record) return;
    const empId = record.emp_id || record.student_id || record.id || '1234567890';
    const name = ((record.first_name || '') + ' ' + (record.last_name || '')).trim() || record.full_name || record.username || 'Morgan Maxwell';
    const designation = record.designation || record.job_role || 'Sales Executive';
    const salary = record.salary || 1850;
    const department = record.department_name || record.department || 'Sales & Marketing';
    const phone = record.phone || '';
    showPayslipModal(empId, name, designation, salary, department, phone);
}

function populatePayslipUI() {
    const d = currentPayslipData;
    const curr = d.currency;

    document.getElementById('ps_company_name').innerText = d.companyName;
    document.getElementById('ps_emp_name').innerText = d.empName;
    document.getElementById('ps_emp_id').innerText = d.empId;
    document.getElementById('ps_emp_position').innerText = d.position;
    document.getElementById('ps_emp_dept').innerText = d.department;
    document.getElementById('ps_emp_bank').innerText = d.bankAccount;
    document.getElementById('ps_period').innerText = d.period;
    document.getElementById('ps_issue_date').innerText = d.issueDate;

    document.getElementById('ps_breakdown_title').innerText = `Salary Breakdown (${curr})`;

    document.getElementById('ps_basic_salary').innerText = formatCurrencyVal(d.basicSalary);
    document.getElementById('ps_trans_allow').innerText = formatCurrencyVal(d.transAllow);
    document.getElementById('ps_meal_allow').innerText = formatCurrencyVal(d.mealAllow);
    document.getElementById('ps_overtime').innerText = formatCurrencyVal(d.overtime);
    document.getElementById('ps_bonus').innerText = formatCurrencyVal(d.bonus);
    document.getElementById('ps_deductions').innerText = '- ' + formatCurrencyVal(d.deductions);
    document.getElementById('ps_tax').innerText = '- ' + formatCurrencyVal(d.tax);

    const total = d.basicSalary + d.transAllow + d.mealAllow + d.overtime + d.bonus - d.deductions - d.tax;
    document.getElementById('ps_total_paid').innerText = formatCurrencyVal(total);

    // Sync Editor fields
    document.getElementById('edit_company_name').value = d.companyName;
    document.getElementById('edit_payroll_period').value = d.period;
    document.getElementById('edit_issue_date').value = d.issueDate;
    document.getElementById('edit_currency').value = d.currency;
    document.getElementById('edit_basic_salary').value = d.basicSalary.toFixed(2);
    document.getElementById('edit_trans_allow').value = d.transAllow.toFixed(2);
    document.getElementById('edit_meal_allow').value = d.mealAllow.toFixed(2);
    document.getElementById('edit_overtime').value = d.overtime.toFixed(2);
    document.getElementById('edit_bonus').value = d.bonus.toFixed(2);
    document.getElementById('edit_deductions').value = d.deductions.toFixed(2);
    document.getElementById('edit_tax').value = d.tax.toFixed(2);
    document.getElementById('edit_bank_account').value = d.bankAccount;
}

function formatCurrencyVal(num) {
    return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function updatePayslipFromEditor() {
    currentPayslipData.companyName = document.getElementById('edit_company_name').value || 'INGOUDE COMPANY';
    currentPayslipData.period = document.getElementById('edit_payroll_period').value || 'April 2025';
    currentPayslipData.issueDate = document.getElementById('edit_issue_date').value || 'May 1, 2025';
    currentPayslipData.currency = document.getElementById('edit_currency').value || '$';
    currentPayslipData.bankAccount = document.getElementById('edit_bank_account').value || '1234567890- Fauget Bank';

    populatePayslipUI();
}

function recalcPayslipTotals() {
    currentPayslipData.basicSalary = parseFloat(document.getElementById('edit_basic_salary').value) || 0;
    currentPayslipData.transAllow = parseFloat(document.getElementById('edit_trans_allow').value) || 0;
    currentPayslipData.mealAllow = parseFloat(document.getElementById('edit_meal_allow').value) || 0;
    currentPayslipData.overtime = parseFloat(document.getElementById('edit_overtime').value) || 0;
    currentPayslipData.bonus = parseFloat(document.getElementById('edit_bonus').value) || 0;
    currentPayslipData.deductions = parseFloat(document.getElementById('edit_deductions').value) || 0;
    currentPayslipData.tax = parseFloat(document.getElementById('edit_tax').value) || 0;

    populatePayslipUI();
}

function togglePayslipEditor() {
    const panel = document.getElementById('payslipEditorPanel');
    if (panel) {
        panel.style.display = (panel.style.display === 'none') ? 'block' : 'none';
    }
}

function closePayslipModal() {
    const modal = document.getElementById('payslipModal');
    if (modal) {
        modal.classList.remove('active');
    }
}

function printPayslip() {
    window.print();
}

function downloadPayslipPDF() {
    const element = document.getElementById('payslipCard');
    const btn = document.getElementById('downloadPdfBtn');
    const origText = btn.innerText;
    btn.innerText = '⌛ Generating PDF...';
    btn.disabled = true;

    const empIdClean = (currentPayslipData.empId || '1234567890').replace(/[^a-zA-Z0-9_-]/g, '');
    const periodClean = (currentPayslipData.period || 'April_2025').replace(/[^a-zA-Z0-9_-]/g, '_');
    const fileName = `Payslip_${empIdClean}_${periodClean}.pdf`;

    if (typeof html2pdf !== 'undefined') {
        const opt = {
            margin:       [0.15, 0.15, 0.15, 0.15],
            filename:     fileName,
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2.5, useCORS: true, logging: false, scrollY: 0 },
            jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
        };
        html2pdf().set(opt).from(element).save().then(() => {
            btn.innerText = origText;
            btn.disabled = false;
        }).catch(err => {
            console.error('PDF Generation error:', err);
            btn.innerText = origText;
            btn.disabled = false;
            window.print();
        });
    } else {
        btn.innerText = origText;
        btn.disabled = false;
        window.print();
    }
}
</script>
