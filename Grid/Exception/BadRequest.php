<?php
namespace Grid\Exception;

use Grid\Http\Exception;

class BadRequest extends Exception
{
	protected $code = 400;
	protected $message = 'INVALID_ARGUMENTS';
}