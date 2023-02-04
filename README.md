# smpp-php
 PHP Implementation for sending SMS through SMPP Protocol.

 ## Installation

You can install the package via composer:

```bash
composer require jagdish-j-p/smpp-php
```

## Usage

* Send SMS

```php
    use JagdishJP\SmppPhp\Smpp;
    
    $smpp = new \JagdishJP\SmppPhp\Smpp('localhost',2775, true, true);
    $smpp->bindTransmitter('SENDERID', 'USERNAME', 'PASSWORD');

    $response = $smpp->sendSms('recepient number', 'Message');
    $smpp->close();
```

* Read SMS

```php
    use JagdishJP\SmppPhp\Smpp;
    
    $smpp = new \JagdishJP\SmppPhp\Smpp('localhost',2775, true, true);
    $smpp->bindReceiver('SENDERID', 'USERNAME', 'PASSWORD');

    $message = $smpp->readSms();
    $smpp->close();
```

* Send and Read SMS

```php
    use JagdishJP\SmppPhp\Smpp;
    
    $smpp = new \JagdishJP\SmppPhp\Smpp('localhost',2775, true, true);
    $smpp->bindTransreceiver('SENDERID', 'USERNAME', 'PASSWORD');

    $response = $smpp->sendSms('recepient number', 'Message');

    $message = $smpp->readSms();
    $smpp->close();
```
