<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Request;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            // 元の例外が ModelNotFoundException だった場合、APIリクエストの場合
            if ($e->getPrevious() instanceof ModelNotFoundException || $request->is('api/v1/*')) {
                return response()->json([
                    'error' => 'Not Found',
                    'message' => 'お問い合わせが見つかりませんでした。'
                ], 404);
            }
        });
    }
}
