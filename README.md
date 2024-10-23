# WiseService Package

A Laravel package to integrate with Wise (formerly TransferWise) API.

## Installation

1. Install via composer:

```bash
composer require your-vendor-name/wise-service
```

2. Publish the configuration file:

```bash
php artisan vendor:publish --provider="Larabisaja\WiseService\WiseServiceProvider"
```

3. Set the environment variables in your `.env` file:

```
WISE_API_URL=https://api.sandbox.transferwise.tech/v1
WISE_API_TOKEN=your-wise-api-token
WISE_API_URL_V3=https://api.sandbox.transferwise.tech/v3
```
