<?php
namespace Grid\Exception;

use Grid\Http\Exception;

class Forbidden extends Exception
{
	protected $code = 403;
	protected $message = 'ACCESS_DENIED';
}