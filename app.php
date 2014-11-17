#!/usr/bin/env php
<?php
// application.php
require_once "vendor/autoload.php";
use tomverran\Viewgen\Command\GenerateViews;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new GenerateViews);
$application->run();