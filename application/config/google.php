<?php
/**
 * 파일 위치: application/config/google.php
 * 역할: 구글 OAuth 인증 설정
 *
 * 설정 방법:
 * 1. Google Cloud Console (https://console.cloud.google.com) 접속
 * 2. 프로젝트 생성 또는 선택
 * 3. API 및 서비스 > 사용자 인증 정보 > OAuth 2.0 클라이언트 ID 생성
 * 4. 승인된 리디렉션 URI에 redirect_uri 추가
 */
defined('BASEPATH') OR exit('No direct script access allowed');

// 구글 OAuth 클라이언트 ID
$config['google_client_id'] = '380691274627-pqc50hdco6ct7lkuqq3m8rq2rn021r64.apps.googleusercontent.com';

// 구글 OAuth 클라이언트 시크릿
$config['google_client_secret'] = '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';

// 구글 OAuth 콜백 URL
$config['google_redirect_uri'] = 'https://unesco.webhows.com/login/google_callback';
