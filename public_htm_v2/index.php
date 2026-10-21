<?php
declare(strict_types=1);

/* Tek giriş noktası (front controller). Uygulama kodu web kökünün dışında: ../kdrk */
require __DIR__ . '/../kdrk_v2/bootstrap.php';

App\Core\App::handle();
