<?php
/**
 * Bootstrap - loaded at the top of every public page
 */
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/helpers/Security.php';
require_once __DIR__ . '/../app/helpers/Stats.php';
require_once __DIR__ . '/../app/controllers/Notes.php';

Security::startSession();
Security::setSecureHeaders();
