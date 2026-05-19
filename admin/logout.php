<?php
require_once __DIR__ . '/../includes/auth.php';
admin_logout();
redirect(base_url('admin/login.php'));
