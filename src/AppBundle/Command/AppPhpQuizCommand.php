<?php

namespace AppBundle\Command;

use AppBundle\Finder\PhpQuizFinder;
use AppBundle\Google\GoogleSheetsService;
use AppBundle\Twitter\TwitterService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AppPhpQuizCommand extends ContainerAwareCommand
{
    /**
     * @var GoogleSheetsService
     */
    private $googleSheetsService;
    /**
     * @var TwitterService
     */
    private $twitterService;

    /**
     * AppPhpQuizCommand constructor.
     * @param GoogleSheetsService $googleSheetsService
     * @param TwitterService $twitterService
     */
    public function __construct(GoogleSheetsService $googleSheetsService, TwitterService $twitterService)
    {
        $this->googleSheetsService = $googleSheetsService;
        $this->twitterService = $twitterService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:phpquiz')
            ->setDescription('...')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->text('Requesting Google Sheet...');
        $phpQuizFinder = $this->getPhpQuizFinder();
        $today = new \DateTime();

        $phpQuiz = $phpQuizFinder->findOneByReference($today->format('dm'));

        $io->success('Completed');
    }

    /**
     * @return PhpQuizFinder
     * @throws \Exception
     */
    private function getPhpQuizFinder()
    {
        $values = $this->googleSheetsService->getSheetValues();
        $converter = $this->googleSheetsService->getConverter();
        $phpQuizzes = $converter->getPhpQuizzes($values);
        return new PhpQuizFinder($phpQuizzes);
    }

}
