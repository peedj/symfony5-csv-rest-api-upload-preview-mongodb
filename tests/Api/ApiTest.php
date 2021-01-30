<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ApiTest extends WebTestCase
{

    public function testAddAndGetData()
    {
        $this->_createUploadFile();

        $originalFileName = 'test.csv';
        $csvFile = new UploadedFile(__DIR__ . '/../../public/test/test.csv', $originalFileName, 'text/csv');

        /**
         * @var $client KernelBrowser
         */
        $client = static::createClient();
        $client->request('POST', '/api/csv', [
            'headers' => [
                'Content-Type' => 'multipart/form-data',
            ],
        ], [
            'csv_file' => $csvFile
        ]);

        $response = $client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());

        $this->assertTrue($response->headers->contains(
            'Content-Type', 'application/json'
        ));

        $result = $response->getContent();
        $this->assertStringStartsWith('{"id":', $result);

        $array = \json_decode($result, true);
        $this->assertIsArray($array);

        $this->assertArrayHasKey('fileName', $array);
        $this->assertEquals($originalFileName, $array['fileName']);

        $this->assertArrayHasKey('id', $array);
        $this->_getData($client, $array['id']);

    }

    /**
     * @param KernelBrowser $client
     * @param string $id
     */
    private function _getData(KernelBrowser $client, int $id)
    {
        $this->_waitForFileToBeParsed($client, $id);

        $client->request('GET', '/api/csv-stats/' . $id . '?loadOptions=%7B%22requireTotalCount%22:true,%22searchOperation%22:%22contains%22,%22searchValue%22:null,%22skip%22:0,%22take%22:11,%22userData%22:%7B%7D,%22sort%22:[%7B%22selector%22:%22client%22,%22desc%22:false,%22isExpanded%22:true%7D,%7B%22selector%22:%22group_month%22,%22desc%22:false,%22isExpanded%22:true%7D]%7D');

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertTrue($response->headers->contains(
            'Content-Type', 'application/json'
        ));

        $result = $response->getContent();
        $this->assertIsString($result, 'Unknown result returned');

        $this->assertStringStartsWith('{"fileName":"test.csv","items":[', $result, 'Not Corresponding Response Returned');

        $array = \json_decode($result, true);

        $this->assertIsArray($array, 'Response is Array');

        $this->assertArrayHasKey('items', $array, "Has no Items In Response"); // has items
        $this->assertArrayHasKey(0, $array['items'], "Has no Item With Index=1 In Response");
        $this->assertArrayHasKey('client', $array['items'][0], "Has no Client Column");

        $this->assertEquals('A', $array['items'][0]['client'], 'Cannot find appropriate Client in DB Data');

        $this->_removeFile($client, $id);
        $this->_checkIfFileWasRemovedFromBDAndReturns404($client, $id);
    }

    private function _createUploadFile()
    {
        $contents = <<<CONTENTS
date,client,sign_smartid,sign_mobile,sign_sc,authorize_smartid,authorize_mobile,authorize_sc,ocsp,crl
2010-01-01,A,7609,6981,8100,2226,3601,3898,1314,1346
CONTENTS;

        file_put_contents(__DIR__ . '/../../public/test/test.csv', $contents);
    }

    /**
     * @param KernelBrowser $client
     * @param int $id
     * @return mixed
     */
    private function checkUploadedCsvFileStatus(KernelBrowser $client, int $id) {
        $client->request('GET', '/api/csv/' . $id);

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), 'File Is not in DB');

        $this->assertTrue($response->headers->contains(
            'Content-Type', 'application/json'
        ));

        $result = $response->getContent();
        $this->assertIsString($result, 'Unknown result returned');

        $array = \json_decode($result, true);

        return $array['status'];
    }

    /**
     * @param KernelBrowser $client
     * @param int $id
     */
    private function _removeFile(KernelBrowser $client, int $id): void
    {
        $client->request('DELETE', '/api/csv/' . $id);
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), 'Delete: Test File Is not removed from DB');
    }

    /**
     * @param KernelBrowser $client
     * @param int $id
     */
    private function _checkIfFileWasRemovedFromBDAndReturns404(KernelBrowser $client, int $id): void
    {
        $client->request('GET', '/api/csv/' . $id);
        $response = $client->getResponse();
        $this->assertEquals(404, $response->getStatusCode(), 'Request: Test File Is not removed from DB');
    }

    /**
     * @param KernelBrowser $client
     * @param int $id
     */
    private function _waitForFileToBeParsed(KernelBrowser $client, int $id): void
    {
        $sleepSeconds = 2;
        $max_tries = 20;
        $tries = 0;
        $startTime = \time();
        while (in_array($status = $this->checkUploadedCsvFileStatus($client, $id), ['new', 'parse']) && $max_tries > $tries) {
            $tries++;
            sleep($sleepSeconds);
        }

        $xSeconds = \time() - $startTime;
        $this->assertEquals('complete', $status, 'File is not parsed for ' . $xSeconds . ' Seconds');
    }

}