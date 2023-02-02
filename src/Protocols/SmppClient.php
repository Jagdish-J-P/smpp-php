<?php
namespace JagdishJP\SmppPhp\Protocols;

use Exception;
use InvalidArgumentException;
use JagdishJP\SmppPhp\Transports\TTransport;
use RuntimeException;

/**
 * Class for receiving or sending sms through SMPP protocol.
 * This is a reduced implementation of the SMPP protocol, and as such not all features will or ought to be available.
 * The purpose is to create a lightweight and simplified SMPP client.
 *
 * @author hd@onlinecity.dk, paladin
 * @see http://en.wikipedia.org/wiki/Short_message_peer-to-peer_protocol - SMPP 3.4 protocol specification
 * Derived from work done by paladin, see: http://sourceforge.net/projects/phpsmppapi/
 *
 * Copyright (C) 2011 OnlineCity
 * Copyright (C) 2006 Paladin
 *
 * This library is free software; you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Lesser General Public License for more details.
 *
 * This license can be read at: http://www.opensource.org/licenses/lgpl-2.1.php
 */
class SmppClient
{
	// SMPP bind parameters
	public static $system_type="WWW";
	public static $interface_version=0x34;
	public static $addr_ton=0;
	public static $addr_npi=0;
	public static $address_range="";

	// ESME transmitter parameters
	public static $sms_service_type="";
	public static $sms_esm_class=0x00;
	public static $sms_protocol_id=0x00;
	public static $sms_priority_flag=0x00;
	public static $sms_registered_delivery_flag=0x00;
	public static $sms_replace_if_present_flag=0x00;
	public static $sms_sm_default_msg_id=0x00;

	/**
	 * SMPP v3.4 says octect string are "not necessarily NULL terminated".
	 * Switch to toggle this feature
	 * @var boolean
	 */
	public static $sms_null_terminate_octetstrings=true;

	/**
	 * Use the optional param, message_payload to send concatenated SMSes?
	 * @var boolean
	 */
	public static $sms_use_msg_payload_for_csms=false;

	public $debug;

	protected $pdu_queue;

	protected $transport;
	protected $debugHandler;

	// Used for reconnect
	protected $mode;
	private $login;
	private $pass;

	protected $sequence_number;
	protected $sar_msg_ref_num;

	/**
	 * Construct the SMPP class
	 *
	 * @param TTransport $transport
	 * @param string $debugHandler
	 */
	public function __construct(TTransport $transport,$debugHandler=null)
	{
		// Internal parameters
		$this->sequence_number=1;
		$this->debug=false;
		$this->pdu_queue=array();

		$this->transport = $transport;
		$this->debugHandler = $debugHandler ? $debugHandler : 'error_log';
		$this->mode = null;
	}

	/**
	 * Binds the receiver. One object can be bound only as receiver or only as trancmitter.
	 * @param string $login - ESME system_id
	 * @param string $pass - ESME password
	 * @throws SmppException
	 */
	public function bindReceiver($login, $pass)
	{
		if (!$this->transport->isOpen()) return false;
		if($this->debug) call_user_func($this->debugHandler, 'Binding receiver...');

		$response = $this->_bind($login, $pass, SmppConstants::BIND_RECEIVER);

		if($this->debug) call_user_func($this->debugHandler, "Binding status  : ".$response->status);
		$this->mode = 'receiver';
		$this->login = $login;
		$this->pass = $pass;
	}

	/**
	 * Binds the transmitter. One object can be bound only as receiver or only as trancmitter.
	 * @param string $login - ESME system_id
	 * @param string $pass - ESME password
	 * @throws SmppException
	 */
	public function bindTransreceiver($login, $pass)
	{
		if (!$this->transport->isOpen()) return false;
		if($this->debug) call_user_func($this->debugHandler, 'Binding transreceiver...');

		$response = $this->_bind($login, $pass, SmppConstants::BIND_TRANSCEIVER);

		if($this->debug) call_user_func($this->debugHandler, "Binding status  : ".$response->status);
		$this->mode = 'transreceiver';
		$this->login = $login;
		$this->pass = $pass;
	}

	/**
	 * Binds the transreceiver. One object can be bound only as receiver or only as trancmitter.
	 * @param string $login - ESME system_id
	 * @param string $pass - ESME password
	 * @throws SmppException
	 */
	public function bindTransmitter($login, $pass)
	{
		if (!$this->transport->isOpen()) return false;
		if($this->debug) call_user_func($this->debugHandler, 'Binding transmitter...');

		$response = $this->_bind($login, $pass, SmppConstants::BIND_TRANSMITTER);

		if($this->debug) call_user_func($this->debugHandler, "Binding status  : ".$response->status);
		$this->mode = 'transmitter';
		$this->login = $login;
		$this->pass = $pass;
	}

	/**
	 * Closes the session on the SMSC server.
	 */
	public function close()
	{
		if (!$this->transport->isOpen()) return;
		if($this->debug) call_user_func($this->debugHandler, 'Unbinding...');

		$response=$this->sendCommand(SmppConstants::UNBIND,"");

		if($this->debug) call_user_func($this->debugHandler, "Unbind status   : ".$response->status);
		$this->transport->close();
	}

	/**
	 * Read one SMS from SMSC. Can be executed only after bindReceiver() call.
	 * This method bloks. Method returns on socket timeout or enquire_link signal from SMSC.
	 * @return sms associative array or false when reading failed or no more sms.
	 */
	public function readSMS()
	{
		$command_id=SmppConstants::DELIVER_SM;
		// Check the queue
		$ql = count($this->pdu_queue);
		for($i=0;$i<$ql;$i++) {
			$pdu=$this->pdu_queue[$i];
			if($pdu->id==$command_id) {
				//remove response
				array_splice($this->pdu_queue, $i, 1);
				return $this->parseSMS($pdu);
			}
		}
		// Read pdu
		do{
			$pdu = $this->readPDU();
			if ($pdu === false) return false; // Just in case
			//check for enquire link command
			if($pdu->id==SmppConstants::ENQUIRE_LINK) {
				$response = new SmppPdu(SmppConstants::ENQUIRE_LINK_RESP, SmppConstants::ESME_ROK, $pdu->sequence, "\x00");
				$this->sendPDU($response);
			} else if ($pdu->id!=$command_id) { // if this is not the correct PDU add to queue
				array_push($this->pdu_queue, $pdu);
			}
		} while($pdu && $pdu->id!=$command_id);

		if($pdu) return $this->parseSMS($pdu);
		return false;
	}

	/**
	 * Send one SMS to SMSC. Can be executed only after bindTransmitter() call.
	 * $message is always in octets regardless of the data encoding.
	 * For correct handling of Concatenated SMS, message must be encoded with GSM 03.38 (data_coding 0x00) or UCS-2BE (0x08).
	 * Concatenated SMS'es uses 16-bit reference numbers, which gives 152 GSM 03.38 chars or 66 UCS-2BE chars per CSMS.
	 *
	 * @param SmppAddress $from
	 * @param SmppAddress $to
	 * @param string $message
	 * @param array $tags (optional)
	 * @param integer $dataCoding (optional)
	 * @param integer $priority (optional)
	 * @param string $scheduleDeliveryTime (optional)
	 * @param string $validityPeriod (optional)
	 * @return string message id
	 */
	public function sendSMS(SmppAddress $from, SmppAddress $to, $message, $tags=null, $dataCoding=SmppConstants::DATA_CODING_DEFAULT, $priority=0x00, $scheduleDeliveryTime=null, $validityPeriod=null)
	{
		$msg_length = strlen($message);

		if ($msg_length>160 && $dataCoding != SmppConstants::DATA_CODING_UCS2 && $dataCoding != SmppConstants::DATA_CODING_DEFAULT) return false;

		switch ($dataCoding) {
			case SmppConstants::DATA_CODING_UCS2:
				$singleSmsOctetLimit = 140; // in octets, 70 UCS-2 chars
				$csmsSplit = 132; // There are 133 octets available, but this would split the UCS the middle so use 132 instead
				break;
			case SmppConstants::DATA_CODING_DEFAULT:
				$singleSmsOctetLimit = 160; // we send data in octets, but GSM 03.38 will be packed in septets (7-bit) by SMSC.
				$csmsSplit = 152; // send 152 chars in each SMS since, we will use 16-bit CSMS ids (SMSC will format data)
				break;
			default:
				$singleSmsOctetLimit = 254; // From SMPP standard
				break;
		}

		// Figure out if we need to do CSMS, since it will affect our PDU
		if ($msg_length > $singleSmsOctetLimit) {
			$doCsms = true;
			if (!self::$sms_use_msg_payload_for_csms) {
				$parts = $this->splitMessageString($message, $csmsSplit, $dataCoding);
				$short_message = reset($parts);
				$csmsReference = $this->getCsmsReference();
			}
		} else {
			$short_message = $message;
			$doCsms = false;
		}

		// Deal with CSMS
		if ($doCsms) {
			if (self::$sms_use_msg_payload_for_csms) {
				$payload = new SmppTag(SmppTag::MESSAGE_PAYLOAD, $message, $msg_length);
				return $this->submit_sm($from, $to, null, (empty($tags) ? array($payload) : array_merge($tags,$payload)), $dataCoding, $priority, $scheduleDeliveryTime, $validityPeriod);
			} else {
				$sar_msg_ref_num = new SmppTag(SmppTag::SAR_MSG_REF_NUM, $csmsReference, 2, 'n');
				$sar_total_segments = new SmppTag(SmppTag::SAR_TOTAL_SEGMENTS, count($parts), 1, 'c');
				$seqnum = 1;
				foreach ($parts as $part) {
					$sartags = array($sar_msg_ref_num, $sar_total_segments, new SmppTag(SmppTag::SAR_SEGMENT_SEQNUM, $seqnum, 1, 'c'));
					$res = $this->submit_sm($from, $to, $part, (empty($tags) ? $sartags : array_merge($tags,$sartags)), $dataCoding, $priority, $scheduleDeliveryTime, $validityPeriod);
					$seqnum++;
				}
				return $res;
			}
		}

		return $this->submit_sm($from, $to, $short_message, $tags, $dataCoding);
	}

	/**
	 * Perform the actual submit_sm call to send SMS.
	 * Implemented as a protected method to allow automatic sms concatenation.
	 * Tags must be an array of already packed and encoded TLV-params.
	 *
	 * @param SmppAddress $source
	 * @param SmppAddress $destination
	 * @param string $short_message
	 * @param array $tags
	 * @param integer $dataCoding
	 * @param integer $priority
	 * @param string $scheduleDeliveryTime
	 * @param string $validityPeriod
	 * @return string message id
	 */
	protected function submit_sm(SmppAddress $source, SmppAddress $destination, $short_message=null, $tags=null, $dataCoding=SmppConstants::DATA_CODING_DEFAULT, $priority=0x00, $scheduleDeliveryTime=null, $validityPeriod=null)
	{
		// Construct PDU with mandatory fields
		$pdu = pack('a1cca'.(strlen($source->value)+1).'cca'.(strlen($destination->value)+1).'ccc'.($scheduleDeliveryTime ? 'a16x' : 'a1').($validityPeriod ? 'a16x' : 'a1').'ccccca'.(strlen($short_message)+(self::$sms_null_terminate_octetstrings ? 1 : 0)),
			self::$sms_service_type,
			$source->ton,
			$source->npi,
			$source->value,
			$destination->ton,
			$destination->npi,
			$destination->value,
			self::$sms_esm_class,
			self::$sms_protocol_id,
			$priority,
			$scheduleDeliveryTime,
			$validityPeriod,
			self::$sms_registered_delivery_flag,
			self::$sms_replace_if_present_flag,
			$dataCoding,
			self::$sms_sm_default_msg_id,
			strlen($short_message),//sm_length
			$short_message//short_message
		);

		// Add any tags
		if (!empty($tags)) {
			foreach ($tags as $tag) {
				$pdu .= $tag->getBinary();
			}
		}

		try{
			$response=$this->sendCommand(SmppConstants::SUBMIT_SM,$pdu);
			if ($response === false) {
				$msgId='';
				$msg = 'Failed to read reply of SMS Command';
				$status = 'failed';
			}
			else if ($response->status === SmppConstants::ESME_ROK){
				$msgId = unpack("a*msgid",$response->body)['msgid'];
				$status = 'sent';
				$msg='Message Sent successfully';
			}
			else if ($response->status !== SmppConstants::ESME_ROK){
				$status = 'failed';
				$msg=SmppConstants::getStatusMessage($response->status);
				$msgId='';
			}
		}
		catch(Exception $e){
			$status='failed';
			$msg=$e->getMessage();
			$msgId='';
		}

		$json['status']=$status;
		$json['msg']=$msg;
		$json['msgId']=$msgId;
		return $json;
	}

	/**
	 * Get a CSMS reference number for sar_msg_ref_num.
	 * Initializes with a random value, and then returns the number in sequence with each call.
	 */
	protected function getCsmsReference()
	{
		if (!isset($this->sar_msg_ref_num)) $this->sar_msg_ref_num = mt_rand(0,65535);
		$this->sar_msg_ref_num++;
		if ($this->sar_msg_ref_num>65535) $this->sar_msg_ref_num = 0;
		return $this->sar_msg_ref_num;
	}


	/**
	 * Split a message into multiple parts, taking the encoding into account.
	 * A character represented by an GSM 03.38 escape-sequence shall not be split in the middle.
	 * Uses str_split if at all possible, and will examine all split points for escape chars if it's required.
	 *
	 * @param string $message
	 * @param integer $split
	 * @param integer $dataCoding (optional)
	 */
	protected function splitMessageString($message, $split, $dataCoding=SmppConstants::DATA_CODING_DEFAULT)
	{
		switch ($dataCoding) {
			case SmppConstants::DATA_CODING_DEFAULT:
				$msg_length = strlen($message);
				// Do we need to do php based split?
				$numParts = floor($msg_length / $split);
				if ($msg_length % $split == 0) $numParts--;
				$slowSplit = false;

				for($i=1;$i<=$numParts;$i++) {
					if ($message[$i*$split-1] == "\x1B") {
						$slowSplit = true;
						break;
					};
				}
				if (!$slowSplit) return str_split($message,$split);

				// Split the message char-by-char
				$parts = array();
				$part = null;
				$n = 0;
				for($i=0;$i<$msg_length;$i++) {
					$c = $message[$i];
					// reset on $split or if last char is a GSM 03.38 escape char
					if ($n==$split || ($n==($split-1) && $c=="\x1B")) {
						$parts[] = $part;
						$n = 0;
						$part = null;
					}
					$part .= $c;
				}
				$parts[] = $part;
				return $parts;
			case SmppConstants::DATA_CODING_UCS2: // UCS2-BE can just use str_split since we send 132 octets per message, which gives a fine split using UCS2
			default:
				return str_split($message,$split);
		}
	}

	/**
	 * Binds the socket and opens the session on SMSC
	 * @param string $login - ESME system_id
	 * @param string $port - ESME password
	 * @return SmppPdu
	 */
	protected function _bind($login, $pass, $command_id)
	{
		// Make PDU body
		$pduBody = pack(
			'a'.(strlen($login)+1).
			'a'.(strlen($pass)+1).
			'a'.(strlen(self::$system_type)+1).
			'CCCa'.(strlen(self::$address_range)+1),
			$login, $pass, self::$system_type,
			self::$interface_version, self::$addr_ton,
			self::$addr_npi, self::$address_range
		);

		$response=$this->sendCommand($command_id,$pduBody);
		if ($response->status != SmppConstants::ESME_ROK) throw new SmppException(SmppConstants::getStatusMessage($response->status), $response->status);

		return $response;
	}

	/**
	 * Parse received PDU from SMSC.
	 * @param SmppPdu $pdu - received PDU from SMSC.
	 * @return parsed PDU as array.
	 */
	protected function parseSMS(SmppPdu $pdu)
	{
		// Check command id
		if($pdu->id != SmppConstants::DELIVER_SM) throw new InvalidArgumentException('PDU is not an received SMS');

		// Unpack PDU
		$ar=unpack("C*",$pdu->body);

		// Read mandatory params
		$service_type = $this->getString($ar,6,true);

		$source_addr_ton = next($ar);
		$source_addr_npi = next($ar);
		$source_addr = $this->getString($ar,21);
		$source = new SmppAddress($source_addr,$source_addr_ton,$source_addr_npi);

		$dest_addr_ton = next($ar);
		$dest_addr_npi = next($ar);
		$destination_addr = $this->getString($ar,21);
		$destination = new SmppAddress($destination_addr,$dest_addr_ton,$dest_addr_npi);

		$esmClass = next($ar);
		$protocolId = next($ar);
		$priorityFlag = next($ar);
		next($ar); // schedule_delivery_time
		next($ar); // validity_period
		$registeredDelivery = next($ar);
		next($ar); // replace_if_present_flag
		$dataCoding = next($ar);
		next($ar); // sm_default_msg_id
		$sm_length = next($ar);
		$message = $this->getString($ar,$sm_length);

		// Check for optional params, and parse them
		if (current($ar) !== false) {
			$tags = array();
			do {
				$tag = $this->parseTag($ar);
				if ($tag !== false) $tags[] = $tag;
			} while (current($ar) !== false);
		} else {
			$tags = null;
		}

		if (($esmClass & SmppConstants::ESM_DELIVER_SMSC_RECEIPT) != 0) {
			$sms = new SmppDeliveryReceipt($pdu->id, $pdu->status, $pdu->sequence, $pdu->body, $service_type, $source, $destination, $esmClass, $protocolId, $priorityFlag, $registeredDelivery, $dataCoding, $message, $tags);
			$sms->parseDeliveryReceipt();
		} else {
			$sms = new SmppSms($pdu->id, $pdu->status, $pdu->sequence, $pdu->body, $service_type, $source, $destination, $esmClass, $protocolId, $priorityFlag, $registeredDelivery, $dataCoding, $message, $tags);
		}

		if($this->debug) call_user_func($this->debugHandler, "Received sms:\n".print_r($sms,true));

		// Send response of recieving sms
		$response = new SmppPdu(SmppConstants::DELIVER_SM_RESP, SmppConstants::ESME_ROK, $pdu->sequence, "\x00");
		$this->sendPDU($response);
		return $sms;
	}

	/**
	 * Send the enquire link command.
	 * @return SmppPdu
	 */
	public function enquireLink()
	{
		$response = $this->sendCommand(SmppConstants::ENQUIRE_LINK, null);
		return $response;
	}

	/**
	 * Send the enquire link command.
	 * @return SmppPdu
	 */
	public function getResponse()
	{
		$response = $this->readPDU_resp(2, SmppConstants::SUBMIT_SM_RESP);

		return $response;
	}

	/**
	 * Reconnect to SMSC.
	 * This is mostly to deal with the situation were we run out of sequence numbers
	 */
	protected function reconnect()
	{
		$this->close();
		sleep(1);
		$this->transport->open();
		$this->sequence_number = 1;

		if ($this->mode == 'receiver') {
			$this->bindReceiver($this->login, $this->pass);
		} else
		if ($this->mode == 'transreceiver') {
			$this->bindTransreceiver($this->login, $this->pass);
		} else {
			$this->bindTransmitter($this->login, $this->pass);
		}
	}

	/**
	 * Sends the PDU command to the SMSC and waits for response.
	 * @param integer $id - command ID
	 * @param string $pduBody - PDU body
	 * @return SmppPdu
	 */
	protected function sendCommand($id, $pduBody)
	{
		if (!$this->transport->isOpen()) return false;
		$pdu = new SmppPdu($id, 0, $this->sequence_number, $pduBody);
		$this->sendPDU($pdu);
		$response=$this->readPDU_resp($this->sequence_number, $pdu->id);
		if ($response === false) throw new SmppException('Failed to read reply to command: 0x'.dechex($id));

		if ($response->status != SmppConstants::ESME_ROK) throw new SmppException(SmppConstants::getStatusMessage($response->status), $response->status);

		$this->sequence_number++;

		// Reached max sequence number, spec does not state what happens now, so we re-connect
		if ($this->sequence_number >= 0x7FFFFFFF) {
			$this->reconnect();
		}

		return $response;
	}

	/**
	 * Prepares and sends PDU to SMSC.
	 * @param SmppPdu $pdu
	 */
	protected function sendPDU(SmppPdu $pdu)
	{
		$length=strlen($pdu->body) + 16;
		$header=pack("NNNN", $length, $pdu->id, $pdu->status, $pdu->sequence);
		if($this->debug) {
			call_user_func($this->debugHandler, "Send PDU         : $length bytes");
			call_user_func($this->debugHandler, ' '.chunk_split(bin2hex($header.$pdu->body),2," "));
			call_user_func($this->debugHandler, ' command_id      : 0x'.dechex($pdu->id));
			call_user_func($this->debugHandler, ' sequence number : '.$pdu->sequence);
		}
		$this->transport->write($header.$pdu->body);
	}

	/**
	 * Waits for SMSC response on specific PDU.
	 * If a GENERIC_NACK with a matching sequence number, or null sequence is received instead it's also accepted.
	 * Some SMPP servers, ie. logica returns GENERIC_NACK on errors.
	 *
	 * @param integer $seq_number - PDU sequence number
	 * @param integer $command_id - PDU command ID
	 * @return SmppPdu
	 * @throws SmppException
	 */
	protected function readPDU_resp($seq_number, $command_id)
	{
		// Get response cmd id from command id
		$command_id=$command_id|SmppConstants::GENERIC_NACK;

		// Check the queue first
		$ql = count($this->pdu_queue);
		for($i=0;$i<$ql;$i++) {
			$pdu=$this->pdu_queue[$i];
			if (
				($pdu->sequence == $seq_number && ($pdu->id == $command_id || $pdu->id == SmppConstants::GENERIC_NACK)) ||
				($pdu->sequence == null && $pdu->id == SmppConstants::GENERIC_NACK)
			) {
				// remove response pdu from queue
				array_splice($this->pdu_queue, $i, 1);
				return $pdu;
			}
		}

		// Read PDUs until the one we are looking for shows up, or a generic nack pdu with matching sequence or null sequence
		do{
			$pdu=$this->readPDU();
			if ($pdu) {
				if ($pdu->sequence == $seq_number && ($pdu->id == $command_id || $pdu->id == SmppConstants::GENERIC_NACK)) return $pdu;
				if ($pdu->sequence == null && $pdu->id == SmppConstants::GENERIC_NACK) return $pdu;
				array_push($this->pdu_queue, $pdu); // unknown PDU push to queue
			}
		} while($pdu);
		return false;
	}

	/**
	 * Reads incoming PDU from SMSC.
	 * @return SmppPdu
	 */
	protected function readPDU()
	{
		// Read PDU length
		$bufLength = $this->transport->read(4);
		if(!$bufLength) return false;
		extract(unpack("Nlength", $bufLength));

		// Read PDU headers
		$bufHeaders = $this->transport->read(12);
		if(!$bufHeaders)return false;
		extract(unpack("Ncommand_id/Ncommand_status/Nsequence_number", $bufHeaders));

		// Read PDU body
		if($length-16>0){
			$body=$this->transport->readAll($length-16);
			if(!$body) throw new RuntimeException('Could not read PDU body');
		} else {
			$body=null;
		}

		if($this->debug) {
			call_user_func($this->debugHandler, "Read PDU         : $length bytes");
			call_user_func($this->debugHandler, ' '.chunk_split(bin2hex($bufLength.$bufHeaders.$body),2," "));
			call_user_func($this->debugHandler, " command id      : 0x".dechex($command_id));
			call_user_func($this->debugHandler, " command status  : 0x".dechex($command_status)." ".SmppConstants::getStatusMessage($command_status));
			call_user_func($this->debugHandler, ' sequence number : '.$sequence_number);
		}
		return new SmppPdu($command_id, $command_status, $sequence_number, $body);
	}

	/**
	 * Reads C style null padded string from the char array.
	 * Reads until $maxlen or null byte.
	 *
	 * @param array $ar - input array
	 * @param integer $maxlen - maximum length to read.
	 * @param boolean $firstRead - is this the first bytes read from array?
	 * @return read string.
	 */
	protected function getString(&$ar, $maxlen=255, $firstRead=false)
	{
		$s="";
		$i=0;
		do{
			$c = ($firstRead && $i==0) ? current($ar) : next($ar);
			if ($c != 0) $s .= chr($c);
			$i++;
		} while($i<$maxlen && $c !=0);
		return $s;
	}

	/**
	 * Read a specific number of octets from the char array.
	 * Does not stop at null byte
	 *
	 * @param array $ar - input array
	 * @param intger $length
	 */
	protected function getOctets(&$ar,$length)
	{
		$s = "";
		for($i=0;$i<$length;$i++) {
			$c = next($ar);
			if ($c === false) return $s;
			$s .= chr($c);
		}
		return $s;
	}

	protected function parseTag(&$ar)
	{
		$unpackedData = unpack('nid/nlength',pack("C2C2",next($ar),next($ar),next($ar),next($ar)));
		if (!$unpackedData) throw new InvalidArgumentException('Could not read tag data');
		extract($unpackedData);

		// Sometimes SMSC return an extra null byte at the end
		if ($length==0 && $id == 0) {
			return false;
		}

		$value = $this->getOctets($ar,$length);
		$tag = new SmppTag($id, $value, $length);
		if ($this->debug) {
			call_user_func($this->debugHandler, "Parsed tag:");
			call_user_func($this->debugHandler, " id     :0x".dechex($tag->id));
			call_user_func($this->debugHandler, " length :".$tag->length);
			call_user_func($this->debugHandler, " value  :".chunk_split(bin2hex($tag->value),2," "));
		}
		return $tag;
	}

}
