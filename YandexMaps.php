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

    public $inputDataId;

    public $disableScroll = true;

    // отступы объектов
    public $mapMargin = 50;

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

        $js .= $this->assignMainVars($this->buildJSPoints());

        $js .= "ymaps.ready(init_{$this->id});";

        $js .= $this->buildCreateMap();

        $js .= $this->buildPjaxEvents();

        $view->registerJs($js);
    }

    public static function buildPoints(array $myPlacemarks)
    {

        $items = [];
        foreach ($myPlacemarks as $one) {
            $item              = [];
            $item['latitude']  = $one['latitude'];
            $item['longitude'] = $one['longitude'];
            $item['options']   = $one['options'];
            $item['latitude']  = $one['latitude'];
            $item['longitude'] = $one['longitude'];
            $item['options']   = $one['options'];
            $items[]           = $item;
        }

        return json_encode($items);
    }

    public static function buildValuePoints($myPlacemarks)
    {
        return urlencode(self::buildPoints($myPlacemarks));
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

    protected function buildJSPoints()
    {
        return self::buildPoints($this->myPlacemarks);
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

    protected function assignMainVars(
        $placemarks
    ) {
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
            function loadData_{$this->id}(){
                if (disableScroll_{$this->id}) {
                    myMap_{$this->id}.behaviors.disable('scrollZoom');                    
                }
       
                for (let i = 0; i < myPlacemarks_{$this->id}.length; i++) {
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
                
                if (myPlacemarks_{$this->id}.length) {
                  myMap_{$this->id}.setBounds(myMap_{$this->id}.geoObjects.getBounds());
                  myMap_{$this->id}.margin.setDefaultMargin($this->mapMargin);
                  let maxZoom = myMap_{$this->id}.getZoom();
                  
                  if (maxZoom > 19) {
                    maxZoom = 19;
                  }
                  myMap_{$this->id}.setZoom(maxZoom);
                }
            }            
            function init_{$this->id}(){
                myMap_{$this->id} = new ymaps.Map("$this->id", {$this->mapOptions}, {$this->additionalOptions});
                loadData_{$this->id}();

                
            }
JS;

        return $endJS;
    }

    protected function buildPjaxEvents()
    {
        $js = '';
        $needParseJson = isset($this->inputDataId);
        foreach ($this->pjaxIds as $pjaxId) {
            $js .= "
            $('#{$pjaxId}').on('pjax:success', function(xhr, textStatus, error, options) {
                    if ($needParseJson) {
                        let jsonData = decodeURIComponent(($('#{$this->inputDataId}').val()+'').replace(/\+/g, '%20'));
                        myPlacemarks_{$this->id} = JSON.parse(jsonData);
                    }
                     loadData_{$this->id}();           
            });
            ";
        }

        return $js;
    }
}


