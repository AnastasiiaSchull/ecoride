<?php
$pdo = require __DIR__ . '/../../config/db.php';

require __DIR__ . '/../../app/Models/Trajet.php';
$t = new Trajet($pdo);

var_dump($t->distinctDeparts());
var_dump($t->upcomingShort());
