<?php

use yii\data\Pagination;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\LinkPager;

/**
 * @var array $notifications
 * @var Pagination $pagination
 */

$this->title = Yii::t('modules/notifications', 'Notifications');

?>

<div class="page-header">
    <div class="buttons">
        <a class="btn btn-danger" href="<?= Url::toRoute(['/notifications/default/delete-all']) ?>"><?= Yii::t('modules/notifications', 'Delete all'); ?></a>
        <a class="btn btn-secondary" href="<?= Url::toRoute(['/notifications/default/read-all']) ?>"><?= Yii::t('modules/notifications', 'Mark all as read'); ?></a>
    </div>
</div>

<div class="page-content notifications-list">

    <ul id="notifications-items">
        <?php if ($notifications) { ?>
            <?php foreach ($notifications as $notif) { ?>
                <li class="notification-item<?= $notif['read'] ? ' read' : '' ?>" data-id="<?= $notif['id']; ?>" data-key="<?= $notif['key']; ?>">
                    <a href="<?= $notif['url'] ?>">
                        <i class="fa fa-comment"></i>
                        <span class="message"><?= Html::encode($notif['message']); ?></span>
                    </a>
                    <small class="timeago"><?= $notif['timeago']; ?></small>
                    <span class="mark-read" data-toggle="tooltip" title="<?= $notif['read'] ? Yii::t('modules/notifications', 'Mark as unread') : Yii::t('modules/notifications', 'Mark as read') ?>"></span>
                </li>
            <?php } ?>
        <?php } else { ?>
            <li class="empty-row"><?= Yii::t('modules/notifications', 'There are no notifications to show') ?></li>
        <?php } ?>
    </ul>

    <?= LinkPager::widget(['pagination' => $pagination]); ?>

</div>
