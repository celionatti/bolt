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

use Exception;

class BoltException_wo extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
        
        // Log the error message to a file
        $this->logErrorToFile();
        
        // Display the error message on the screen
        $this->displayErrorOnScreen();
    }
    
    private function logErrorToFile() {
        $errorMessage = "[" . date("Y-m-d H:i:s") . "] " . $this->getMessage() . "\n";
        $basePath = get_root_dir() . DIRECTORY_SEPARATOR . "logs" . DIRECTORY_SEPARATOR;
        file_put_contents($basePath . 'error.log', $errorMessage, FILE_APPEND);
    }
    
    private function displayErrorOnScreen() {
        // You can customize the error display format here
        echo '<div style="background-color: #FF0000; color: #FFFFFF; padding: 10px;">';
        echo '<strong>Error:</strong> ' . $this->getMessage();
        echo '</div>';
    }
}
