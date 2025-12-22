<?php

define('HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DATABASE', $_ENV['DB_NAME'] ?? '');
define('USERNAME', $_ENV['DB_USER'] ?? '');
define('PASSWORD', $_ENV['DB_PASSWORD'] ?? '');

define('SESSION_LIFETIME', $_ENV['SESSION_LIFETIME'] ?? 3600);