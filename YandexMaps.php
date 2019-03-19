<?php
/**
 * Created by PhpStorm.
 * User: mtc-toolbox - http://mtc-toolbox.com
 * Date: 28.04.2017
 * Time: 8:00
 */

namespace mtcToolbox\yandexMap;

use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\View;

class YandexMaps extends Widget
{
    public $myPlacemarks;
    public $mapOptions;
    public $additionalOptions = ['searchControlProvider' => 'yandex#search'];

    public $pjaxIds = [];

    public $pjaxOnly = false;

    public $disableScroll = true;

    public $windowWidth  = '100%';
    public $windowHeight = '400px';

    public function init()
    {
        parent::init();
        $this->myPlacemarks      = ArrayHelper::toArray($this->myPlacemarks);
        $this->mapOptions        = Json::encode($this->mapOptions);
        $this->additionalOptions = Json::encode($this->additionalOptions);
        $this->disableScroll     = $this->disableScroll ? 1 : 0;
        $this->registerClientScript();
    }

    public function run()
    {

        //dd($this->id);
        return $this->render(
            'view',
            [
                'widget' => $this,
            ]);
    }

    public function registerClientScript()
    {
        /* @var yii\web\View $view */
        $view = $this->getView();

        $this->registerAssets();

        $js = $this->buildMainVarsJs();

        $js .= $this->assignMainVars($this->buildPoints());

        $js .= "ymaps.ready(init_{$this->id});";

        $js .= $this->buildCreateMap();

        $js .= $this->buildPjaxEvents();

        $view->registerJs($js);
    }

    protected function registerAssets()
    {
        /* @var yii\web\View $view */
        $view = $this->getView();

        YandexMapsAsset::register($view);
    }

    protected function getPointCount()
    {
        return count($this->myPlacemarks);
    }

    protected function buildPoints()
    {

        $items = [];
        $i     = 0;
        foreach ($this->myPlacemarks as $one) {
            $items[$i]['latitude']  = $one['latitude'];
            $items[$i]['longitude'] = $one['longitude'];
            $items[$i]['options']   = $one['options'];
            $i++;
        }

        return json_encode($items);

    }

    protected function buildMainVarsJs()
    {
        $js = <<< JS
          var myMap_{$this->id},
                myPlacemark_{$this->id},
                myPlacemarks_{$this->id},
                disableScroll_{$this->id};  

JS;

        return $js;
    }

    protected function assignMainVars($placemarks)
    {
        $js = <<< JS
                disableScroll_{$this->id} = $this->disableScroll;
                myPlacemarks_{$this->id} = $placemarks;

JS;

        return $js;
    }

    protected function buildCreateMap()
    {
        $countPlaces = $this->getPointCount();
        $endJS       = <<< JS
            function init_{$this->id}(){
                myMap_{$this->id} = new ymaps.Map("$this->id", {$this->mapOptions}, {$this->additionalOptions});
                
                if (disableScroll_{$this->id}) {
                    myMap_{$this->id}.behaviors.disable('scrollZoom');                    
                }
        
                for (let i = 0; i < $countPlaces; i++) {
                    myPlacemark_{$this->id} = new ymaps.Placemark([myPlacemarks_{$this->id}[i]['latitude'], myPlacemarks_{$this->id}[i]['longitude']],
                    myPlacemarks_{$this->id}[i]['options'][0],
                    myPlacemarks_{$this->id}[i]['options'][1],
                    myPlacemarks_{$this->id}[i]['options'][2],
                    myPlacemarks_{$this->id}[i]['options'][3],
                    myPlacemarks_{$this->id}[i]['options'][4],
                    myPlacemarks_{$this->id}[i]['options'][5]
                    );
                
                    myMap_{$this->id}.geoObjects.add(myPlacemark_{$this->id});
                }
            }
JS;
        return $endJS;
    }

    protected function buildPjaxEvents()
    {
        $js = '';
        foreach ($this->pjaxIds as $pjaxId) {
            $js .= "
            $('#{$pjaxId}').on('pjax:success', function(xhr, textStatus, error, options) {
                    alert(222);
                     init_{$this->id}();           
            });
            ";
        }
        return $js;
    }
}


