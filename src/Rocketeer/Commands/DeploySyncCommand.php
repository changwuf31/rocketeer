<?php
namespace Rocketeer\Commands;

use Symfony\Component\Console\Input\InputOption;

/**
 * Deploy the website
 */
class DeploySyncCommand extends BaseDeployCommand
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'deploy:sync';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sync from atn-staging to atn-production.';

	/**
	 * Execute the tasks
	 *
	 * @return array
	 */
	public function fire()
	{
		return $this->fireTasksQueue(array(
			'Rocketeer\Tasks\Sync',
		));
	}

}
