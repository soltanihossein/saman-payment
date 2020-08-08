<?php namespace SoltaniHossein\Saman;

use SoltaniHossein\Saman\Exception\SamanException;
use SoapClient;
use SoapFault;

class Saman
{
    /**
     * @var string REQUEST_URL Url for initializing payment request
     */
    const REQUEST_URL = 'https://sep.shaparak.ir/payments/initpayment.asmx?wsdl';

    /**
     * @var string GATE_URL Url for payment gateway
     */
    const GATE_URL = 'https://sep.shaparak.ir/payment.aspx';

    /**
     * @var string VERIFY_URL Url for confirming transaction
     */
    const VERIFY_URL = 'https://verify.sep.ir/payments/referencepayment.asmx?wsdl';


    /**
     * @var string $merchantId
     */
    public $merchantId;

    /**
     * Payment options
     *
     * @var array
     */
    public $payParams = [];

    /**
     * @var \SoapClient
     */
    private $client;

    /**
     * @param string $pin
     */
    public function __construct(string $merchantId)
    {
        $this->merchantId = $merchantId;
    }

    /**
     * Request token for generate payment gateway url
     *
     * @param int $amount in rial
     * @param string $callbackUrl Redirect url after payment
     * @param int $orderId = null
     * @param string $additionalData = null
     * @return array
     * @throws SamanException
     * @throws \SoapFault
     * @throws \Exception
     */
    public function request(int $amount, string $callbackUrl, int $invoiceId = null)
    {
        $invoiceId = $invoiceId ? $invoiceId : $this->uniqueNumber();

        try {
            $client = $this->client ?? new SoapClient(self::REQUEST_URL);
            $token = $client->RequestToken($this->merchantId, $invoiceId, $this->getAmount($amount));
        } catch (SoapFault $e) {
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }

        if (is_numeric($token)) {
            throw new SamanException($token);
        } else {
            $this->payParams['Token'] = $token;
            $this->payParams['RedirectURL'] = $callbackUrl;
        }

        return $token;
    }

    /**
     * Redirect to payment gateway
     */
    public function redirect()
    {
        $jsCode = <<<'HTML'
<!DOCTYPE html><html lang="fa"><body>
                <script>
                var form = document.createElement("form");
                form.setAttribute("method", "POST");
                form.setAttribute("action", "%s");
                form.setAttribute("target", "_self");
HTML;
        $i = 0;
        foreach ($this->payParams as $key => $value) {
            $jsCode .= sprintf(
                'var hiddenField = document.createElement("input");
                hiddenField.setAttribute("type", "hidden");
                hiddenField.setAttribute("name", "%s");
                hiddenField.setAttribute("value", "%s");
                form.appendChild(hiddenField);',
                $key,
                $value
            );
            $i++;
        }

        $jsCode .= 'document.body.appendChild(form);form.submit();</script></body></html>';

        return sprintf($jsCode, self::GATE_URL);
    }

    /**
     * Verify transaction
     *
     * @return array
     * @throws SamanException
     * @throws \SoapFault
     */
    public function verify($amount)
    {
        $amount = $this->getAmount($amount);

        $state = $_POST["State"] ?? null;
        $stateCode = $_POST["StateCode"] ?? null;
        $invoiceNumber = $_POST["ResNum"] ?? null;
        $merchantId = $_POST["MID"] ?? null;
        $referenceNumber = $_POST["RefNum"] ?? null;
        $traceNumber = $_POST["TRACENO"] ?? null;
        $cardNum = $_POST["SecurePan"] ?? null;

        if ($state !== 'OK' || $stateCode !== '0') {
            switch ($stateCode) {
                case '-1':
                    $e = new SamanException(-101);
                    break;
                case '51':
                    $e = new SamanException(51);
                    break;
                default:
                    $e = new SamanException(-100);
                    break;
            }
            throw $e;
        }

        if ($merchantId !== $this->merchantId) {
            $e = new SamanException(-4);
            throw $e;
        }

        try {
            $client = $this->client ?? new SoapClient(self::VERIFY_URL);
            $bankAmount = $client->verifyTransaction($referenceNumber, $this->merchantId);
        } catch (SoapFault $e) {
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }

        if ($bankAmount <= 0) {
            throw SamanException::invalidAmount();
        }

        if ($bankAmount != $amount) {
            throw new SamanException(-102);
        }

        return array(
            'state' => $state,
            'stateCode' => $stateCode,
            'invoiceNumber' => $invoiceNumber,
            'merchantId' => $merchantId,
            'referenceNumber' => $referenceNumber,
            'traceNumber' => $traceNumber,
            'cardNum' => $cardNum,
        );
    }

    /**
     * Set client for testing
     *
     * @param \SoapClient $client
     *
     * @return Saman
     */
    public function setClient(\SoapClient $client)
    {
        $this->client = $client;

        return $this;
    }

    public function uniqueNumber()
    {
        return hexdec(uniqid());
    }

    private function getAmount($amount)
    {
        $amount = intval($amount);
        if ($amount <= 0) {
            throw SamanException::invalidAmount();
        }

        if ($amount < 100) {
            throw SamanException::invalidAmount();
        }

        return $amount;
    }

}
