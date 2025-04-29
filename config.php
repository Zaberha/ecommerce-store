<?php
// تعيين اللغة الافتراضية
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

// تحميل ملف اللغة المختار
$lang = $_SESSION['lang'];
$lang_file = "languages/" . $lang . ".php";
$translations = include($lang_file);
?>