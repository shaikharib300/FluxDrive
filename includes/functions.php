<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/aws.php';

function e(?string $v): string { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
function json_response(array $data, int $status=200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}
function request_json(): array {
    $raw=file_get_contents('php://input');
    $data=json_decode($raw ?: '{}', true);
    return is_array($data) ? $data : [];
}
function human_bytes(int $bytes): string {
    if ($bytes < 1024) return $bytes.' B';
    $units=['KB','MB','GB','TB'];
    $v=$bytes;
    foreach ($units as $u) { $v/=1024; if ($v < 1024) return number_format($v,1).' '.$u; }
    return number_format($v,1).' PB';
}
function ext_of(string $name): string { return strtolower(pathinfo($name, PATHINFO_EXTENSION)); }
function type_bucket(string $mime): string {
    if (str_starts_with($mime,'image/')) return 'Images';
    if (str_starts_with($mime,'video/')) return 'Videos';
    if (str_starts_with($mime,'audio/')) return 'Audio';
    if (str_contains($mime,'pdf') || preg_match('/word|spreadsheet|excel|presentation|text\//i',$mime)) return 'Documents';
    if (preg_match('/zip|rar|7z|gzip|tar/i',$mime)) return 'Archives';
    return 'Other';
}
function icon_for(string $mime): string {
    return match(true) {
        str_starts_with($mime,'image/')=>'bi-file-earmark-image',
        str_starts_with($mime,'video/')=>'bi-file-earmark-play',
        str_starts_with($mime,'audio/')=>'bi-file-earmark-music',
        str_contains($mime,'pdf')=>'bi-file-earmark-pdf',
        str_contains($mime,'word')=>'bi-file-earmark-word',
        str_contains($mime,'excel'), str_contains($mime,'spreadsheet')=>'bi-file-earmark-excel',
        str_contains($mime,'presentation')=>'bi-file-earmark-ppt',
        str_contains($mime,'zip') || str_contains($mime,'rar')=>'bi-file-earmark-zip',
        default=>'bi-file-earmark',
    };
}
function owned_file(int $id, int $uid, bool $includeDeleted=false): array {
    $sql='SELECT * FROM files WHERE id=? AND user_id=?';
    if (!$includeDeleted) $sql.=' AND is_deleted=0';
    $st=db()->prepare($sql.' LIMIT 1'); $st->execute([$id,$uid]);
    $row=$st->fetch();
    if (!$row) json_response(['error'=>'File not found.'],404);
    return $row;
}
function log_activity(int $uid,string $action,?int $fileId,?int $folderId,string $description): void {
    $st=db()->prepare('INSERT INTO activities(user_id,action,file_id,folder_id,description) VALUES(?,?,?,?,?)');
    $st->execute([$uid,$action,$fileId,$folderId,$description]);
}
function storage_stats(int $uid): array {
    $st=db()->prepare("SELECT COALESCE(SUM(file_size),0) used, COUNT(*) files FROM files WHERE user_id=? AND is_deleted=0");
    $st->execute([$uid]); $r=$st->fetch();
    $used=(int)$r['used']; $total=(int)envv('STORAGE_QUOTA_BYTES','10737418240');
    return ['used'=>$used,'total'=>$total,'free'=>max(0,$total-$used),'files'=>(int)$r['files'],'percent'=>$total?min(100,($used/$total)*100):0];
}
function file_type_stats(int $uid): array {
    $out=['Documents'=>0,'Images'=>0,'Videos'=>0,'Audio'=>0,'Archives'=>0,'Other'=>0];
    $st=db()->prepare('SELECT mime_type,SUM(file_size) total FROM files WHERE user_id=? AND is_deleted=0 GROUP BY mime_type'); $st->execute([$uid]);
    foreach($st as $r) { $out[type_bucket($r['mime_type'])]+=(int)$r['total']; }
    return $out;
}
function flash(string $type,string $message): void { $_SESSION['_flash']=['type'=>$type,'message'=>$message]; }
function pull_flash(): ?array { $f=$_SESSION['_flash']??null; unset($_SESSION['_flash']); return $f; }

function ensure_user_root(int $uid): void {
    $st=db()->prepare('SELECT id FROM folders WHERE user_id=? AND parent_id IS NULL LIMIT 1'); $st->execute([$uid]);
    if (!$st->fetch()) {
        $ins=db()->prepare('INSERT INTO folders(user_id,parent_id,folder_name) VALUES(?,?,?)');
        $ins->execute([$uid,null,'My Files']);
    }
}
function root_folder_id(int $uid): int {
    $st=db()->prepare('SELECT id FROM folders WHERE user_id=? AND parent_id IS NULL LIMIT 1'); $st->execute([$uid]);
    return (int)$st->fetchColumn();
}
function breadcrumb(int $folderId,int $uid): array {
    $items=[]; $guard=0;
    while($folderId && $guard++<50) {
        $st=db()->prepare('SELECT * FROM folders WHERE id=? AND user_id=?'); $st->execute([$folderId,$uid]); $f=$st->fetch();
        if(!$f) break; array_unshift($items,$f); $folderId=(int)$f['parent_id'];
    }
    return $items;
}
function create_presigned_url(string $key, int $minutes=10): string {
    $cmd=s3()->getCommand('GetObject',['Bucket'=>bucket(),'Key'=>$key]);
    return (string)s3()->createPresignedRequest($cmd,'+' . $minutes . ' minutes')->getUri();
}
function safe_share_token(): string { return rtrim(strtr(base64_encode(random_bytes(32)), '+/','-_'),'='); }
