<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Base', description: 'Méthodes de base des réponses API')]
class ApiController extends Controller
{
    protected function success(mixed $data, string $message = "Success", int $code = 200): JsonResponse
    {
        return response()->json(["message" => $message, "data" => $data], $code);
    }

    protected function error(string $message, int $code = 400, mixed $errors = null): JsonResponse
    {
        $response = ["message" => $message];
        if ($errors) {
            $response["errors"] = $errors;
        }
        return response()->json($response, $code);
    }

    protected function created(mixed $data, string $message = "Created", int $code = 201): JsonResponse
    {
        return response()->json(["message" => $message, "data" => $data], $code);
    }

    protected function paginated($paginator): JsonResponse
    {
        return response()->json([
            "data" => $paginator->items(),
            "meta" => [
                "current_page" => $paginator->currentPage(),
                "last_page" => $paginator->lastPage(),
                "per_page" => $paginator->perPage(),
                "total" => $paginator->total(),
            ],
        ]);
    }
}
