<?php

namespace WiseServicePackage;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Ramsey\Uuid\Uuid;
use Exception;

class WiseService
{
    protected Client $client;
    protected string $apiUrl;
    protected string $token;
    protected string $apiUrlV3;


    public function __construct()
    {
        $this->client = new Client();
        $this->apiUrl = env('WISE_API_URL');
        $this->apiUrlV3 = env('WISE_API_URL_V3');
        $this->token = env('WISE_API_TOKEN');
    }

    /**
     * @throws Exception
     */
    public function getAllProfiles()
    {
        $url = $this->apiUrl . '/profiles';
        try {
            $response = $this->client->get($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'Content-Type' => 'application/json'
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function getBusinessProfile()
    {
        $url = $this->apiUrl . '/profiles';
        try {
            $response = $this->client->get($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'Content-Type' => 'application/json'
                ]
            ]);

            $profiles = json_decode($response->getBody(), true);
            foreach ($profiles as $profile) {
                if ($profile['type'] === 'business') {
                    return $profile;
                }
            }

            return ['error' => 'Business profile not found'];
        } catch (GuzzleException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function createRecipient(array $recipientDetails)
    {
        $url = $this->apiUrl . '/accounts';
        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'Content-Type' => 'application/json'
                ],
                'json' => $recipientDetails
            ]);

            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function createQuote($amount)
    {
        $profile = $this->getBusinessProfile();
        $url = $this->apiUrlV3 . "/profiles/{$profile['id']}/quotes";
        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'sourceCurrency' => 'USD',
                    'targetCurrency' => 'PHP',
                    'sourceAmount' => null,
                    'targetAmount' => $amount,
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function updateQuote($quoteId, $recipientId)
    {
        $profile = $this->getBusinessProfile();
        $url = $this->apiUrlV3 . "/profiles/{$profile['id']}/quotes/{$quoteId}";
        try {
            $response = $this->client->patch($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'targetAccount' => $recipientId,
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function createTransfer($targetAccount, $quoteId, $reference)
    {
        $profile = $this->getBusinessProfile();
        $url = $this->apiUrl . '/transfers';
        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'targetAccount' => $targetAccount,
                    'quoteUuid' => $quoteId,
                    'customerTransactionId' => Uuid::uuid4()->toString(),
                    'profile' => $profile['id'],
                    'details' => [
                        'reference' => $reference,
                        "transferPurpose" => "verification.transfers.purpose.pay.bills",
                        "transferPurposeSubTransferPurpose" => "verification.sub.transfers.purpose.pay.interpretation.service",
                        "sourceOfFunds" => "verification.source.of.funds.other",
                    ]
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function fundTransfer($transferId)
    {
        $profile = $this->getBusinessProfile();
        $url = $this->apiUrlV3 . "/profiles/{$profile['id']}/transfers/{$transferId}/payments";

        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'type' => 'BALANCE',
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function getTransferResourceById(string $transferId)
    {
        $url = $this->apiUrl . "/transfers/$transferId";

        try {
            $response = $this->client->get($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function createTransferRequirements($targetAccount, $quoteId)
    {
        $url = $this->apiUrl . '/transfer-requirements';
        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'targetAccount' => $targetAccount,
                    'quoteUuid' => $quoteId,
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * This returns the necessary requirements for the user to send money to the recipient
     *
     * @throws Exception
     */
    public function getAccountRequirements(float|int $amount = 400000)
    {
        $url = $this->apiUrl . "/account-requirements?source=USD&target=PHP&sourceAmount=$amount";
        try {
            $response = $this->client->get($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'Content-Type' => 'application/json'
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
