<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Site/SiteGearmanApplication.php';

/**
 * @licence  http://www.opensource.org/licenses/mit-license.html MIT
 * @copyright 2013 silverorange
 */
class Gearman_PDFToText extends SiteGearmanApplication
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
	 * @param GearmanJob $job
	 *
	 * @return string
	 */
	protected function doWork(GearmanJob $job)
	{
		$workload = json_decode($job->workload(), true);

		if ($workload === null || !isset($workload['filename'])) {
			$this->logger->error('Job was not formatted properly.' . PHP_EOL);
			$job->sendFail();
			return '';
		}

		$content = '';

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
			$content = str_replace("\xc2\xa0", ' ', $content);
			pclose($proc);
		}

		$this->logger->info('done' . PHP_EOL);

		$job->sendComplete($content);
	}

	// }}}
}

?>
