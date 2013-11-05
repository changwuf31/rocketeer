<?php
namespace Rocketeer\Tasks;

use Rocketeer\Traits\Task;

/**
 * Update the remote server single package only
 */
class Package extends Deploy
{
	 /**
	 * A description of what the Task does
	 *
	 * @var string
	 */
	protected $description = 'Update the remote server single package only';

	/**
	 * Run the Task
	 *
	 * @return  void
	 */
	public function execute()
	{
		// Update repository
		$this->updateRepository();

		// Validate that composer is enabled for this application
		$runComposer = $this->getConfig('runComposer', TRUE);
		if (!$runComposer)
		{
			$this->command->error('Composer not enabled for this application');
			return $this->history;
		}

		// Validate that packageName is supplied
		$packageName = $this->getOption('packageName');
		if (!$packageName)
		{
			$this->command->error('Package name not supplied');
			return $this->history;
		}

		$this->runComposerUpdatePackage($packageName);

		// Clear cache
		$this->runForCurrentRelease('php artisan cache:clear');

		$this->command->info('Successfully updated package '.$packageName);

		return $this->history;
	}

	/**
	 * Run Composer on the folder
	 *
	 * @return string
	 */
	protected function runComposerUpdatePackage($packageName)
	{
		$this->command->comment('Updating Composer Package '.$packageName);
		$output = $this->runForCurrentRelease($this->getComposer(). ' update '.$packageName);

		return $this->checkStatus('Composer could not install dependencies', $output);
	}
}
