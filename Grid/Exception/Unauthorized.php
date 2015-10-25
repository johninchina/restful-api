<?php
namespace Grid\Exception;

use Grid\Http\Exception;

class Unauthorized extends Exception
{
    protected $code = 401;
    protected $message = 'UNAUTHORIZED';
}
