<?php
// Usage expectations before including this partial:
// set $page_icon (e.g. 'fas fa-book'), $page_title (string),
// optional: $show_actions (bool), $action_buttons (array of ['label'=>..., 'class'=>..., 'onclick'=>...])
// optional: $add_button (array with 'label' and 'onclick')
$icon = $page_icon ?? 'fas fa-circle';
$title = $page_title ?? 'Page';
$show_actions = $show_actions ?? true;
$action_buttons = $action_buttons ?? [];
$add_button = $add_button ?? null;
?>
<div class="breadcrumb-container d-flex justify-content-between align-items-center">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php?page=dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page"><i class="<?= htmlspecialchars($icon) ?>"></i> <?= htmlspecialchars($title) ?></li>
        </ol>
    </nav>
    <div class="d-flex align-items-center gap-2">
        <?php if ($show_actions): ?>
            <div class="action-buttons d-none d-md-flex">
                <?php foreach ($action_buttons as $btn): ?>
                        <button <?php if (!empty($btn['id'])): ?>id="<?= htmlspecialchars($btn['id']) ?>"<?php endif; ?> class="btn <?= htmlspecialchars($btn['class'] ?? 'btn-primary') ?> btn-action" onclick="<?= $btn['onclick'] ?? '' ?>">
                            <i class="<?= htmlspecialchars($btn['icon'] ?? 'fas fa-file') ?>"></i> <?= htmlspecialchars($btn['label'] ?? '') ?>
                        </button>
                    <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if ($add_button): ?>
            <button class="btn btn-primary btn-action" onclick="<?= $add_button['onclick'] ?>">
                <i class="fas fa-plus"></i> <?= htmlspecialchars($add_button['label']) ?>
            </button>
        <?php endif; ?>
    </div>
</div>
