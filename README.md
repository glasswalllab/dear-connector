# DEAR Systems PHP Wrapper
 
This package provides an integration to DEAR Systems (https://dearsystems.com/).

API Docs - https://dearinventory.docs.apiary.io/

[![Latest Version](https://img.shields.io/github/release/glasswalllab/dear-connector.svg?style=flat-square)](https://github.com/glasswalllab/dear-connector/releases)

## Installation

You can install the package via composer:

```bash
composer require glasswalllab/dearconnector 
```

## Usage

1. Setup API Application in your DEAR Systems account (Integrations -> API -> +)

2. Include the following variables in your .env

```
DEAR_ACCOUNT_ID=YOUR_ACCOUNT_ID
DEAR_APP_KEY=YOUR_APP_KEY
DEAR_BASE_API_URL=https://inventory.dearsystems.com/externalapi/v2/
```

3. Run **php artisan migrate** to create the api_logs database table

### Sample Usage (Laravel)

```php
use glasswalllab\arofloconnector\DearConnector;

//Get Customer List
$dear = new DearConnector();
$dear->CallDEAR('customer','get',[]);

//Post Sale
$dear = new DearConnector();

$testSale = array(
    'CustomerID' =>'bc8e0faa-31fb-4d39-93f4-d91a5d192b1c',
    'location' => 'Main Warehouse',
);
$dear->CallDEAR('sale','POST',$testSale);

```

### Security

If you discover any security related issues, please email sreid@gwlab.com.au instead of using the issue tracker.