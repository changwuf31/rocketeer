<?php
namespace Rocketeer\Tasks;

use Rocketeer\Traits\Task;

/**
 * Deploy the website
 */
class Deploy extends Task
{
	/**
	 * Description of the Task
	 *
	 * @var string
	 */
	protected $description = 'Atn Style Deployment';

	/**
	 * Run the Task
	 *
	 * @return  void
	 */
	public function execute()
	{
		// Setup if necessary
		if (!$this->isSetup()) {
			$this->command->error('Server is not ready, running Setup task');
			$this->executeTask('Setup');
		}

		// Update current release
		$release = date('YmdHis');
		$this->releasesManager->updateCurrentRelease($release);

		// Clone Git repository
		if (!$this->cloneRepository()) {
			return $this->cancel();
		}

		// Run Composer
		$runComposer = $this->getConfig('runComposer', TRUE);
		if ($runComposer && !$this->runComposer()) {
			return $this->cancel();
		}

		// Run tests
		if ($this->getOption('tests')) {
			if (!$this->runTests()) {
				$this->command->error('Tests failed');
				return $this->cancel();
			}
		}

		// Upload files not in scm
		$this->uploadNonScmFiles();

		// Set permissions
		$this->setApplicationPermissions();

		// Run migrations
		if ($this->getOption('migrate')) {
			$this->runMigrations($this->getOption('seed'));
		}

		// Synchronize shared folders and files
		$this->syncSharedFolders();

		// Update symlink
		$this->updateSymlink();

		$this->command->info('Successfully deployed release '.$release);

		return $this->history;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Cancel deploy
	 *
	 * @return false
	 */
	protected function cancel()
	{
		$this->executeTask('Rollback');

		return false;
	}

	/**
	 * Sync the requested folders and files
	 *
	 * @return void
	 */
	protected function syncSharedFolders()
	{
		$shared = (array) $this->rocketeer->getOption('remote.shared');
		foreach ($shared as $file) {
			$this->share($file);
		}
	}

	/**
	 * Set permissions for the folders used by the application
	 *
	 * @return  void
	 */
	protected function setApplicationPermissions()
	{
		$files = (array) $this->rocketeer->getOption('remote.permissions.files');
		foreach ($files as $file) {
			$this->setPermissions($file);
		}
	}

	protected function uploadNonScmFiles()
	{
		$stage     = $this->rocketeer->getStage();
		$local     = sprintf('%s/app/config/packages/anahkiasen/rocketeer/%s/%s', $this->app['path.base'], $this->server->getConfig(), $stage);
		if (file_exists($local) && is_dir($local))
		{
			$remote   = $this->releasesManager->getCurrentReleasePath();
			$allFiles = \File::allFiles($local);
			foreach ($allFiles as $fileObj)
			{
				$localFile  = $fileObj->getPathname();
				$remoteFile = $remote.DS.$fileObj->getRelativePathname();
				// printf("Put "%s" => "%s"\n", $localFile, $remoteFile);
				$this->remote->put($localFile, $remoteFile);
			}
		}

	}

	/**
	 * get values from config file
	 * @param  string $name
	 * @param  mixed $value
	 * @return mixed
	 */
	protected function getConfig($name, $value)
	{
		$retval = $this->rocketeer->getOption($name);
		if (is_null($retval)) $retval = $value;
		return $retval;
	}
}
