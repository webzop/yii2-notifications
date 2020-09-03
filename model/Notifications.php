<?php

namespace webzop\notifications\model;

use Yii;

/**
 * This is the model class for table "{{%notifications}}".
 *
 * @property integer $id
 * @property string $class
 * @property string $key
 * @property string $message
 * @property string $route
 * @property integer $seen
 * @property integer $read
 * @property integer $user_id
 * @property integer $created_at
 */
class Notifications extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%notifications}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['class', 'key', 'message', 'route'], 'required'],
            [['seen', 'read', 'user_id', 'created_at'], 'integer'],
            [['class'], 'string', 'max' => 64],
            [['key'], 'string', 'max' => 32],
            [['message', 'route'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'class' => Yii::t('app', 'Class'),
            'key' => Yii::t('app', 'Key'),
            'message' => Yii::t('app', 'Message'),
            'route' => Yii::t('app', 'Route'),
            'seen' => Yii::t('app', 'Seen'),
            'read' => Yii::t('app', 'Read'),
            'user_id' => Yii::t('app', 'User ID'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }
}
