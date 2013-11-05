<?php
namespace Rocketeer\Commands;

use Symfony\Component\Console\Input\InputOption;

/**
 * Update app package
 */
class DeployPackageCommand extends DeployDeployCommand
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'deploy:package';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Update app package.';

	/**
	 * Execute the tasks
	 *
	 * @return array
	 */
	public function fire()
	{
		return $this->fireTasksQueue(array(
			'Rocketeer\Tasks\Package',
		));
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array_merge(BaseDeployCommand::getOptions(), array(
			array('packageName',   'N', InputOption::VALUE_REQUIRED, 'Package Name to be updated'),
		));
	}
}
