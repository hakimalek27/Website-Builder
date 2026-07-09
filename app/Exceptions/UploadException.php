<?php

namespace App\Exceptions;

use RuntimeException;

// Dilempar bila muat naik gagal semakan keselamatan (§11.4).
class UploadException extends RuntimeException {}
