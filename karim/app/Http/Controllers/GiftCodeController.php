<?php

namespace App\Http\Controllers;

use App\Data\Responses\ApplyFailureResponse;
use App\Data\Responses\ApplySuccessResponse;
use App\Data\Responses\GetWinnersSuccessResponse;
use App\Karim\Exceptions\ApplyGiftCodeFailureException;
use App\Karim\Exceptions\DuplicateGiftCodeRequestException;
use App\Karim\Exceptions\GiftCodeFinishedException;
use App\Karim\Exceptions\InvalidGiftCodeException;
use App\Karim\VoucherServiceInterface;
use App\Services\Wallet\Exceptions\WalletServiceException;
use App\Services\Wallet\WalletServiceInterface;
use App\Utils\Enums\TransactionTypeEnum;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class GiftCodeController extends Controller
{
    public function apply(
        WalletServiceInterface $walletService,
        VoucherServiceInterface $voucherService,
        Request $request,
        ResponseFactory $responseFactory): Response
    {
        $request->validate([
            'code' => 'required|max:32|min:2',
            'phone' => 'required|regex:/^(09)[0-9]{9}$/',
        ]);

        // converts `09106802437` to `9106802437`
        $id = (int)$request->input('phone');
        $code = $request->input('code');
        try {
            $voucherResponse = $voucherService->applyGiftCodeFor($id, $code);

            $walletService->userCreditor(
                $id,
                TransactionTypeEnum::GiftCode,
                $voucherResponse->getAmount(),
                $voucherResponse->getDescription()
            );

            $response = new ApplySuccessResponse(
                "Gift code has been applied successfully.",
                $voucherResponse->getAmount(),
                $voucherResponse->getDescription()
            );
            $status = Response::HTTP_OK;
        } catch (DuplicateGiftCodeRequestException $exception) {
            $response = new ApplyFailureResponse("This gift code is used before");
            $status = Response::HTTP_TOO_MANY_REQUESTS;
            Log::info($exception);
        } catch (GiftCodeFinishedException $exception) {
            $response = new ApplyFailureResponse("This gift code is finished");
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
            Log::info($exception);
        } catch (ApplyGiftCodeFailureException $exception) {
            $response = new ApplyFailureResponse("Applying the code failed! please try again");
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        } catch (InvalidGiftCodeException $exception) {
            $response = new ApplyFailureResponse("Gift code is invalid");
            $status = Response::HTTP_BAD_REQUEST;
            Log::info($exception);
        } catch (WalletServiceException $exception) {
            // let's revert reserved gift code
            try {
                $voucherService->freeGiftCode($id, $code);
            } catch (Exception $exception) {
                Log::error($exception);
            }

            $response = new ApplyFailureResponse(
                "System can't charge your wallet: "
            );
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
            Log::error($exception);
        } catch (\Throwable $exception) {
            $response = new ApplyFailureResponse("Something went wrong! please call administrator");
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
            Log::error($exception);
        }

        return $responseFactory->json($response, $status);
    }

    public function getWinners(
        VoucherServiceInterface $voucherService,
        Request $request,
        ResponseFactory $responseFactory)
    {
        $request->validate([
            'page' => 'gte:1',
        ]);

        $winnersResponse = $voucherService->getGiftCodeWinners(
            (int)$request->input('page', 1),
            20 // FIXME: it can be configurable and read from ENV
        );

        return $responseFactory->json(
            new GetWinnersSuccessResponse(
                "winners list loading successfully",
                $winnersResponse->getCount(),
                $winnersResponse->getList()
            )
        );
    }
}
