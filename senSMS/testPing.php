<?php
require_once 'pingMonitor.php';

$targetIP = '192.168.0.16';
$pingMonitor = new PingMonitor($targetIP);
$pingMonitor->monitor();
