<?php

namespace Tests;

use SoltaniHossein\Saman\Saman;

class SamanTests extends Base
{
    /**
     * @var Saman
     */
    private $saman;

    public function test_request_will_return_token()
    {
        $this->saman->setClient($this->requestMock('aaa123456789'));
        $response = $this->saman->request(1000, 'http://www.example.com', '123456');
        $this->assertEquals($response, 'aaa123456789');
    }

    public function test_verify_transaction()
    {
        $_POST = array(
            "State" => 'OK',
            "StateCode" => '0',
            "ResNum" => '123456789',
            "SecurePan" => '1234-1234-1234-1234',
            "MID" => '123456789',
            "RefNum" => '1234567',
            "TRACENO" => '34535234',
        );

        $this->saman->setClient($this->verifyMock());
        $response = $this->saman->verify(1000);
        $this->assertArrayHasKey('state', $response);
        $this->assertArrayHasKey('stateCode', $response);
        $this->assertArrayHasKey('invoiceNumber', $response);
        $this->assertArrayHasKey('merchantId', $response);
        $this->assertArrayHasKey('referenceNumber', $response);
    }

    public function setUp()
    {
        $this->saman = new Saman('123456789');
    }
}
