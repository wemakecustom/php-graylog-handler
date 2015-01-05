<?php

/**
 * Set a custom error handler to make sure that errors are logged to Graylog.
 * Allows any non-fatal errors to be logged to the Graylog2 server.
 */
set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) use ($logger, $getContext) {
    switch ($errno) {
        case E_USER_ERROR:
            $level = 'error';
            $type  = 'E_USER_ERROR';
            break;
        case E_USER_WARNING:
            $level = 'warning';
            $type  = 'E_USER_WARNING';
            break;
        case E_USER_NOTICE:
            $level = 'notice';
            $type  = 'E_USER_NOTICE';
            break;
        case E_STRICT:
            $level = 'debug';
            $type  = 'E_STRICT';
            break;
        case E_RECOVERABLE_ERROR:
            $level = 'error';
            $type  = 'E_RECOVERABLE_ERROR';
            break;
        case E_DEPRECATED:
            $level = 'debug';
            $type  = 'E_DEPRECATED';
            break;
        case E_USER_DEPRECATED:
            $level = 'debug';
            $type  = 'E_USER_DEPRECATED';
            break;
        case E_NOTICE:
            $level = 'notice';
            $type  = 'E_NOTICE';
            break;
        case E_WARNING:
            $level = 'warning';
            $type  = 'E_WARNING';
            break;
        default:
            $level = 'error';
            $type  = "UNKNOWN[${errno}]";
    }

    $message = sprintf(
        '%s: %s in %s on line %d',
        $type,
        $errstr,
        $errfile,
        $errline
    );

    ob_start();
    debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    $trace = ob_get_contents();
    ob_end_clean();
    
    $context = $getContext(array(
        'type'  => $type,
        'file'  => $errfile,
        'line'  => $errline,
        'trace' => $trace,
    ));
    $logger->log($level, $message, $context);

    return false; // Returning false will mean that PHP's error handling mechanism will not be bypassed.
});
