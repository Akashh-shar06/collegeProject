<?php
$vmFile = __DIR__ . '/../app/Views/modules/view_module.php';
$vmContent = file_get_contents($vmFile);

$target1 = '<option value="Teaching Staff / Teacher">Teaching Staff / Teacher</option>';
$replace1 = '<option value="HOD / Head of Department">HOD / Head of Department</option>' . "\n" . '                                             ' . $target1;

if (strpos($vmContent, $target1) !== false) {
    $vmContent = str_replace($target1, $replace1, $vmContent);
    file_put_contents($vmFile, $vmContent);
    echo "SUCCESS: Added HOD option to view_module.php\n";
} else {
    echo "NOTICE: Target not found in view_module.php\n";
}

$smFile = __DIR__ . '/../app/Views/modules/staff_management.php';
$smContent = file_get_contents($smFile);

$target2 = '<option value="Teaching Staff / Teacher">Teaching Staff / Teacher</option>';
$replace2 = '<option value="HOD / Head of Department">HOD / Head of Department</option>' . "\n" . '                            ' . $target2;

if (strpos($smContent, $target2) !== false) {
    $smContent = str_replace($target2, $replace2, $smContent);
    file_put_contents($smFile, $smContent);
    echo "SUCCESS: Added HOD option to staff_management.php\n";
} else {
    echo "NOTICE: Target not found in staff_management.php\n";
}
