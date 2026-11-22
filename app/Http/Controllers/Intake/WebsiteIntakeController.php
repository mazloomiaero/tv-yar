<?php

declare(strict_types=1);

namespace App\Http\Controllers\Intake;

use App\Actions\Intake\CreateWebsiteIntake;
use App\Http\Controllers\Controller;
use App\Http\Requests\Intake\StoreWebsiteIntakeRequest;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class WebsiteIntakeController extends Controller
{
    public function store(StoreWebsiteIntakeRequest $request, CreateWebsiteIntake $createWebsiteIntake): JsonResponse
    {
        try {
            $serviceRequest = $createWebsiteIntake->execute($request->validated());
        } catch (InvalidArgumentException|RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'ثبت درخواست با خطا مواجه شد.',
            ], 500);
        }

        return response()->json([
            'message' => 'درخواست با موفقیت ثبت شد.',
            'request_number' => $serviceRequest->request_number,
        ], 201);
    }
}
