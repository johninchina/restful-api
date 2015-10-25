<?php
namespace Grid\Http;

class Exception extends \Exception
{
	protected $code = 500;
	protected $message = 'INTERNAL_ERROR';
	
	public function getBody()
	{
		return array(
			'result' => array(
				'success'	=> 'false',
				'message'	=> $this->message
			)
		);
	}
}