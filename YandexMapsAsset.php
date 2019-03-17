<?php
/**
 * Created by PhpStorm.
 * User: mtcToolbox - http://mtcToolbox.com
 * Date: 30.06.2016
 * Time: 22:24
 */

namespace mtcToolbox\yandexMap;

use yii\web\AssetBundle;

class YandexMapsAsset extends AssetBundle
{
    public $js = [
        'https://api-maps.yandex.ru/2.1/?lang=ru_RU'
    ];
}