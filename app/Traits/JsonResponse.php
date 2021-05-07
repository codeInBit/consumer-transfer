<?php

namespace App\Traits;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\DatatableForResource;
use Exception;

trait JsonResponse
{
    public function successResponse($data = null, $message = "Operation Successful", $statusCode = Response::HTTP_OK)
    {
        $response = [
            "success" => true,
            "message" => $message
        ];

        if ($data) {
            $response["data"] = $data;
        }
        return response()->json($response, $statusCode);
    }

    public function datatableResponse($query, string $resources, array $config = [])
    {
        $data = DatatableForResource::make($query, $resources, $config);

        if ($data instanceof BinaryFileResponse) {
            return $data;
        }

        $response = [
            "success" => true,
            "message" => "Data fetched successfuly"
        ];

        if ($data) {
            $response["data"] = $data;
        }
        return response()->json($response, Response::HTTP_OK);
    }

    public function errorResponse($data = null, $message = null, $statusCode = Response::HTTP_BAD_REQUEST)
    {
        $response = [
            "success" => false,
            "message" => $message,
        ];

        if ($data) {
            $response["data"] = $data;
        }
        return response()->json($response, $statusCode);
    }

    public function fatalErrorResponse(Exception $e, $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        $line = $e->getTrace();

        $error = [
            "message" => $e->getMessage(),
            "trace" => $line[0],
            "mini_trace" => $line[1]
        ];

        if (config("app.debug") == false) {
            $error = null;
        }

        $response = [
            "success" => false,
            "message" => "Oops! Something went wrong on the server",
            "error" => $error
        ];
        return response()->json($response, $statusCode);
    }
}
