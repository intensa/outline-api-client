# OutlineVPN API Client

A simple API client for [OutlineVPN](https://getoutline.org/ru/) written in PHP.

The project implemented a class for working with OutlineVPN api methods and a class for convenient interaction with access keys

## Installation

Install the latest version with

```bash
$ composer require intensa/outline-api-client
```

## Requirements

PHP >= 7.4

## How to use

### Usage API Client

```php

require 'vendor/autoload.php';

use OutlineApiClient\OutlineApiClient;

try {

    // Your Outline server address
    $serverUrl = 'https://127.0.0.1:3333/YZwl3D1r-B6cNYzQ';

    $api = new OutlineApiClient($serverUrl);

    // Get an array of all server keys
    $keysList = $api->getKeys();

    // Create new key
    $key = $api->create();

    // Rename exist key.
    // Passing key id and new name
    $api->setName($key['id'], 'New key name');

    // Set transfer data limit for key.
    // Passing key id and limit in bytes.
    // In the example set 5MB
    $api->setLimit($key['id'], 5 * 1024 * 1024);

    // Remove key limit
    // Passing key id
    $api->deleteLimit($key['id']);

    // Delete key
    $api->delete($key['id']);

    // Get an array of used traffic for all keys
    $transferData = $api->metricsTransfer();
} catch (\Exception $e) {
    // Handle exception
}
```

### Usage OutlineVPN key wrapper

Interaction with an existing key

```php
<?php
require 'vendor/autoload.php';

use OutlineApiClient\OutlineKey;

try {

    // Your Outline server address
    $serverUrl = 'https://127.0.0.1:3333/YZwl3D1r-B6cNYzQ';

    // Key id
    $keyId = 1;
    
    // Initializing an object and getting key data
    $key = (new OutlineKey($serverUrl))->load($keyId);
    
    // Get key id
    $key->getId();
    
    // Get key name
    $key->getName();
    
    // Get key transfer traffic
    $key->getTransfer();
    
    // Get access link 
    $key->getAccessUrl();

    // Rename exist key.
    // Passing key id and new name
    $key->rename('New name');

    // Set transfer data limit for key.
    // Passing limit in bytes.
    // In the example set 5MB
    $key->limit(5 * 1024 * 1024);

    // Remove key traffic limit
    $key->deleteLimit();
    
    // Delete key
    $key->delete();
    
} catch (\Exception $e) {
    // Handle exception
}

```

Creating a new key on the server

```php
<?php
require 'vendor/autoload.php';

use OutlineApiClient\OutlineKey;

try {
    // Your Outline server address
    $serverUrl = 'https://127.0.0.1:3333/YZwl3D1r-B6cNYzQ';
    
    // Initializing an object and creating new key
    // Passing to method create() key name and traffic limit (optional)
    $key = (new OutlineKey($serverUrl))->create('Key name', 5 * 1024 * 1024);

} catch (\Exception $e) {

}
```