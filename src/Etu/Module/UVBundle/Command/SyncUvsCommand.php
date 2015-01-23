<?php

namespace Etu\Module\UVBundle\Command;

use Etu\Core\CoreBundle\Framework\Command\ProgressBar;
use Etu\Core\UserBundle\CriApi\Browser\CriBrowser;
use Etu\Module\UVBundle\CriApi\UvApi;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;

class SyncUvsCommand extends ContainerAwareCommand
{
	/**
	 * Configure the command
	 */
	protected function configure()
	{
		$this
			->setName('etu:sync:uvs')
			->setDescription('Synchronize UVs from the CRI API')
		;
	}

	/**
	 * @param \Symfony\Component\Console\Input\InputInterface $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @return void
	 * @throws \RuntimeException
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('
	Welcome to the EtuUTT UV manager

This command helps you to synchronise EtuUTT UV with officials UV.
');

		$output->writeln("\nGetting officials schedules ...");
		$output->writeln("------------------------------------------------------------");

		$tempDirectory = __DIR__.'/../Resources/temp';

		if (! file_exists($tempDirectory)) {
			mkdir($tempDirectory, 0777, true);
		}

		/** @var UvApi $uvApi */
		$uvApi = $this->getContainer()->get('etu.sync.uvs');

		$output->writeln('Requesting CRI API ('.CriBrowser::ROOT_URL.') ...');

		try {
			$pageContent = $uvApi->findPage(1);
		} catch (\Exception $e) {
			throw new \RuntimeException('API is currently disabled by CRI');
		}

		$content = $pageContent['courses'];

		$bar = new ProgressBar('%fraction% [%bar%] %percent%', '=>', ' ', 80, $pageContent['pagination']['count_pages']);
		$bar->update(1);

		for ($page = 2; true; $page++) {
			$pageContent = $uvApi->findPage($page);
			$courses = $pageContent['courses'];

			if (empty($courses)) {
				break;
			}

			/** @var $content [] */
			$content = array_merge($content, $courses);

			$bar->update($page);
		}

		var_dump(count($content));
	}
}
