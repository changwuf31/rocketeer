<?php
namespace Rocketeer\Tasks;

use Rocketeer\Traits\Task;

/**
 * Deploy the website
 */
class Sync extends Deploy
{
	/**
	 * Description of the Task
	 *
	 * @var string
	 */
	protected $description = 'Atnetwork Style Sync from atn-staging to atn-production';

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

		// Validate that default stage is 'atn-staging'
		$defaultStage = $this->getConfig('stages.default', '');
		if ('atn-staging' != $defaultStage) {
			$this->command->error('Default stage is '.$defaultStage.', should be \'atn-staging\'');
			return $this->history;
		}

		// Validate that there are releases for 'atn-staging'
		$stagingRelease = $this->releasesManager->getCurrentRelease('atn-staging');
		if (!$stagingRelease) {
			$this->command->error('No release has yet been deployed for atn-staging');
			return $this->history;
		}

		// Set stage to 'atn-staging'
		$this->rocketeer->setStage('atn-staging');
		$stagingPath    = $this->releasesManager->getCurrentReleasePath();

		// Validate that we have 'atn-production' in stages
		$stages = $this->rocketeer->getStages();
		if (!in_array('atn-production', $stages)) {
			$this->command->error('Can\'t find \'atn-production\' in stages');
			return $this->history;
		}

		// Set stage to 'atn-production'
		$this->rocketeer->setStage('atn-production');

		// Generate new productionRelease
		$productionRelease = date('YmdHis');
		$this->releasesManager->updateCurrentRelease($productionRelease);
		$productionPath = $this->releasesManager->getCurrentReleasePath();

		// Copy the latest staging release to 'atn-production'
		$this->run(sprintf('cp -a %s %s', $stagingPath, $productionPath));

		// Synchronize shared folders and files
		$this->syncSharedFolders();

		// Update symlink
		$this->updateSymlink();

		$this->command->info('Successfully sync release '.$stagingRelease.' to '.$productionRelease);

		return $this->history;
	}

}
