<?php

namespace JagdishJP\SmppPhp\Protocols;

/**
 * Numerous constants for SMPP v3.4
 * Based on specification at: http://www.smsforum.net/SMPP_v3_4_Issue1_2.zip.
 */
class SmppConstants
{
    // Command ids - SMPP v3.4 - 5.1.2.1 page 110-111
    public const GENERIC_NACK = 0x80000000;

    public const BIND_RECEIVER = 0x00000001;

    public const BIND_RECEIVER_RESP = 0x80000001;

    public const BIND_TRANSMITTER = 0x00000002;

    public const BIND_TRANSMITTER_RESP = 0x80000002;

    public const QUERY_SM = 0x00000003;

    public const QUERY_SM_RESP = 0x80000003;

    public const SUBMIT_SM = 0x00000004;

    public const SUBMIT_SM_RESP = 0x80000004;

    public const DELIVER_SM = 0x00000005;

    public const DELIVER_SM_RESP = 0x80000005;

    public const UNBIND = 0x00000006;

    public const UNBIND_RESP = 0x80000006;

    public const REPLACE_SM = 0x00000007;

    public const REPLACE_SM_RESP = 0x80000007;

    public const CANCEL_SM = 0x00000008;

    public const CANCEL_SM_RESP = 0x80000008;

    public const BIND_TRANSCEIVER = 0x00000009;

    public const BIND_TRANSCEIVER_RESP = 0x80000009;

    public const OUTBIND = 0x0000000B;

    public const ENQUIRE_LINK = 0x00000015;

    public const ENQUIRE_LINK_RESP = 0x80000015;

    //  Command status - SMPP v3.4 - 5.1.3 page 112-114
    public const ESME_ROK = 0x00000000; // No Error

    public const ESME_RINVMSGLEN = 0x00000001; // Message Length is invalid

    public const ESME_RINVCMDLEN = 0x00000002; // Command Length is invalid

    public const ESME_RINVCMDID = 0x00000003; // Invalid Command ID

    public const ESME_RINVBNDSTS = 0x00000004; // Incorrect BIND Status for given command

    public const ESME_RALYBND = 0x00000005; // ESME Already in Bound State

    public const ESME_RINVPRTFLG = 0x00000006; // Invalid Priority Flag

    public const ESME_RINVREGDLVFLG = 0x00000007; // Invalid Registered Delivery Flag

    public const ESME_RSYSERR = 0x00000008; // System Error

    public const ESME_RINVSRCADR = 0x0000000A; // Invalid Source Address

    public const ESME_RINVDSTADR = 0x0000000B; // Invalid Dest Addr

    public const ESME_RINVMSGID = 0x0000000C; // Message ID is invalid

    public const ESME_RBINDFAIL = 0x0000000D; // Bind Failed

    public const ESME_RINVPASWD = 0x0000000E; // Invalid Password

    public const ESME_RINVSYSID = 0x0000000F; // Invalid System ID

    public const ESME_RCANCELFAIL = 0x00000011; // Cancel SM Failed

    public const ESME_RREPLACEFAIL = 0x00000013; // Replace SM Failed

    public const ESME_RMSGQFUL = 0x00000014; // Message Queue Full

    public const ESME_RINVSERTYP = 0x00000015; // Invalid Service Type

    public const ESME_RINVNUMDESTS = 0x00000033; // Invalid number of destinations

    public const ESME_RINVDLNAME = 0x00000034; // Invalid Distribution List name

    public const ESME_RINVDESTFLAG = 0x00000040; // Destination flag (submit_multi)

    public const ESME_RINVSUBREP = 0x00000042; // Invalid ‘submit with replace’ request (i.e. submit_sm with replace_if_present_flag set)

    public const ESME_RINVESMSUBMIT = 0x00000043; // Invalid esm_SUBMIT field data

    public const ESME_RCNTSUBDL = 0x00000044; // Cannot Submit to Distribution List

    public const ESME_RSUBMITFAIL = 0x00000045; // submit_sm or submit_multi failed

    public const ESME_RINVSRCTON = 0x00000048; // Invalid Source address TON

    public const ESME_RINVSRCNPI = 0x00000049; // Invalid Source address NPI

    public const ESME_RINVDSTTON = 0x00000050; // Invalid Destination address TON

    public const ESME_RINVDSTNPI = 0x00000051; // Invalid Destination address NPI

    public const ESME_RINVSYSTYP = 0x00000053; // Invalid system_type field

    public const ESME_RINVREPFLAG = 0x00000054; // Invalid replace_if_present flag

    public const ESME_RINVNUMMSGS = 0x00000055; // Invalid number of messages

    public const ESME_RTHROTTLED = 0x00000058; // Throttling error (ESME has exceeded allowed message limits)

    public const ESME_RINVSCHED = 0x00000061; // Invalid Scheduled Delivery Time

    public const ESME_RINVEXPIRY = 0x00000062; // Invalid message (Expiry time)

    public const ESME_RINVDFTMSGID = 0x00000063; // Predefined Message Invalid or Not Found

    public const ESME_RX_T_APPN = 0x00000064; // ESME Receiver Temporary App Error Code

    public const ESME_RX_P_APPN = 0x00000065; // ESME Receiver Permanent App Error Code

    public const ESME_RX_R_APPN = 0x00000066; // ESME Receiver Reject Message Error Code

    public const ESME_RQUERYFAIL = 0x00000067; // query_sm request failed

    public const ESME_RINVOPTPARSTREAM = 0x000000C0; // Error in the optional part of the PDU Body.

    public const ESME_ROPTPARNOTALLWD = 0x000000C1; // Optional Parameter not allowed

    public const ESME_RINVPARLEN = 0x000000C2; // Invalid Parameter Length.

    public const ESME_RMISSINGOPTPARAM = 0x000000C3; // Expected Optional Parameter missing

    public const ESME_RINVOPTPARAMVAL = 0x000000C4; // Invalid Optional Parameter Value

    public const ESME_RDELIVERYFAILURE = 0x000000FE; // Delivery Failure (data_sm_resp)

    public const ESME_RUNKNOWNERR = 0x000000FF; // Unknown Error

    // SMPP v3.4 - 5.2.5 page 117
    public const TON_UNKNOWN = 0x00;

    public const TON_INTERNATIONAL = 0x01;

    public const TON_NATIONAL = 0x02;

    public const TON_NETWORKSPECIFIC = 0x03;

    public const TON_SUBSCRIBERNUMBER = 0x04;

    public const TON_ALPHANUMERIC = 0x05;

    public const TON_ABBREVIATED = 0x06;

    // SMPP v3.4 - 5.2.6 page 118
    public const NPI_UNKNOWN = 0x00;

    public const NPI_E164 = 0x01;

    public const NPI_DATA = 0x03;

    public const NPI_TELEX = 0x04;

    public const NPI_E212 = 0x06;

    public const NPI_NATIONAL = 0x08;

    public const NPI_PRIVATE = 0x09;

    public const NPI_ERMES = 0x0A;

    public const NPI_INTERNET = 0x0E;

    public const NPI_WAPCLIENT = 0x12;

    // ESM bits 1-0 - SMPP v3.4 - 5.2.12 page 121-122
    public const ESM_SUBMIT_MODE_DATAGRAM = 0x01;

    public const ESM_SUBMIT_MODE_FORWARD = 0x02;

    public const ESM_SUBMIT_MODE_STOREANDFORWARD = 0x03;

    // ESM bits 5-2
    public const ESM_SUBMIT_BINARY = 0x04;

    public const ESM_SUBMIT_TYPE_ESME_D_ACK = 0x08;

    public const ESM_SUBMIT_TYPE_ESME_U_ACK = 0x10;

    public const ESM_DELIVER_SMSC_RECEIPT = 0x04;

    public const ESM_DELIVER_SME_ACK = 0x08;

    public const ESM_DELIVER_U_ACK = 0x10;

    public const ESM_DELIVER_CONV_ABORT = 0x18;

    public const ESM_DELIVER_IDN = 0x20; // Intermediate delivery notification

    // ESM bits 7-6
    public const ESM_UHDI = 0x40;

    public const ESM_REPLYPATH = 0x80;

    // SMPP v3.4 - 5.2.17 page 124
    public const REG_DELIVERY_NO = 0x00;

    public const REG_DELIVERY_SMSC_BOTH = 0x01; // both success and failure

    public const REG_DELIVERY_SMSC_FAILED = 0x02;

    public const REG_DELIVERY_SME_D_ACK = 0x04;

    public const REG_DELIVERY_SME_U_ACK = 0x08;

    public const REG_DELIVERY_SME_BOTH = 0x10;

    public const REG_DELIVERY_IDN = 0x16; // Intermediate notification

    // SMPP v3.4 - 5.2.18 page 125
    public const REPLACE_NO = 0x00;

    public const REPLACE_YES = 0x01;

    // SMPP v3.4 - 5.2.19 page 126
    public const DATA_CODING_DEFAULT = 0;

    public const DATA_CODING_IA5 = 1; // IA5 (CCITT T.50)/ASCII (ANSI X3.4)

    public const DATA_CODING_BINARY_ALIAS = 2;

    public const DATA_CODING_ISO8859_1 = 3; // Latin 1

    public const DATA_CODING_BINARY = 4;

    public const DATA_CODING_JIS = 5;

    public const DATA_CODING_ISO8859_5 = 6; // Cyrllic

    public const DATA_CODING_ISO8859_8 = 7; // Latin/Hebrew

    public const DATA_CODING_UCS2 = 8; // UCS-2BE (Big Endian)

    public const DATA_CODING_PICTOGRAM = 9;

    public const DATA_CODING_ISO2022_JP = 10; // Music codes

    public const DATA_CODING_KANJI = 13; // Extended Kanji JIS

    public const DATA_CODING_KSC5601 = 14;

    // SMPP v3.4 - 5.2.25 page 129
    public const DEST_FLAG_SME = 1;

    public const DEST_FLAG_DISTLIST = 2;

    // SMPP v3.4 - 5.2.28 page 130
    public const STATE_ENROUTE = 1;

    public const STATE_DELIVERED = 2;

    public const STATE_EXPIRED = 3;

    public const STATE_DELETED = 4;

    public const STATE_UNDELIVERABLE = 5;

    public const STATE_ACCEPTED = 6;

    public const STATE_UNKNOWN = 7;

    public const STATE_REJECTED = 8;

    public static function getStatusMessage($statuscode)
    {
        switch ($statuscode) {
            case SmppConstants::ESME_ROK:
                return 'No Error';

            case SmppConstants::ESME_RINVMSGLEN:
                return 'Message Length is invalid';

            case SmppConstants::ESME_RINVCMDLEN:
                return 'Command Length is invalid';

            case SmppConstants::ESME_RINVCMDID:
                return 'Invalid Command ID';

            case SmppConstants::ESME_RINVBNDSTS:
                return 'Incorrect BIND Status for given command';

            case SmppConstants::ESME_RALYBND:
                return 'ESME Already in Bound State';

            case SmppConstants::ESME_RINVPRTFLG:
                return 'Invalid Priority Flag';

            case SmppConstants::ESME_RINVREGDLVFLG:
                return 'Invalid Registered Delivery Flag';

            case SmppConstants::ESME_RSYSERR:
                return 'System Error';

            case SmppConstants::ESME_RINVSRCADR:
                return 'Invalid Source Address';

            case SmppConstants::ESME_RINVDSTADR:
                return 'Invalid Dest Addr';

            case SmppConstants::ESME_RINVMSGID:
                return 'Message ID is invalid';

            case SmppConstants::ESME_RBINDFAIL:
                return 'Bind Failed';

            case SmppConstants::ESME_RINVPASWD:
                return 'Invalid Password';

            case SmppConstants::ESME_RINVSYSID:
                return 'Invalid System ID';

            case SmppConstants::ESME_RCANCELFAIL:
                return 'Cancel SM Failed';

            case SmppConstants::ESME_RREPLACEFAIL:
                return 'Replace SM Failed';

            case SmppConstants::ESME_RMSGQFUL:
                return 'Message Queue Full';

            case SmppConstants::ESME_RINVSERTYP:
                return 'Invalid Service Type';

            case SmppConstants::ESME_RINVNUMDESTS:
                return 'Invalid number of destinations';

            case SmppConstants::ESME_RINVDLNAME:
                return 'Invalid Distribution List name';

            case SmppConstants::ESME_RINVDESTFLAG:
                return 'Destination flag (submit_multi)';

            case SmppConstants::ESME_RINVSUBREP:
                return 'Invalid ‘submit with replace’ request (i.e. submit_sm with replace_if_present_flag set)';

            case SmppConstants::ESME_RINVESMSUBMIT:
                return 'Invalid esm_SUBMIT field data';

            case SmppConstants::ESME_RCNTSUBDL:
                return 'Cannot Submit to Distribution List';

            case SmppConstants::ESME_RSUBMITFAIL:
                return 'submit_sm or submit_multi failed';

            case SmppConstants::ESME_RINVSRCTON:
                return 'Invalid Source address TON';

            case SmppConstants::ESME_RINVSRCNPI:
                return 'Invalid Source address NPI';

            case SmppConstants::ESME_RINVDSTTON:
                return 'Invalid Destination address TON';

            case SmppConstants::ESME_RINVDSTNPI:
                return 'Invalid Destination address NPI';

            case SmppConstants::ESME_RINVSYSTYP:
                return 'Invalid system_type field';

            case SmppConstants::ESME_RINVREPFLAG:
                return 'Invalid replace_if_present flag';

            case SmppConstants::ESME_RINVNUMMSGS:
                return 'Invalid number of messages';

            case SmppConstants::ESME_RTHROTTLED:
                return 'Throttling error (ESME has exceeded allowed message limits)';

            case SmppConstants::ESME_RINVSCHED:
                return 'Invalid Scheduled Delivery Time';

            case SmppConstants::ESME_RINVEXPIRY:
                return 'Invalid message (Expiry time)';

            case SmppConstants::ESME_RINVDFTMSGID:
                return 'Predefined Message Invalid or Not Found';

            case SmppConstants::ESME_RX_T_APPN:
                return 'ESME Receiver Temporary App Error Code';

            case SmppConstants::ESME_RX_P_APPN:
                return 'ESME Receiver Permanent App Error Code';

            case SmppConstants::ESME_RX_R_APPN:
                return 'ESME Receiver Reject Message Error Code';

            case SmppConstants::ESME_RQUERYFAIL:
                return 'query_sm request failed';

            case SmppConstants::ESME_RINVOPTPARSTREAM:
                return 'Error in the optional part of the PDU Body.';

            case SmppConstants::ESME_ROPTPARNOTALLWD:
                return 'Optional Parameter not allowed';

            case SmppConstants::ESME_RINVPARLEN:
                return 'Invalid Parameter Length.';

            case SmppConstants::ESME_RMISSINGOPTPARAM:
                return 'Expected Optional Parameter missing';

            case SmppConstants::ESME_RINVOPTPARAMVAL:
                return 'Invalid Optional Parameter Value';

            case SmppConstants::ESME_RDELIVERYFAILURE:
                return 'Delivery Failure (data_sm_resp)';

            case SmppConstants::ESME_RUNKNOWNERR:
                return 'Unknown Error';

            default:
                return 'Unknown statuscode: ' . dechex($statuscode);
        }
    }
}
