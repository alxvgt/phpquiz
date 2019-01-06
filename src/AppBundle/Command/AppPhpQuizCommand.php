<?php

namespace AppBundle\Command;

use AppBundle\Finder\Exception\NoQuizFoundException;
use AppBundle\Finder\PhpQuizFinder;
use AppBundle\Google\GoogleSheetsService;
use AppBundle\Hcti\HctiService;
use AppBundle\Model\PhpQuiz;
use AppBundle\Renderer\PhpQuizRenderer;
use AppBundle\Tokenizer\Tokenizer;
use AppBundle\Twitter\TwitterService;
use Doctrine\ORM\EntityManagerInterface;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\Yaml\Yaml;

class AppPhpQuizCommand extends ContainerAwareCommand
{
    const MODE_DEV = 'dev';

    /**
     * @var GoogleSheetsService
     */
    private $googleSheetsService;
    /**
     * @var TwitterService
     */
    private $twitterService;
    /**
     * @var HctiService
     */
    private $hctiService;
    /**
     * @var \Swift_Mailer
     */
    private $mailer;
    /**
     * @var PhpQuizRenderer
     */
    private $phpQuizRenderer;
    /**
     * @var bool
     */
    private $isModeDev = true;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * AppPhpQuizCommand constructor.
     *
     * @param GoogleSheetsService    $googleSheetsService
     * @param TwitterService         $twitterService
     * @param HctiService            $hctiService
     * @param \Swift_Mailer          $mailer
     * @param PhpQuizRenderer        $phpQuizRenderer
     * @param EntityManagerInterface $em
     */
    public function __construct(
        GoogleSheetsService $googleSheetsService,
        TwitterService $twitterService,
        HctiService $hctiService,
        Swift_Mailer $mailer,
        PhpQuizRenderer $phpQuizRenderer,
        EntityManagerInterface $em
    ) {
        $this->googleSheetsService = $googleSheetsService;
        $this->twitterService = $twitterService;
        $this->hctiService = $hctiService;
        $this->mailer = $mailer;
        $this->phpQuizRenderer = $phpQuizRenderer;
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:phpquiz')
            ->setDescription('Read a Google spreadsheet and tweet identified quizz')
            ->addArgument('config', InputArgument::REQUIRED, 'Command configuration file');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $this->isModeDev = ($this->getContainer()->getParameter('mode') == static::MODE_DEV);

        try {
            $io->section('Processing...');

            $io->text('Loading configuration. ..');
            $configFilePath = $input->getArgument('config');
            $config = Yaml::parseFile($configFilePath);
            $this->googleSheetsService->setSheetId($config['google_sheet']['id']);
            $this->googleSheetsService->setSheetRange($config['google_sheet']['range']);
            $this->googleSheetsService->setSheetMapping($config['google_sheet']['mapping']);
            $this->googleSheetsService->setFirstLineAsHeader(true);

            $io->text('Requesting Google Sheet...');
            $phpQuizFinder = $this->getPhpQuizFinder();
            $today = new \DateTime();

            $io->text('Finding a quizz...');
            $phpQuiz = $phpQuizFinder->findOneByReference($today->format('dm'));
            if (!$phpQuiz) {
                throw new NoQuizFoundException('No quiz found for reference '.$today->format('dm'));
            }

            $io->text('Parsing quizz...');
            $code = Tokenizer::getTokenText($phpQuiz->getQuestion(), '%code%');
            $imagePath = null;
            if ($code) {
                $imagePath = $this->getImageFromTokenCode($input, $io, $phpQuiz, $code);
            }

            $io->text('Tweeting...');

            $mediaId = null;
            if ($imagePath) {
                $mediaData = $this->twitterService->uploadMedia($imagePath);
                $mediaId = $mediaData['media_id_string'];
            }

            $status = $this->phpQuizRenderer->render($phpQuiz);
            $tweetData = $this->twitterService->postTweet($status, [$mediaId]);

            $io->success('Completed (tweet id : '.$tweetData['id_str'].' )');
        } catch (\Exception $e) {
            $io->section('An exception occured, sending mail ...');
            if (!$this->isModeDev && $this->sendExceptionMail($e)) {
                $io->error('Exception email Sent !');
            }
            if ($this->isModeDev) {
                throw $e;
            }
        }
    }

    /**
     * @return PhpQuizFinder
     *
     * @throws \Exception
     */
    private function getPhpQuizFinder()
    {
        $values = $this->googleSheetsService->getSheetValues();
        $converter = $this->googleSheetsService->getConverter();
        $phpQuizzes = $converter->getPhpQuizzes($values);

        return new PhpQuizFinder($phpQuizzes);
    }

    /**
     * @param \Exception $e
     *
     * @return int
     */
    private function sendExceptionMail(\Exception $e)
    {
        $dumper = new HtmlDumper();
        $cloner = new VarCloner();
        $content = $dumper->dump($cloner->cloneVar($e), true);
        $title = '<h1>Oops !</h1>';

        $message = new Swift_Message();
        $message->setSubject('['.$this->getName().'] '.substr($e->getMessage(), 0, 20).'...');
        $message->setTo(['phpquizz+error@gmail.com']);
        $message->setBody($title.$content, 'text/html');

        return $this->mailer->send($message);
    }

    /**
     * @param InputInterface $input
     * @param SymfonyStyle   $io
     * @param PhpQuiz        $phpQuiz
     * @param                $code
     *
     * @return bool|mixed
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \AppBundle\Hcti\Exception\HttpException
     */
    private function getImageFromTokenCode(InputInterface $input, SymfonyStyle $io, PhpQuiz $phpQuiz, $code)
    {
        $io->text('Beautifing code...');
        $originalCode = $code;
        $code = '<?php'.$code;
        $code = highlight_string($code, true);

        $io->text('Creating source code image...');
        $imagePath = false;

        if ($this->isModeDev) {
            $imagePath = $this->hctiService->retrieveImage('0acf1a8c-f69e-499d-be12-4e8bdd204a02');
        }

        if (!$imagePath) {
            $imagePath = $this->hctiService->createImage($code, null, true);
        }

        if ($imagePath) {
            $io->text('Removing source code from question...');
            $replace = '';
        } else {
            $replace = $originalCode;
        }

        $outerCode = Tokenizer::getOuterTokenText($phpQuiz->getQuestion(), '%code%');
        $phpQuiz->replaceInQuestion($outerCode, $replace);

        return $imagePath;
    }
}
