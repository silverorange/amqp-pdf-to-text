<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Site/SiteAMQPApplication.php';

/**
 * Worker that converts a PDF file to plain text
 *
 * The worker accepts a JSON-encoded job that contains a single field:
 * ```
 * {
 *   "filename" : "/path/to/pdf.pdf"
 * }
 * ```
 *
 * The worker returns plain-text data on success.
 *
 * @package   AMQP_PDFToText
 * @license   http://www.opensource.org/licenses/mit-license.html MIT
 * @copyright 2013-2014 silverorange
 */
class AMQP_PDFToText extends SiteAMQPApplication
{
	// {{{ protected properties

	/**
	 * @var string
	 */
	protected $bin = '';

	// }}}
	// {{{ protected function init()

	protected function init()
	{
		parent::init();
		$this->bin = trim(`which pdftotext`);
	}

	// }}}
	// {{{ protected function doWork()

	/**
	 * Expects JSON in the form:
	 * {
	 *   "filename": "/absolute/path/to/file"
	 * }
	 *
	 * @param SiteAMQPJob $job
	 *
	 * @return void
	 */
	protected function doWork(SiteAMQPJob $job)
	{
		$workload = json_decode($job->getBody(), true);

		if ($workload === null || !isset($workload['filename'])) {
			$this->logger->error('Job was not formatted properly.' . PHP_EOL);
			$job->sendFail();
			return;
		}

		$content = '';

		if (!file_exists($workload['filename'])) {
			$this->logger->error('PDF file was not found.' . PHP_EOL);
			$job->sendFail();
			return;
		}

		if (!is_file($workload['filename']) ||
			!is_readable($workload['filename'])) {
			$this->logger->error('PDF file could not be opened.' . PHP_EOL);
			$job->sendFail();
			return;
		}

		$this->logger->info(
			'Converting PDF "{filename}" ... ',
			array(
				'filename' => $workload['filename']
			)
		);

		$command = sprintf(
			'%s -q -enc \'UTF-8\' -eol unix %s -',
			$this->bin,
			escapeshellarg($workload['filename'])
		);

		$proc = popen($command, 'r');
		if ($proc !== false) {
			$content = stream_get_contents($proc);

			// Replace non-breaking spaces with regular spaces. This depends
			// on the encoding set to UTF-8 above.
			$content = str_replace("\xc2\xa0", ' ', $content);

			pclose($proc);
		}

		$this->logger->info('done' . PHP_EOL);

		$job->sendSuccess($content);
	}

	// }}}
}

?>
