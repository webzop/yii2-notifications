<?php
use yii\helpers\Url;
use yii\widgets\LinkPager;

$this->title = Yii::t('app', 'Notifications');

?>

<div class="page-header">
    <div class="buttons">
        <a class="btn btn-danger" href="<?= Url::toRoute(['/notifications/default/delete-all']) ?>"><?= Yii::t('app', 'Delete all'); ?></a>
        <a class="btn btn-secondary" href="<?= Url::toRoute(['/notifications/default/read-all']) ?>"><?= Yii::t('app', 'Mark all as read'); ?></a>
    </div>

    <h1>
        <span class="icon icon-bell"></span>
        <a href="<?= Url::to(['/notifications/manage']) ?>"><?= Yii::t('app', 'Notifications') ?></a>
    </h1>
</div>

<div class="page-content">

    <ul id="notifications-items">
        <? if($notifications): ?>

        <? foreach($notifications as $notif): ?>
        <li class="notification-item<? if($notif['read']): ?> read<? endif; ?>" data-id="<?= $notif['id']; ?>" data-key="<?= $notif['key']; ?>">
            <a href="<?= $notif['url'] ?>">
                <span class="icon"></span>
                <span class="message"><?= $notif['message']; ?></span>
            </a>
            <small class="timeago"><?= $notif['timeago']; ?></small>
            <span class="mark-read" data-toggle="tooltip" title="<? if($notif['read']): ?><?= Yii::t('app', 'Read') ?><? else: ?><?= Yii::t('app', 'Mark as read') ?><? endif; ?>"></span>

        </li>
        <? endforeach; ?>
        <? else: ?>
            <li class="empty-row"><?= Yii::t('app', 'There are no notifications to show') ?></li>
        <? endif; ?>
    </ul>

    <?= LinkPager::widget(['pagination' => $pagination]); ?>

</div>
