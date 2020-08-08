<?php

namespace Tests;


use PHPUnit\Framework\TestCase;
use SoltaniHossein\Saman\Saman;

class Base extends TestCase
{
    public function test()
    {
        self::assertTrue(true);
    }

    public function requestMock($token)
    {
        $soapClient = $this->getMockBuilder(\SoapClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $soapClient->expects($this->any())
            ->method('__call')
            ->willReturnCallback(function ($methodName) {
                if ('RequestToken' === $methodName) {
                    return 'aaa123456789';
                }
                return null;
            });
        return $soapClient;
    }

    public function verifyMock()
    {
        $soapClient = $this->getMockBuilder(\SoapClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $soapClient->expects($this->any())
            ->method('__call')
            ->willReturnCallback(function ($methodName) {
                if ('verifyTransaction' === $methodName) {
                    return '1000';
                }
                return null;
            });
        return $soapClient;
    }


    public function wsdlMock($resultCode = 0)
    {
        $fromWsdl = $this->getMockFromWsdl(Request::WSDL_REQUEST);
        $result = new \stdClass();
        $return = new \stdClass();
        $return->result = $resultCode;
        $return->token = uniqid();
        $return->signature = 'yyyxxxxyyy';
        $result->return = $return;
        $fromWsdl->method('reservation')->will($this->returnValue($result));

        return $fromWsdl;
    }

    public function wsdlVerifyMock($resultCode = 0, $successful = false)
    {
        $fromWsdl = $this->getMockFromWsdl(Request::WSDL_VERIFY);

        $conf = new \stdClass();
        $conf->RESCODE = $resultCode;
        $conf->REPETETIVE = 'xx';
        $conf->AMOUNT = 'xx';
        $conf->DATE = 'xx';
        $conf->TIME = 'xx';
        $conf->TRN = 'xx';
        $conf->STAN = 'xx';
        $conf->successful = $successful;
        $conf->SIGNATURE = 'xx';
        $result = new \stdClass();
        $result->return = $conf;
        $fromWsdl->method('sendConfirmation')->will($this->returnValue($result));

        return $fromWsdl;
    }

    public function verifyData($result = '00')
    {
        return ['RESCODE' => $result, 'CRN' => 'xxx', 'TRN' => 'yyy'];
    }
}
