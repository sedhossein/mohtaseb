<?php

namespace App\Http\Controllers;

use App\Jib\Data\Responses\CreditsSuccessResponse;
use App\Jib\Data\Responses\FailureResponse;
use App\Jib\Data\Responses\GetWalletFailureResponse;
use App\Jib\Data\Responses\GetWalletSuccessResponse;
use App\Jib\Exceptions\WalletNotFoundException;
use App\Jib\Exceptions\WalletServiceException;
use App\Jib\WalletServiceInterface;
use App\Utils\Enums\TransactionTypeEnum;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class WalletController extends Controller
{
    public function getWallet(
        WalletServiceInterface $walletService,
        Request $request,
        ResponseFactory $responseFactory)
    {
        $user_id = $request->route('user_id');
        $validator = Validator::make(
            ['user_id' => $user_id],
            ['user_id' => 'required|regex:/^0?9[0-9]{9}$/']
        );
        if ($validator->fails()) {
            return $responseFactory->json(
                new GetWalletFailureResponse("phone is invalid"),
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $walletResponse = $walletService->getUserWallet(
                (int)$user_id // converts `09106802437` to `9106802437`
            );

            $response = new GetWalletSuccessResponse(
                $walletResponse->getWallet()
            );
            $status = Response::HTTP_OK;
        } catch (WalletNotFoundException $e) {
            Log::error($e);
            $response = new GetWalletFailureResponse("wallet not found");
            $status = Response::HTTP_NOT_FOUND;
        } catch (WalletServiceException $e) {
            Log::error($e);
            $response = new GetWalletFailureResponse("failed to retrieve wallet");
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return $responseFactory->json(
            $response,
            $status
        );
    }

    public function credits(
        WalletServiceInterface $walletService,
        Request $request,
        ResponseFactory $responseFactory)
    {
        $request->validate([
            'transaction_type' => 'required|in:' . TransactionTypeEnum::GiftCode,
            'user_id' => 'required|regex:/^0?9[0-9]{9}$/',
            'amount' => 'required|numeric',
            'description' => 'required',
        ]);

        $transaction_type = $request->post('transaction_type');
        $user_id = (int)$request->post('user_id');
        $amount = $request->post('amount');
        $description = $request->post('description');

        try {
            $referenceID = $walletService->userCreditor(
                $user_id,
                $transaction_type,
                $amount,
                $description
            );
        } catch (GuzzleException $exception) {
            Log::error($exception);
            return $responseFactory->json(
                new FailureResponse("failed to charging wallet"),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $responseFactory->json(
            new CreditsSuccessResponse($referenceID),
            Response::HTTP_OK
        );
    }
}
