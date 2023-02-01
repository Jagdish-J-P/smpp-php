<?php

namespace JagdishJP\SmppPhp\Protocols;

use InvalidArgumentException;

/**
 * Primitive class for encapsulating smpp addresses
 * @author hd@onlinecity.dk
 */
class SmppAddress
{
	public $ton; // type-of-number
	public $npi; // numbering-plan-indicator
	public $value;

	/**
	 * Construct a new object of class Address
	 * 
	 * @param string $value
	 * @param integer $ton
	 * @param integer $npi
	 * @throws InvalidArgumentException
	 */
	public function __construct($value,$ton=SmppConstants::TON_UNKNOWN,$npi=SmppConstants::NPI_UNKNOWN)
	{
		// Address-Value field may contain 10 octets (12-length-type), see 3GPP TS 23.040 v 9.3.0 - section 9.1.2.5 page 46.
		if ($ton == SmppConstants::TON_ALPHANUMERIC && strlen($value) > 11) throw new InvalidArgumentException('Alphanumeric address may only contain 11 chars');
		if ($ton == SmppConstants::TON_INTERNATIONAL && $npi == SmppConstants::NPI_E164 && strlen($value) > 15) throw new InvalidArgumentException('E164 address may only contain 15 digits');
		
		$this->value = (string) $value;
		$this->ton = $ton;
		$this->npi = $npi;
	}
}
