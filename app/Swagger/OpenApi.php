<?php

namespace App\Swagger;

/**
 * @OA\Info(
 *     title="Blog API",
 *     version="1.0.0",
 *     description="API documentation for Blog"
 * )
 *
 * @OA\Server(
 *     url="http://localhost/Blog/public",
 *     description="Local server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class OpenApi {}
