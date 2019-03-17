<?php
/**
 * Created by PhpStorm.
 * User: mtc-toolbox - http://mtc-toolbox.com
 * Date: 28.04.2017
 * Time: 8:00
 */

namespace mtcToolbox\yandexMap;

use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class YandexMaps extends Widget
{
    public $myPlacemarks;
    public $mapOptions;
    public $additionalOptions = ['searchControlProvider' => 'yandex#search'];

    public $pjaxIds = [];

    public $disableScroll   = true;

    public $windowWidth = '100%';
    public $windowHeight = '400px';

    public function init()    {
        parent::init();
        $this->myPlacemarks = ArrayHelper::toArray($this->myPlacemarks);
        $this->mapOptions = Json::encode($this->mapOptions);
        $this->additionalOptions = Json::encode($this->additionalOptions);
        $this->disableScroll = $this->disableScroll ? 1 : 0;
        $this->registerClientScript();
    }

    public function run()
    {

        //dd($this->id);
        return $this->render(
            'view',
            [
                'widget' => $this
            ]);
    }

    public function registerClientScript()
    {
        $countPlaces = count($this->myPlacemarks);
        $items  = [];
        $i      = 0;
        foreach ($this->myPlacemarks as $one) {
            $items[$i]['latitude']  = $one['latitude'];
            $items[$i]['longitude'] = $one['longitude'];
            $items[$i]['options'] = $one['options'];
            $i++;
        }

        $myPlacemarks = json_encode($items);
        $view = $this->getView();

        YandexMapsAsset::register($view);

        $js = <<< JS
        ymaps.ready(init_{$this->id});
            var myMap_{$this->id},
                myPlacemark_{$this->id};
        
            function init_{$this->id}(){
                myMap = new ymaps.Map("$this->id", {$this->mapOptions}, {$this->additionalOptions});
                
                var disableScroll_{$this->id} = $this->disableScroll;
                if ($this->disableScroll) {
                    myMap_{$this->id}.behaviors.disable('scrollZoom');                    
                }

                var myPlacemarks_{$this->id} = $myPlacemarks;        
        
                for (var i = 0; i < $countPlaces; i++) {
                    myPlacemark_{$this->id} = new ymaps.Placemark_{$this->id}([myPlacemarks_{$this->id}[i]['latitude'], myPlacemarks[i]_{$this->id}['longitude']],
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
        
        foreach ($this->pjaxIds as $pjaxId) {
            $js.= "
            $('#{$pjaxId}').on('pjax:success', function(xhr, textStatus, error, options) {
                     init_{$this->id}();           
            });
            ";

        }
        $view->registerJs($js);
    }
}
