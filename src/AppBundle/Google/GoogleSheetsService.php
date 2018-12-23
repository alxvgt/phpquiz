<?php

namespace AppBundle\Google;


use AppBundle\Google\Converter\GoogleSheetConverter;
use Google_Client;
use Google_Service_Sheets;

class GoogleSheetsService
{
    /**
     * @var string
     */
    private $googleCredentialsPath;
    /**
     * @var string
     */
    private $sheetId;
    /**
     * @var string
     */
    private $sheetRange;
    /**
     * @var bool
     */
    private $firstLineAsHeader;
    /**
     * @var array
     */
    private $sheetMapping;

    /**
     * GoogleSheetsService constructor.
     * @param string $googleCredentialsPath
     */
    public function __construct(string $googleCredentialsPath)
    {
        $this->googleCredentialsPath = $googleCredentialsPath;
    }

    /**
     * @param string $sheetId
     */
    public function setSheetId(string $sheetId): void
    {
        $this->sheetId = $sheetId;
    }

    /**
     * @param string $sheetRange
     */
    public function setSheetRange(string $sheetRange): void
    {
        $this->sheetRange = $sheetRange;
    }

    /**
     * @param array $sheetMapping
     */
    public function setSheetMapping(array $sheetMapping): void
    {
        $this->sheetMapping = $sheetMapping;
    }

    /**
     * @return Google_Service_Sheets
     */
    private function getSheetsService()
    {
        return new Google_Service_Sheets($this->getClient());
    }

    /**
     * @return Google_Client
     */
    private function getClient()
    {
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $this->googleCredentialsPath);
        $client = new Google_Client();
        $client->addScope(Google_Service_Sheets::SPREADSHEETS_READONLY);
        $client->useApplicationDefaultCredentials();

        return $client;
    }

    /**
     * @param string|null $sheetId
     * @param string|null $sheetRange
     * @return mixed
     */
    public function getSheetValues(string $sheetId = null, string $sheetRange = null)
    {
        $response = $this->getSheetsService()->spreadsheets_values->get($sheetId ?? $this->sheetId, $sheetRange ?? $this->sheetRange);
        $values = $response->getValues();
        if ($this->firstLineAsHeader) {
            $values = $this->reindexSheetValues($values);
        }

        return $values;
    }

    /**
     * @param bool $firstLineAsHeader
     */
    public function setFirstLineAsHeader(bool $firstLineAsHeader): void
    {
        $this->firstLineAsHeader = $firstLineAsHeader;
    }

    /**
     * @param array $values
     * @return array
     */
    private function reindexSheetValues(array $values)
    {
        $headers = array_values(reset($values));
        unset($values[0]);

        foreach ($values as $k => &$row) {
            $row = array_combine($headers, array_values($row));
        }

        return $values;
    }

    /**
     * @return GoogleSheetConverter
     */
    public function getConverter()
    {
        return new GoogleSheetConverter($this->sheetMapping);
    }

}