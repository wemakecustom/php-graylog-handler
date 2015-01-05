<?php

/**
 * This function will be called before the script exits.
 * This allows us to catch and log any fatal errors in the Graylog2 server.
 * This is needed as the set_error_handler function cannot be used to handle
 * any of the errors in the array below.
 */
register_shutdown_function(function() use ($logger, $getContext) {
    $codes = array(
        1   => 'E_ERROR',
        4   => 'E_PARSE',
        16  => 'E_CORE_ERROR',
        32  => 'E_CORE_WARNING',
        64  => 'E_COMPILE_ERROR',
        128 => 'E_COMPILE_WARNING'
    );
    $error = error_get_last();

    if (!is_array($error) || !array_key_exists($error['type'], $codes)) return;

    $message = sprintf(
        'Error of type %s raised in file %s at line %d with message "%s"',
        $codes[$error['type']],
        $error['file'],
        $error['line'],
        $error['message']
    );

    ob_start();
    debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    $trace = ob_get_contents();
    ob_end_clean();

    $context = $getContext(array(
        'type'  => $codes[$error['type']],
        'file'  => $error['file'],
        'line'  => $error['line'],
        'trace' => $trace,
    ));

    if (in_array($error['type'], array(32, 128))) {
        //These errors are warnings and should be logged at a lower level.
        $logger->critical($message, $context);
    } else {
        $logger->alert($message, $context);
    }
});
