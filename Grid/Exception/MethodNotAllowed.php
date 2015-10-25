<?php
namespace Grid\Exception;

use Grid\Http\Exception;

class MethodNotAllowed extends Exception
{
	protected $code = 405;
	protected $message = 'METHOD_DENIED';
}