<?php
class AdvancedCustomException extends Exception {
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
        $this->logFile = $logFile;

        // Log the exception
        $this->logException();
    }

    public function getContext() {
        return $this->context;
    }

    public function logException() {
        $logMessage = date('Y-m-d H:i:s') . " - Exception [{$this->code}]: {$this->message}\n";
        if (!empty($this->context)) {
            $logMessage .= "Context: " . json_encode($this->context) . "\n";
        }
        error_log($logMessage, 3, $this->logFile);
    }

    public function __toString() {
        $contextString = !empty($this->context) ? "\nContext: " . json_encode($this->context) : '';
        return "AdvancedCustomException [{$this->code}]: {$this->message}{$contextString}\n";
    }
}

// Example usage
try {
    // Simulate an error with some context information
    $user = ["id" => 123, "name" => "John"];
    if (!isset($user['email'])) {
        throw new AdvancedCustomException(
            "Email not found for user",
            404,
            null,
            ["user" => $user],
            'error.log'
        );
    }
} catch (AdvancedCustomException $e) {
    echo "Caught an exception: " . $e->getMessage();
    echo "\nCode: " . $e->getCode();
    echo "\nContext: " . json_encode($e->getContext());
}

