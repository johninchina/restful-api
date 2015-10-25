<?php
namespace Grid\Exception;

use Grid\Http\Exception;

class NotFound extends Exception
{
	protected $code = 404;
	protected $message = 'NOT_FOUND';
}