<?php
// Config Sample - akan di-generate ulang oleh install.php menjadi config.php
// JANGAN EDIT FILE INI LANGSUNG. Edit config.php setelah install.

return [
    'db' => [
        'host'    => 'localhost',
        'port'    => 3306,
        'name'    => 'undangandigital',
        'user'    => 'root',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        // Kosongkan utk auto-detect (cocok untuk vhost & dynamic domain)
        'base_url'      => '',
        'site_name'     => 'Undangan Digital',
        'timezone'      => 'Asia/Jakarta',
        'session_name'  => 'ud_sess',
        'upload_max_mb' => 10,
    ],
    'security' => [
        // Ganti string ini setelah install
        'app_key' => 'CHANGE_ME_TO_RANDOM_STRING',
    ],
];
