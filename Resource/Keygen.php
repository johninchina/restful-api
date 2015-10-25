<?php
/*
 * 注册机
 * 通过发送用户名和密码获得key
 * 得到的key可用在curl中进行状态持久化
 */
namespace Resource;

use Grid\Http\Resource;
use Grid\Exception\BadRequest;
use Grid\Util\Json;
use Model\User;
use Model\Role;

class Keygen extends Resource
{
	public function actionPost()
	{
		$data = $this->getData('user');
		if (!isset($data['username']))
			throw new BadRequest("USERNAME_REQUIRED");
		if (!isset($data['password']))
			throw new BadRequest("PASSWORD_REQUIRED");
		$username = $data['username'];
		$md5Password = md5($data['password']);
		$user = User::model($this->getManager()->getComponent('db'));
		$where = "`username`='$username' AND `password`='$md5Password'";
		$userinfo = $user->where($where)->find();
		if ($userinfo != null) {
			session_start();
			$roleId	= $userinfo['role_id'];
			$role = Role::model($this->getManager()->getComponent('db'));
			$roleinfo = $role->where("`id`={$userinfo['role_id']}")->find();
			$_SESSION['userid']		= $userinfo['id'];
			$_SESSION['username']	= $userinfo['username'];
			$_SESSION['authtype']	= $userinfo['authtype'];
			$_SESSION['userrole']	= $roleinfo['name'];
			$_SESSION['permission']	= Json::toArray($roleinfo['permission']);
			return array(
				'result'	=> array(
					'success'	=> true,
					'key'		=> base64_encode($username . ',' . $md5Password . ',' . session_id())
				)
			);
		} else {
			throw new BadRequest('USERNAME_PASSWORD_INCORRECT');
		}
	}
}