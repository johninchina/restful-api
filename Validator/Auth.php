<?php
namespace Validator;

use Grid\Http\Validator;
use Grid\Util\Json;
use Model\User;
use Grid\Exception\Unauthorized;

class Auth extends Validator
{
	protected $except = array();
	
	public function validate()
	{
		// $except中指定的资源不需要认证
		$rName = $this->getManager()->getRequest()->getResource();
		if (!in_array($rName, $this->except)) {
			// 验证HTTP_KEY是否有效
			if (!isset($_SERVER['HTTP_KEY']))
				throw new Unauthorized();
			$keygen = explode(',', base64_decode($_SERVER['HTTP_KEY']));
			list($username, $md5Password, $sessionId) = $keygen;
			$sessionPath = session_save_path() == '' ? '/SmartGrid/tmp' : session_save_path();
			$sessionFile = rtrim($sessionPath, '/') . '/sess_' . $sessionId;
			if (!file_exists($sessionFile)) {
				$user = User::model($this->getManager()->getComponent('db'));
				$where = "`username`='$username' AND `password`='$md5Password'";
				$userinfo = $user->where($where)->find();
				if ($userinfo == null)
					throw new Unauthorized();
				session_id($sessionId);
				session_start();
				$_SESSION['userid']		= $userinfo['id'];
				$_SESSION['username']	= $userinfo['username'];
				$_SESSION['userrole']	= $userinfo['userrole'];
				$_SESSION['authtype']	= $userinfo['authtype'];
				$_SESSION['permission']	= json_decode($userinfo['permission']);
			}
			session_id($sessionId);
			session_start();
		}
	}
}