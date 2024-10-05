<?php

declare(strict_types=1);

/**
 * =================================
 * Bolt - Global Variables =========
 * =================================
 */

if (!defined('BOLT_ROOT')) {
    define('BOLT_ROOT', get_root_dir());
}

if (!defined('COOKIE_SECRET')) {
    define('COOKIE_SECRET', "");
}

const ALLOWED_IMAGE_FILE_UPLOAD = ['image/png', 'image/jpg', 'image/jpeg', 'image/gif', 'image/svg+xml', 'image/webp', 'image/x-icon'];

const ALLOWED_DOC_FILE_UPLOAD = ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'text/plain'];

const ALLOWED_ARCHIVE_FILE_UPLOAD = ['application/zip', 'application/x-tar', 'application/gzip', 'application/x-rar-compressed'];
const ALLOWED_FONT_FILE_UPLOAD = ['font/woff', 'font/woff2', 'application/x-font-ttf', 'application/x-font-opentype'];
const ALLOWED_VIDEO_FILE_UPLOAD = ['video/mp4', 'video/webm', 'video/ogg'];
const ALLOWED_AUDIO_FILE_UPLOAD = ['audio/mpeg', 'audio/ogg', 'audio/wav'];
