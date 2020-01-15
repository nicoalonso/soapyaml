<?php

namespace Lacedorium\SoapYaml\Exception;

/**
 * Exception class
 */
class LoadException extends \RuntimeException
{
    /**
     * Throw exception
     * 
     * @param  string  $message
     * @param  integer $code
     * @param  Throwable  $previous
     * 
     * @throws LoadException
     */
    public static function throw($message = '', $code = 0, $previous = null)
    {
        throw new self($message, $code, $previous);
    }   
}