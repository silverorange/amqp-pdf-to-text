#!/bin/env php
<?php

ini_set('display_errors', '1');
error_reporting(E_ALL | E_STRICT);

@include_once 'PackageConfig.php';
if (class_exists('PackageConfig')) {
	PackageConfig::addPackage('swat');
	PackageConfig::addPackage('site');
	PackageConfig::addPackage('hot-date');
}

require_once 'Site/SiteGearmanCommandLine.php';
require_once 'Site/SiteCommandLineLogger.php';
require_once 'Gearman/PDFToText.php';

$parser = SiteGearmanCommandLine::fromXMLFile(
	__DIR__ . '/../data/pdf-to-text.xml'
);

$logger = new SiteCommandLineLogger($parser);
$worker = new GearmanWorker();
$app = new Gearman_PDFToText(
	'pdf-to-text',
	$parser,
	$logger,
	$worker
);
$app();

?>
