<?php
// 设置页面执行时间
set_time_limit(0);
// 设置错误报告级别
error_reporting(0);
// 关闭错误显示
ini_set('display_errors', 'Off');
// 可以单独创建config.php，并通过require或include加载
$config = array(
	'component'	=> array(
		'db'	=> array(
			'class'		=> 'Grid\Db\DbFactory',
			'options'	=> array(
				'driver'	=> 'mysql',
				'host'		=> 'localhost',
				'port'		=> '3306',
				'username'	=> 'root',
				'password'	=> '',
				'database'	=> '',
				'charset'	=> 'utf8'
			)
		)
	),
	'resource'	=> array(
		'aliases'	=> array(
			// 别名映射，防止有些资源名为关键字或保留字
			'Interface'	=> 'Interfaces'
		)
	),
	'validator'	=> array(
		'Auth'	=> array(
			// 不需要认证的资源
			'except'	=> array(
				'Keygen',
				'Download',
			)
		),
		'Rbac'	=> array(
			// 不需要权限判断的资源
			'except'	=> array(
				'Keygen'
			),
			// 资源名和权限名的对应关系
			'mapping'	=> array(
				'Ad'	=> 'auth',
			)
		)
	)
);
include 'Grid/Application.php';
Grid\Application::init($config)->run();