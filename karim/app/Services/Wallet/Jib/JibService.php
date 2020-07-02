<?php

namespace App\Services\Wallet\Jib;

use App\Services\Wallet\Jib\Exceptions\JibServiceException;
use App\Services\Wallet\WalletServiceInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;
use Throwable;

class JibService implements WalletServiceInterface
{
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @inheritDoc
     */
    public function userCreditor(
        int $userID,
        int $transactionType,
        int $amount,
        string $description)
    {
        try {
            $response = $this->client->request('POST', 'api/v1/wallets/credits', [
                RequestOptions::JSON => [
                    'transaction_type' => $transactionType,
                    'description' => $description,
                    'user_id' => $userID,
                    'amount' => $amount,
                ],
            ]);
        } catch (Throwable | GuzzleException $exception) {
            Log::error($exception);
            throw new JibServiceException;
        }

        $response = json_decode($response->getBody()->getContents(), true);

        $isSuccessResponse = $response['success'] ?? false;
        if (!$isSuccessResponse) {
            throw new JibServiceException(
                $response['message'] ?? 'invalid response given:' . serialize($response)
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getUserBalance(int $id): int
    {
        try {
            $response = $this->client->request('GET', 'api/v1/user/balance', [
                RequestOptions::JSON => [
                    'id' => $id,
                ],
            ]);
        } catch (\Exception $exception) {
            Log::error($exception);
            throw new JibServiceException;
        }

        $response = json_decode($response->getBody()->getContents(), true);
        if (!isset($response['status']) || !isset($response['balance']) || $response['status'] != 'OK') {
            throw new JibServiceException(
                $response['message'] ?? 'invalid response given:' . serialize($response)
            );
        }

        return intval($response['balance']);
    }

    /**
     * @inheritDoc
     */
    public function userDebtor(
        int $id,
        int $transactionType,
        int $relationId,
        int $amount,
        string $description = null)
    {
        // TODO: Implement userDebtor() method.
    }

    /**
     * @inheritDoc
     */
    public function generateUniqueReferenceId(): string
    {
        return $this->uuid4();
    }

    /**
     * Generate V4 uuiq
     */
    private function uuid4(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
