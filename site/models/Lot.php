<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;
use Yii;
use yii\db\Query;
use yii\db\Expression;

/**
 * Элемент лота аукциона ...
 *
 * @property $id integer Номер лота
 * @property $name string(50) Наименование лота
 * @property $sale float Цена продажи
 * @property $dprice float Шаг цены от каждой заявки
 * @property $dtime integer Ожидание следующей ставки ..
 * @property $state string Статус состояния лота
 *
 */
class Lot extends ActiveRecord
{
    const STATE_NEW = 'new';
    const STATE_PLAY = 'play';
    const STATE_ENDED = 'ended';


    const STATES = [
        self::STATE_NEW => 'Новый',
        self::STATE_PLAY => 'Торгуется',
        self::STATE_ENDED => 'Торговля завершена',
    ];

    public static function tableName()
    {
        return '{{%lots}}';
    }

    public function rules()
    {
        return [
            ['name', 'trim'],
            ['name', 'string', 'max' => 50],
            ['dprice', 'double', 'min' => 0,],
            ['dtime', 'integer', 'min' => 1],
            [['name', 'dprice', 'dtime'], 'required'],
            ['state', 'in', 'range' => array_keys(static::STATES)],
            ['state', 'default', 'value' => static::STATE_NEW],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Наименование лота',
            'sale' => 'Цена продажи, р',
            'dprice' => 'Шаг цены, р',
            'dtime' => 'Время ожидания ставки, с',
            'state' => 'Состояние лота',
        ];
    }

    public function saveData($postData)
    {
        if ($postData && !$this->load($postData) || !$this->validate()) {
            return false;
        }

        return parent::save();
    }

    public static function list()
    {
        return new ActiveDataProvider([
            'query' => static::find(),
        ]);
    }

    public function gotoPlay()
    {
        if ($this->state == static::STATE_NEW) {
            $this->state = static::STATE_PLAY;
            $this->save();
        }
    }

    /**
     * делаем ставкку от бользователя
     * @param  $uid int Номер пользователя ..
     *
     * @return int число ставок в таблице ..
     */
    public function putStake($uid)
    {
        Yii::$app->db->createCommand()->insert('{{%stakes}}', ['lid' => $this->id, 'uid' => $uid])->execute();
        return Yii::$app->db->createCommand('select count(*) from {{%stakes}}' )->queryScalar();
    }

    /**
     * список лотов для странницы управления
     *
     * @return     ActiveDataProvider  The active data provider.
     */
    public static function playList()
    {
        $dp = new ActiveDataProvider([
            'query' => static::find()->where(['not', ['state' => static::STATE_NEW]])->asArray()->indexBy('id'),
        ]);
        $list = $dp->models;
        $ids = array_keys($list);
        // считаем текущую сумму по лотам ..
        $summPerLot = new Query();
        $summPerLot->from(['s' => '{{%stakes}}']);
        $summPerLot->select([
            'id' => 'lid',
            'sum' => new Expression('count(*)'),
        ])->where(['lid' => $ids])->groupBy('id')->indexBy('id');
        $summPerLot = $summPerLot->all();

        foreach ($list as $id => $item) {
            $list[$id]['curr'] = 0;
            if (isset($summPerLot[$id]['sum'])) {
                $list[$id]['curr'] = $summPerLot[$id]['sum'] * floatval($item['dprice']);
            }
        }
        $dp->models = $list;
        return $dp;
    }

    /**
     * забрать по каждому запрошенному лоту . число ставок, цену по ставке и время до окончания торгов
     */
    public static function getUpdData(array $ids = [])
    {
        $where = $ids ? ['l.id' => $ids] : ['l.state' => [static::STATE_PLAY, static::STATE_ENDED]];
        $select = [
            'l.id', 'l.dprice', 'l.dtime', 'l.name',
            'co' => new Expression('count(s.id)'),
            'lastdate' => new Expression('unix_timestamp(max(s.created))*1000'),
            'closed' => new Expression('l.state = :endstate', [':endstate' => static::STATE_ENDED]),
        ];
        $q = static::find()->alias('l')->asArray();
        $q->where($where)->select($select)->groupBy('l.id');
        $q->leftjoin(['s' => '{{%stakes}}'], 's.lid = l.id');

        return $q->all();
    }


    /**
     * закрываем торговлю ...для просроченных лотов ..
     */
    public static function closePlaing()
    {
        $q = static::find()->alias('l')->leftjoin(['s' => '{{%stakes}}'], 's.lid = l.id')->groupBy(['l.id', 'l.dtime']);
        $q->where(['l.state' => static::STATE_PLAY]);
        $q->having(new Expression('timestampdiff(second, max(s.created), now()) > l.dtime'));
        $q->select('l.id');
        $q->asArray();
        $listOutdated = $q->column();
        if ($listOutdated) {
            // Yii::info($listOutdated, '$listOutdated');
            Yii::$app->db->createCommand()->update('{{%lots}}', ['state' => static::STATE_ENDED], ['id' => $listOutdated])->execute();
        }

    }

}