<?php

namespace app\controllers;

use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\Response;
use Yii;

use app\models\Lot;

class LotsController extends Controller
{
    public function behaviors()
    {
        return [
            ['class' => AccessControl::class, 'rules' => [
                ['allow' => true, 'roles' => ['@']],
            ]],
        ];
    }

    /**
     * спписок лотов ..
     *
     * @return     <type>  ( description_of_the_return_value )
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * редактирование лота ..
     *
     * @param      int  $lotId  номер лота
     *
     * @return     <type>  ( description_of_the_return_value )
     */
    public function actionEdit($lotId = null)
    {
        if ($lotId) {
            $lot = $this->getModel($lotId);
        } else {
            $lot = new Lot();
        }

        if ($this->request->isPost) {
            $post = $this->request->post();
            $ret = false;
            switch (true) {
                case isset($post['save']):
                    $ret = $lot->saveData($post);
                    break;
                case isset($post['kill']):
                    $ret = $lot->delete();
                    break;
            }
            if ($ret) {
                return $this->redirect(['index']);
            }

        }
        return $this->render('edit', [
            'model' => $lot,
        ]);
    }

    /**
     * добвление нового лота
     *
     * @return     <type>  ( description_of_the_return_value )
     */
    public function actionAdd()
    {
        return $this->actionEdit();
    }

    /**
     * Запуск торгов по лоту
     *
     * @param      <type>  $lotId Номер лота
     *
     * @return     bool    ( description_of_the_return_value )
     */
    public function actionStart($lotId)
    {
        $lot = $this->getModel($lotId);
        $lot->gotoPlay();

        return $this->redirect(['index']);
    }

    public function actionPlaig()
    {
        if (!$this->request->isPost) {
            // происвести закрытие торгов (в случае просрочки по ставке)
            return $this->render('plaig');
        }
        Lot::closePlaing();
        $this->response->format = Response::FORMAT_JSON;
        $post = $this->request->post();
        $limit = intval(getenv('request_terminate_timeout'));
        $sum = 0;
        switch ($post['action']) {
            case 'upd':
                $syncs = $this->request->post('syncs', 0);
                $syncsCache = Yii::$app->cache->get('stakes-size', 0);
                while ($syncs == $syncsCache) {
                    $sum += 2;
                    if ($sum == $limit) {
                        break;
                    }
                    sleep(2);
                    $syncsCache = Yii::$app->cache->get('stakes-size', 0);
                }
                return [
                    'ok' => true,
                    'data' => Lot::getUpdData(),
                ];

            case 'put-stake':
                $lot = Lot::findOne($post['on']);
                if ($lot) {
                    // добавляем ставку и сохраняем количество ставок в кеше
                    $countStakes = $lot->putStake(Yii::$app->user->id);
                    Yii::$app->cache->set('stakes-size', $countStakes);
                    return [
                        'ok' => true,
                        'data' => Lot::getUpdData([$lot->id])[0],
                        'countStakes' => $countStakes,
                    ];
                }


        }
        return ['ok' => true];
    }

    /**
     * ищем лот по номеру ..
     *
     * @param      int                          $id     Номер лота
     *
     * @throws     \yii\web\NotFoundHttpException  (description)
     */
    private function getModel($id)
    {
        $lot = Lot::findOne($id);
        if (!$lot) {
            throw new \yii\web\NotFoundHttpException("Лот не найден");
        }
        return $lot;
    }


}