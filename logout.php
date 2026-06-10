<?php
require_once __DIR__ . '/includes/init.php';
logout_user();
session_start();
flash('success', 'You have logged out.');
redirect('index.php');

