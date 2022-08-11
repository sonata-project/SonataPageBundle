<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Command;

use Sonata\PageBundle\Model\SiteManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Create a site.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
#[AsCommand(name: 'sonata:page:create-site', description: 'Create a site')]
final class CreateSiteCommand extends Command
{
    // TODO: Remove static properties when support for Symfony < 5.4 is dropped.
    protected static $defaultName = 'sonata:page:create-site';
    protected static $defaultDescription = 'Create a site';

    private SiteManagerInterface $siteManager;

    public function __construct(SiteManagerInterface $siteManager)
    {
        parent::__construct();

        $this->siteManager = $siteManager;
    }

    public function configure(): void
    {
        \assert(null !== static::$defaultDescription);

        $this
            // TODO: Remove setDescription when support for Symfony < 5.4 is dropped.
            ->setDescription(static::$defaultDescription)
            ->addOption('no-confirmation', null, InputOption::VALUE_NONE, 'Ask confirmation before generating the site')
            ->addOption('enabled', null, InputOption::VALUE_NONE, 'Site.enabled')
            ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'Site.name')
            ->addOption('relativePath', null, InputOption::VALUE_OPTIONAL, 'Site.relativePath')
            ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'Site.host')
            ->addOption('enabledFrom', null, InputOption::VALUE_OPTIONAL, 'Site.enabledFrom')
            ->addOption('enabledTo', null, InputOption::VALUE_OPTIONAL, 'Site.enabledTo')
            ->addOption('default', null, InputOption::VALUE_NONE, 'Site.default')
            ->addOption('locale', null, InputOption::VALUE_OPTIONAL, 'Site.locale')
            ->setHelp(
                <<<'EOT'
The <info>sonata:page:create-site</info> command create a new site entity.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        $values = [
            'name' => null,
            'host' => null,
            'relativePath' => null,
            'enabledFrom' => null,
            'enabledTo' => null,
            'locale' => null,
        ];

        foreach ($values as $name => $value) {
            $values[$name] = $input->getOption($name);

            while (null === $values[$name]) {
                $question = new Question(sprintf('Please define a value for <info>Site.%s</info> : ', $name));
                $values[$name] = $helper->ask($input, $output, $question);
            }
        }

        $site = $this->siteManager->create();

        $site->setName($values['name']);
        $site->setRelativePath('/' === $values['relativePath'] ? '' : $values['relativePath']);
        $site->setHost($values['host']);

        $site->setEnabledFrom('-' === $values['enabledFrom'] ? null : new \DateTime($values['enabledFrom']));
        $site->setEnabledTo('-' === $values['enabledTo'] ? null : new \DateTime($values['enabledTo']));
        $site->setIsDefault($input->getOption('default'));
        $site->setLocale('-' === $values['locale'] ? null : $values['locale']);
        $site->setEnabled($input->getOption('enabled'));

        $enabledFrom = $site->getEnabledFrom();
        $enabledTo = $site->getEnabledTo();

        $infoEnabledFrom = null !== $enabledFrom ? $enabledFrom->format('r') : 'ALWAYS';
        $infoEnabledTo = null !== $enabledTo ? $enabledTo->format('r') : 'ALWAYS';

        $output->writeln(
            <<<INFO

Creating website with the following information :
  <info>name</info> : {$site->getName()}
  <info>site</info> : http(s)://{$site->getHost()}{$site->getRelativePath()}
  <info>enabled</info> :  <info>from</info> {$infoEnabledFrom} => <info>to</info> {$infoEnabledTo}

INFO
        );

        $confirmation = true;

        if (!$input->getOption('no-confirmation')) {
            $question = new ConfirmationQuestion('Confirm site creation (Y/N)', false, '/^(y)/i');
            $confirmation = $helper->ask($input, $output, $question);
        }

        if (true === $confirmation) {
            $this->siteManager->save($site);

            $output->writeln([
                '',
                '<info>Site created!</info>',
                '',
                'You can now create the related pages and snapshots by running the followings commands:',
                sprintf('  bin/console sonata:page:update-core-routes --site=%s', $site->getId() ?? ''),
                sprintf('  bin/console sonata:page:create-snapshots --site=%s', $site->getId() ?? ''),
            ]);
        } else {
            $output->writeln('<error>Site creation cancelled!</error>');
        }

        return 0;
    }
}
