<?php
$DB = array(
    'SERVER' => getenv('DB_HOST') ?: 'database',
    'USERNAME' => getenv('DB_USER') ?: 'teamobi',
    'PASSWORD' => getenv('DB_PASSWORD') ?: 'change-me',
    'TABLE' => getenv('DB_NAME') ?: 'team2026'
);
