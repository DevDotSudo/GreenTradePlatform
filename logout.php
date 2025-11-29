<?php
require_once __DIR__ . '/includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: /');
	exit();
}

logout_and_redirect('/login.php');
