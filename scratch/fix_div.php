<?php
$file = 'app/Views/modules/view_module.php';
$content = file_get_contents($file);

// Replace the extra closing div after department_id input
$search1 = "<input type=\"hidden\" name=\"department_id[]\" value=\"1\">\r\n                                </div>";
$search2 = "<input type=\"hidden\" name=\"department_id[]\" value=\"1\">\n                                </div>";
$replace = "<input type=\"hidden\" name=\"department_id[]\" value=\"1\">";

if (strpos($content, $search1) !== false) {
    file_get_contents($file);
    file_put_contents($file, str_replace($search1, $replace, $content));
    echo "Successfully replaced CRLF\n";
} elseif (strpos($content, $search2) !== false) {
    file_put_contents($file, str_replace($search2, $replace, $content));
    echo "Successfully replaced LF\n";
} else {
    echo "Search string not found!\n";
}
