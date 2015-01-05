<?php

/**
 * Create a closure to handle uncaught exceptions
 */
set_exception_handler($handler = function(Exception $e) use (&$handler, $logger, $getContext) {
    $message = sprintf(
        'Uncaught exception of type %s thrown in file %s at line %s%s.',
        get_class($e),
        $e->getFile(),
        $e->getLine(),
        $e->getMessage() ? sprintf(' with message "%s"', $e->getMessage()) : ''
    );

    $context = $getContext(array(
        'type'  => get_class($e),
        'file'  => $e->getFile(),
        'line'  => $e->getLine(),
        'trace' => $e->getTraceAsString(),
    ));

    $logger->error($message, $context);

    /**
     * If there was a previous nested exception call this function recursively
     * to log that too.
     */
    if ($prev = $e->getPrevious()) {
        $handler($prev);
    }
});

unset($handler);
