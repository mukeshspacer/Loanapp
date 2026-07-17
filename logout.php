<?php
require_once 'backend/db.php';
session_destroy();
header('Location: index.php');
exit;
