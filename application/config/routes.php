<?php
/**
 * 파일 위치: application/config/routes.php
 * 역할: URI 라우팅 설정
 */
defined('BASEPATH') OR exit('No direct script access allowed');

// 기본 컨트롤러를 로그인 페이지로 설정
$route['default_controller'] = 'login';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// 로그인 관련 라우트
$route['login'] = 'login/index';
$route['login/google'] = 'login/google_login';
$route['login/callback'] = 'login/google_callback';
$route['logout'] = 'login/logout';

// 대시보드 라우트
$route['dashboard'] = 'dashboard/index';
