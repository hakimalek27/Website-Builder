<?php

namespace App\Services\Ai;

use RuntimeException;

// Output AI gagal validasi §8.4 → gagal percubaan penuh (bukan "baiki").
class DraftValidationException extends RuntimeException {}
