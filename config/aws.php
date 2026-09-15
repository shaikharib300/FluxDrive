<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

use Aws\S3\S3Client;

function s3(): S3Client {
    static $client = null;
    if ($client instanceof S3Client) return $client;

    $args = [
        'version' => 'latest',
        'region' => envv('AWS_REGION'),
    ];
    $key = envv('AWS_ACCESS_KEY_ID');
    $secret = envv('AWS_SECRET_ACCESS_KEY');
    if ($key && $secret) {
        $args['credentials'] = ['key' => $key, 'secret' => $secret];
    }
    $client = new S3Client($args);
    return $client;
}

function bucket(): string {
    $name = envv('AWS_BUCKET_NAME');
    if (!$name) throw new RuntimeException('AWS_BUCKET_NAME is not configured.');
    return $name;
}
