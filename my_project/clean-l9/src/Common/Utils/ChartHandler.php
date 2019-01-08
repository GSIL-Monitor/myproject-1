<?php

namespace Common\Utils;

use Ob\HighchartsBundle\Highcharts\Highchart;

class ChartHandler
{

    static public function getLineChart($title, $xTitle, $yTitle, $name, $categoriesArr, $dataArr, $elementId)
    {
        $series = array(
                array(
                        "name" => $name,
                        "data" => array_values($dataArr) 
                ) 
        );
        $ob = new Highchart();
        $ob->chart->renderTo($elementId); // The #id of the div where to render the chart
        $ob->title->text($title);
        $ob->xAxis->title(array(
                'text' => $xTitle 
        ));
        $ob->xAxis->categories($categoriesArr);
        $ob->yAxis->title(array(
                'text' => $yTitle 
        ));
        $ob->plotOptions->line(array(
                'allowPointSelect' => true,
                'cursor' => 'pointer',
                'dataLabels' => array(
                        'enabled' => true 
                ) 
        ));
        $ob->series($series);
        
        return $ob;
    }

    static public function getPieChart($title, $name, $dataArr, $elementId)
    {
        $ob = new Highchart();
        $ob->chart->renderTo($elementId);
        $ob->title->text($title);
        $ob->plotOptions->pie(array(
                'allowPointSelect' => true,
                'cursor' => 'pointer',
                'dataLabels' => array(
                        'enabled' => true,
                        "format" => '<b>{point.name}</b>: {point.percentage:.1f} %' 
                ),
                'showInLegend' => true 
        ));
        $ob->series(array(
                array(
                        'type' => 'pie',
                        'name' => $name,
                        'data' => $dataArr 
                ) 
        ));
        return $ob;
    }

    static public function getColumnChart($title, $yTitle, $name, $dataArr, $elementId)
    {
        $ob = new Highchart();
        $ob->chart->renderTo($elementId);
        $ob->title->text($title);
        $ob->xAxis->type('category');
        $ob->xAxis->labels(array(
                'rotation' => - 45,
                'style' => array(
                        'fontSize' => '13px',
                        'fontFamily' => 'Verdana, sans-serif' 
                ) 
        ));
        $ob->yAxis->title(array(
                'text' => $yTitle 
        ));
        $ob->plotOptions->column(array(
                'allowPointSelect' => true,
                'cursor' => 'pointer',
                'dataLabels' => array(
                        'enabled' => true,
                        // 'rotation'=> -90,
                        // 'color'=> '#FFFFFF',
                        // 'align'=> 'right',
                        // 'x'=> 0,
                        // 'y'=> 0,
                        'style' => array(
                                'fontSize' => '13px',
                                'fontFamily' => 'Verdana, sans-serif' 
                        )
                        // 'textShadow'=> '0 0 3px black'
                         
                ),
                'showInLegend' => true 
        ));
        $ob->series(array(
                array(
                        'type' => 'column',
                        'name' => $name,
                        'data' => $dataArr 
                ) 
        ));
        return $ob;
    }
}

?>