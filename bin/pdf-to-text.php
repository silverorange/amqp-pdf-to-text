#!/bin/env php
<?php

ini_set('display_errors', '1');
error_reporting(E_ALL | E_STRICT);

require_once 'Site/SiteAMQPCommandLine.php';
require_once 'Site/SiteCommandLineLogger.php';
require_once 'AMQP/PDFToText.php';

$dir = '@data-dir@/@package-name@/data';
if ($dir[0] == '@') {
	$dir = __DIR__ . '/../data';
}

$parser = SiteAMQPCommandLine::fromXMLFile(
	$dir . '/pdf-to-text.xml'
);

$logger = new SiteCommandLineLogger($parser);
$app = new AMQP_PDFToText(
	'pdf-to-text',
	$parser,
	$logger,
	$dir . '/pdf-to-text.ini'
);
$app();

?>
