<?php

namespace App\Providers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('MyInterface', function () {
            $request = app(\Illuminate\Http\Request::class);

            return app(MyImplementation::class, [$request->header("Authorization")]);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Response::macro('success', function ($data = [], $httpCode = 200, $message = null) {

            $response = [
                'status_code' => $httpCode,
                'message' => $message,
                'data' => $data
            ];

            return Response::make($response, $httpCode);
        });

        Response::macro('error', function ($error = [], $httpCode = 422, $settings = []) {
            if (is_array($error)) {
                $arrError = implode(', ', $error);;
            } else {
                $arrError = [];
                $tmpError = (array) $error;
                if (count($tmpError) > 1) {
                    foreach ($tmpError as $val) {
                        foreach ((array) $val as $v) {
                            if ($v !== ':message') {
                                $arrError[] = $v[0];
                            }
                        }
                    }
                } else {
                    foreach ($tmpError as $val) {
                        foreach ((array) $val as $v) {
                            if ($v !== ':message') {
                                $arrError[] = $v;
                            }
                        }
                    }
                }
            }

            if (is_array($arrError)) {
                $arrError = implode(', ', $arrError);
            }

            $response = [
                'status_code' => $httpCode,
                'message' => $arrError,
                'data' => null
            ];

            return Response::make($response, $httpCode);
        });
    }
}
