<?php
require_once __DIR__.'/../includes/functions.php'; $u=require_auth(); $title='Starred — FluxDrive';
$st=db()->prepare('SELECT f.* FROM files f WHERE f.user_id=? AND f.is_deleted=0 AND f.is_starred=1 ORDER BY f.updated_at DESC'); $st->execute([$u['id']]); $rows=$st->fetchAll();
include __DIR__.'/../includes/header.php'; include __DIR__.'/../includes/sidebar.php'; ?>
<div class="app-shell"><div class="main"><?php include __DIR__.'/../includes/navbar.php';?><section class="page-wrap"><div class="page-heading"><div><span class="eyebrow">STARRED</span><h1>Starred</h1><p>Manage your starred items.</p></div></div><div class="panel"><div class="table-responsive"><table class="table align-middle file-table"><thead><tr><th>Name</th><th>Type</th><th>Size</th><th>Modified</th><th></th></tr></thead><tbody>
<?php if(!$rows):?><tr><td colspan="5"><div class="empty-state compact"><i class="bi bi-inbox"></i><h3>Nothing here yet</h3></div></td></tr><?php endif; ?>
<?php foreach($rows as $f):?><tr><td><div class="d-flex align-items-center gap-3"><div class="file-icon"><i class="bi <?=e(icon_for($f['mime_type']))?>"></i></div><div><strong><?=e($f['file_name'])?></strong><small class="d-block muted"><?=e(ext_of($f['file_name'])?:'file')?></small></div></div></td><td><?=e(type_bucket($f['mime_type']))?></td><td><?=e(human_bytes((int)$f['file_size']))?></td><td><?=e(date('M j, Y',strtotime($f['updated_at'])))?></td><td class="text-end"><div class="btn-group"><button class="icon-btn" onclick="fileMenu(<?=intval($f['id'])?>,<?=((int)$f['is_deleted']?1:0)?>)"><i class="bi bi-three-dots-vertical"></i></button></div></td></tr><?php endforeach;?>
</tbody></table></div></div></section></div></div>
<?php $scripts=['/FluxDrive/assets/js/files.js']; include __DIR__.'/../includes/footer.php';?>
