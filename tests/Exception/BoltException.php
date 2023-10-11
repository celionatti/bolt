<?php

declare(strict_types=1);

/**
 * ==============================================
 * ================         =====================
 * BoltException Class.
 * ================         =====================
 * ==============================================
 */

namespace Bolt\Bolt\BoltException;

use Bolt\Bolt\Bolt;
use Exception;
use Throwable;

class BoltException_oe extends Exception
{
    private $context;
    private $logFile;

    public function __construct(
        $message = "",
        $code = 0,
        Throwable $previous = null,
        $context = [],
        $logFile = 'error.log'
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
        $this->logFile = Bolt::$bolt->pathResolver->base_path("logs");
        $this->logFile = $this->logFile . DIRECTORY_SEPARATOR . $logFile;

        // Log the exception
        $this->logException();
    }

    public function getContext()
    {
        return $this->context;
    }

    public function logException()
    {
        dd("logger here");
        // $logMessage = date('Y-m-d H:i:s') . " - Exception [{$this->code}]: {$this->message}\n";
        // if (!empty($this->context)) {
        //     $logMessage .= "Bolt Context: " . json_encode($this->context) . "\n";
        // }
        // error_log($logMessage, 3, $this->logFile);
    }

    public function __toString()
    {
        dd("error here");
        $contextString = !empty($this->context) ? "\nContext: " . json_encode($this->context) : '';
        return "BoltException [{$this->code}]: {$this->message}{$contextString}\n";
    }
}
