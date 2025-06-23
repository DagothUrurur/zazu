<?php
require_once __DIR__.'/../php/db.php';
require_once __DIR__.'/../php/contest.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$contestManager = new Contest($conn);
$contestManager->updateContestStatus();
$conn->close();
