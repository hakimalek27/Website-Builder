<?php

namespace App\Exceptions;

use RuntimeException;

// Gate penjanaan gagal (kuota/cooldown/kunci/status) — §8.6/§6.12.
class GateException extends RuntimeException {}
