# smpp-php
 PHP Implementation for sending SMS through SMPP Protocol.

## Become a sponsor

[![](.github/assets/support.png)](https://github.com/sponsors/Jagdish-J-P)

Your support allows me to keep this package free, up-to-date and maintainable. Alternatively, you can **[spread the word!](http://twitter.com/share?text=I+am+using+this+cool+PHP+package&url=https://github.com/jagdish-j-p/smpp-php&hashtags=PHP,Laravel,SMPP)**

 ## Installation

You can install the package via composer:

```bash
composer require jagdish-j-p/smpp-php
```

## Usage

* Send SMS

```php
    use JagdishJP\SmppPhp\Smpp;
    
    $smpp = new Smpp('localhost',2775, true, true);
    $smpp->bindTransmitter('SENDERID', 'USERNAME', 'PASSWORD');

    $response = $smpp->sendSms('recepient number', 'Message');
    $smpp->close();
```

* Read SMS

```php
    use JagdishJP\SmppPhp\Smpp;
    
    $smpp = new Smpp('localhost',2775, true, true);
    $smpp->bindReceiver('SENDERID', 'USERNAME', 'PASSWORD');

    $message = $smpp->readSms();
    $smpp->close();
```

* Send and Read SMS

```php
    use JagdishJP\SmppPhp\Smpp;
    
    $smpp = new Smpp('localhost',2775, true, true);
    $smpp->bindTransreceiver('SENDERID', 'USERNAME', 'PASSWORD');

    $response = $smpp->sendSms('recepient number', 'Message');

    $message = $smpp->readSms();
    $smpp->close();
```
