<?php

namespace JagdishJP\SmppPhp\Protocols;

/**
 * Primitive class to represent SMPP optional params, also know as TLV (Tag-Length-Value) params.
 *
 * @author hd@onlinecity.dk
 */
class SmppTag
{
    public const DEST_ADDR_SUBUNIT = 0x0005;

    public const DEST_NETWORK_TYPE = 0x0006;

    public const DEST_BEARER_TYPE = 0x0007;

    public const DEST_TELEMATICS_ID = 0x0008;

    public const SOURCE_ADDR_SUBUNIT = 0x000D;

    public const SOURCE_NETWORK_TYPE = 0x000E;

    public const SOURCE_BEARER_TYPE = 0x000F;

    public const SOURCE_TELEMATICS_ID = 0x0010;

    public const QOS_TIME_TO_LIVE = 0x0017;

    public const PAYLOAD_TYPE = 0x0019;

    public const ADDITIONAL_STATUS_INFO_TEXT = 0x001D;

    public const RECEIPTED_MESSAGE_ID = 0x001E;

    public const MS_MSG_WAIT_FACILITIES = 0x0030;

    public const PRIVACY_INDICATOR = 0x0201;

    public const SOURCE_SUBADDRESS = 0x0202;

    public const DEST_SUBADDRESS = 0x0203;

    public const USER_MESSAGE_REFERENCE = 0x0204;

    public const USER_RESPONSE_CODE = 0x0205;

    public const SOURCE_PORT = 0x020A;

    public const DESTINATION_PORT = 0x020B;

    public const SAR_MSG_REF_NUM = 0x020C;

    public const LANGUAGE_INDICATOR = 0x020D;

    public const SAR_TOTAL_SEGMENTS = 0x020E;

    public const SAR_SEGMENT_SEQNUM = 0x020F;

    public const SC_INTERFACE_VERSION = 0x0210;

    public const CALLBACK_NUM_PRES_IND = 0x0302;

    public const CALLBACK_NUM_ATAG = 0x0303;

    public const NUMBER_OF_MESSAGES = 0x0304;

    public const CALLBACK_NUM = 0x0381;

    public const DPF_RESULT = 0x0420;

    public const SET_DPF = 0x0421;

    public const MS_AVAILABILITY_STATUS = 0x0422;

    public const NETWORK_ERROR_CODE = 0x0423;

    public const MESSAGE_PAYLOAD = 0x0424;

    public const DELIVERY_FAILURE_REASON = 0x0425;

    public const MORE_MESSAGES_TO_SEND = 0x0426;

    public const MESSAGE_STATE = 0x0427;

    public const USSD_SERVICE_OP = 0x0501;

    public const DISPLAY_TIME = 0x1201;

    public const SMS_SIGNAL = 0x1203;

    public const MS_VALIDITY = 0x1204;

    public const ALERT_ON_MESSAGE_DELIVERY = 0x130C;

    public const ITS_REPLY_TYPE = 0x1380;

    public const ITS_SESSION_INFO = 0x1383;

    public $id;

    public $length;

    public $value;

    public $type;

    /**
     * Construct a new TLV param.
     * The value must either be pre-packed with pack(), or a valid pack-type must be specified.
     *
     * @param int $id
     * @param string $value
     * @param int $length (optional)
     * @param string $type (optional)
     */
    public function __construct($id, $value, $length = null, $type = 'a*')
    {
        $this->id     = $id;
        $this->value  = $value;
        $this->length = $length;
        $this->type   = $type;
    }

    /**
     * Get the TLV packed into a binary string for transport.
     *
     * @return string
     */
    public function getBinary()
    {
        return pack('nn' . $this->type, $this->id, ($this->length ? $this->length : strlen($this->value)), $this->value);
    }
}
