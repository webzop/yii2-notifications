<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\LinkPager;

$this->title = Yii::t('modules/notifications', 'Notifications');

?>

<div class="page-header">
    <div class="buttons">
        <a class="btn btn-danger" href="<?= Url::toRoute(['/notifications/default/delete-all']) ?>"><?= Yii::t('modules/notifications', 'Delete all'); ?></a>
        <a class="btn btn-secondary" href="<?= Url::toRoute(['/notifications/default/read-all']) ?>"><?= Yii::t('modules/notifications', 'Mark all as read'); ?></a>
    </div>

    <h1>
        <span class="icon icon-bell"></span>
        <a href="<?= Url::to(['/notifications/manage']) ?>"><?= Yii::t('modules/notifications', 'Notifications') ?></a>
    </h1>
</div>

<div class="page-content">

    <ul id="notifications-items">
        <?php if($notifications): ?>
        <?php foreach($notifications as $notif): ?>
        <li class="notification-item<?php if($notif['read']): ?> read<?php endif; ?>" data-id="<?= $notif['id']; ?>" data-key="<?= $notif['key']; ?>">
            <a href="<?= $notif['url'] ?>">
                <span class="icon"></span>
                <span class="message"><?= Html::encode($notif['message']); ?></span>
            </a>
            <small class="timeago"><?= $notif['timeago']; ?></small>
            <span class="mark-read" data-toggle="tooltip" title="<?php if($notif['read']): ?><?php Yii::t('modules/notifications', 'Read') ?><?php else: ?><?= Yii::t('modules/notifications', 'Mark as read') ?><?php endif; ?>"></span>

        </li>
        <?php endforeach; ?>
        <?php else: ?>
            <li class="empty-row"><?= Yii::t('modules/notifications', 'There are no notifications to show') ?></li>
        <?php endif; ?>
    </ul>

    <?= LinkPager::widget(['pagination' => $pagination]); ?>

</div>
