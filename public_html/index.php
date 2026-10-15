<?php
declare(strict_types=1);

/* Tek giriş noktası (front controller). Uygulama kodu web kökünün dışında: ../kdrk */
require __DIR__ . '/../kdrk/bootstrap.php';

App\Core\App::handle();
