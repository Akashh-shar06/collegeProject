<?php
// Update view_module.php
$file = __DIR__ . '/../app/Views/modules/view_module.php';
$content = file_get_contents($file);
$target = '<button type="button" class="btn btn-secondary" style="padding:4px 10px; font-size:11px;" data-record="<?= htmlspecialchars(json_encode($row), ENT_QUOTES, \'UTF-8\') ?>" onclick="handleViewClick(this)"><?= $moduleKey === \'fees\' ? \'📄 Download / View Receipt\' : \'View\' ?></button>';
$replacement = '<?php if ($moduleKey === \'payroll\' || $moduleKey === \'staff_data\' || $moduleKey === \'hr_management\'): ?>
                                        <button type="button" class="btn btn-secondary" style="padding:4px 10px; font-size:11px; background:#ECFDF5; color:#047857; border-color:#A7F3D0; font-weight:700;" onclick="showPayslipModalFromRecord(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, \'UTF-8\') ?>)">📄 Payslip PDF</button>
                                    <?php endif; ?>
                                    ' . $target;

if (strpos($content, $target) !== false) {
    $content = str_replace($target, $replacement, $content);
    file_put_contents($file, $content);
    echo "SUCCESS: Updated view_module.php\n";
} else {
    echo "NOTICE: Target not found in view_module.php\n";
}

// Update staff_management.php
$staffFile = __DIR__ . '/../app/Views/modules/staff_management.php';
$staffContent = file_get_contents($staffFile);
$staffTarget = '<button type="button" class="btn btn-primary" style="font-size:11px; padding:3px 10px;" data-record="<?= htmlspecialchars(json_encode($st), ENT_QUOTES, \'UTF-8\') ?>" onclick="openStaffEditModal(this)">Edit</button>';
$staffReplacement = '<button type="button" class="btn btn-secondary" style="font-size:11px; padding:3px 8px; background:#ECFDF5; color:#047857; border-color:#A7F3D0; font-weight:700;" onclick="showPayslipModalFromRecord(<?= htmlspecialchars(json_encode($st), ENT_QUOTES, \'UTF-8\') ?>)">📄 Payslip PDF</button> ' . $staffTarget;

if (strpos($staffContent, $staffTarget) !== false) {
    $staffContent = str_replace($staffTarget, $staffReplacement, $staffContent);
    file_put_contents($staffFile, $staffContent);
    echo "SUCCESS: Updated staff_management.php\n";
} else {
    echo "NOTICE: Target not found in staff_management.php\n";
}
