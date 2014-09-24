#!/bin/env php
<?php

ini_set('display_errors', '1');
error_reporting(E_ALL | E_STRICT);

require_once 'Site/SiteAMQPCommandLine.php';
require_once 'Site/SiteCommandLineLogger.php';
require_once 'AMQP/PDFToText.php';

$parser = SiteAMQPCommandLine::fromXMLFile(
	__DIR__ . '/../data/pdf-to-text.xml'
);

$logger = new SiteCommandLineLogger($parser);
$config = __DIR__ . '/../data/pdf-to-text.ini';
$app = new AMQP_PDFToText(
	'pdf-to-text',
	$parser,
	$logger,
	$config
);
$app();

?>
