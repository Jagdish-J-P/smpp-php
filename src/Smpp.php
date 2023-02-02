<?php

namespace JagdishJP\SmppPhp;

use Exception;
use JagdishJP\SmppPhp\Protocols\SmppClient;
use JagdishJP\SmppPhp\Protocols\GsmEncoder;
use JagdishJP\SmppPhp\Protocols\SmppConstants;
use JagdishJP\SmppPhp\Protocols\SmppAddress;
use JagdishJP\SmppPhp\Protocols\SmppTag;
use JagdishJP\SmppPhp\Transports\TSocket;

class Smpp
{

	protected $transport;
	protected $smpp;
	protected $tags;
	protected $senderId;

	public function __construct($host = 'localhost', $port = 2775, $persist = FALSE, $debug = false, $debugHandler = null)
	{
		$this->transport = new TSocket($host, $port, $persist, $debugHandler); // hostname/ip (ie. localhost) and port (ie. 2775)
		$this->transport->setRecvTimeout(30000);
		$this->transport->setSendTimeout(30000);

		$this->smpp = new SmppClient($this->transport);

		// Activate debug of server interaction
		$this->smpp->debug = $debug; 		// binary hex-output
		$this->transport->setDebug($debug);	// also get TSocket debug

		// Open the connection
		$this->transport->open();

		// Optional: If you get errors during sendSMS, try this. Needed for ie. opensmpp.logica.com based servers.
		SmppClient::$sms_null_terminate_octetstrings = false;

		// Optional: If your provider supports it, you can let them do CSMS (concatenated SMS)
		SmppClient::$sms_use_msg_payload_for_csms = true;

        $this->initTags();
	}

    public function smpp()
    {
        return $this->smpp;
    }

    public function bindTransmitter($senderId = '', $username = '', $password = '')
    {
        $this->smpp->bindTransmitter($username, $password);

		$this->senderId = new SmppAddress(GsmEncoder::utf8_to_gsm0338($senderId), SmppConstants::TON_ALPHANUMERIC);
    }

    public function bindReceiver($senderId = '', $username = '', $password = '')
    {
        $this->smpp->bindReceiver($username, $password);

		$this->senderId = new SmppAddress(GsmEncoder::utf8_to_gsm0338($senderId), SmppConstants::TON_ALPHANUMERIC);
    }

    public function bindTransreceiver($senderId = '', $username = '', $password = '')
    {
        $this->smpp->bindReceiver($username, $password);

		$this->senderId = new SmppAddress(GsmEncoder::utf8_to_gsm0338($senderId), SmppConstants::TON_ALPHANUMERIC);
    }

    public function addTag($id, $value, $length = null, $type = 'a*')
	{
		$this->tags[] = new SmppTag($id, $value, $length, $type);
	}

	public function initTags()
	{
		$this->tags = null;
	}

	public function readSms()
	{
		return $this->smpp->readSMS();
	}

	public function sendSms($recipient, $message)
	{
		try {

			$to = new SmppAddress($recipient, SmppConstants::TON_INTERNATIONAL, SmppConstants::NPI_E164);

			// Prepare message
			$encodedMessage = GsmEncoder::utf8_to_gsm0338($message);

			// Send
			$response = $this->smpp->sendSMS($this->senderId, $to, $encodedMessage, $this->tags);

            //unset tags
            $this->initTags();

			// Close connection
			$this->smpp->close();

			return $response;
		} catch (Exception $e) {
			// Try to unbind
			try {
				$this->smpp->close();
			} catch (Exception $ue) {
				// if that fails just close the transport
				printDebug("Failed to unbind; '" . $ue->getMessage() . "' closing transport");
				if ($this->transport->isOpen()) $this->transport->close();
			}

			throw $e;
		}
	}

}
