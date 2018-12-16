<?php

namespace AppBundle\Command;

use Abraham\TwitterOAuth\TwitterOAuth;
use Google_Client;
use Google_Service_Sheets;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AppPhpQuizCommand extends ContainerAwareCommand
{
    const SHEET_ID = '1zCAvEsKFbenhZxzJs9deLls9QrxVvrHfYM52ssprVh0';
    const SHEET_RANGE = 'questions!A1:J366';

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
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ss = new SymfonyStyle($input, $output);

        $sheets = $this->getSheetsService();
        $ss->text('Requesting Google Sheet...');
        $response = $sheets->spreadsheets_values->get(static::SHEET_ID, static::SHEET_RANGE);
        $rows = $response->getValues();
        dump(count($rows));

        $ss->text('Tweeting...');
        $twitter = $this->getTwitterService();
        $content = $twitter->post("statuses/update", ['status' => 'ceci est un test !!']);
        dump($content);

        $ss->success('Completed');
    }

    /**
     * @return Google_Client
     */
    private function getClient()
    {
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $this->getContainer()->getParameter('google_credentials_path'));
        $client = new Google_Client();
        $client->addScope(Google_Service_Sheets::SPREADSHEETS_READONLY);
        $client->useApplicationDefaultCredentials();

        return $client;
    }

    /**
     * @return Google_Service_Sheets
     */
    private function getSheetsService()
    {
        return $service = new Google_Service_Sheets($this->getClient());
    }

    /**
     * @return TwitterOAuth
     */
    private function getTwitterService()
    {
        return new TwitterOAuth(
            $this->getContainer()->getParameter('twitter_consumer_key'),
            $this->getContainer()->getParameter('twitter_consumer_key_secret'),
            $this->getContainer()->getParameter('twitter_consumer_access_token'),
            $this->getContainer()->getParameter('twitter_consumer_access_token_secret'),
        );
    }

}
