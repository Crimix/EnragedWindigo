<?php

namespace App\Services;

class DataProcessor
{
    const MIN_VAL = -10;
    const MAX_VAL = 10;

    private $dataPoints = [
        'analysis' => [
            'user'    => [],
            'follows' => [],
            'left'    => [],
            'center'  => [],
            'right'   => [],
        ],
        'mi' => [
            'user'    => [],
            'follows' => [],
            'left'    => [],
            'center'  => [],
            'right'   => [],
        ],
        'media' => [
            'user'    => [],
            'follows' => [],
        ],
        'sentiment' => [
            'user'    => [],
            'follows' => [],
        ],
        'tweetCounts' => [
            'user'    => 0,
            'follows' => [
                'total'   => 0,
                'average' => 0,
            ],
        ],
    ];

    /**
     * 
     */
    public function prepareData($dataArray)
    {
        if (!is_array($dataArray) || empty($dataArray['user']) || empty($dataArray['follows'])) {
            return false;
        }

        $user        = $dataArray['user'];
        $follows     = $dataArray['follows'];
        $followCount = count($follows);

        $this->dataPoints['analysis']['user'] = [$user['analysis']];
        $this->dataPoints['mi']['user'] = [$user['mi']];
        $this->dataPoints['media']['user'] = [$user['media']];
        $this->dataPoints['sentiment']['user'] = [$user['sentiment']];
        $this->dataPoints['tweetCounts']['user'] = $user['tweet_count'];

        foreach ($follows as $follow) {
            $this->addDataValues($follow);
        }

        $totalTweets = $this->dataPoints['tweetCounts']['follows']['total'];
        $this->dataPoints['tweetCounts']['follows']['average'] = floor($totalTweets / $followCount);

        // Sort some of the arrays
        sort($this->dataPoints['analysis']['follows']);
        sort($this->dataPoints['mi']['follows']);
        sort($this->dataPoints['media']['follows']);
        sort($this->dataPoints['sentiment']['follows']);
        
        return true;
    }

    /**
     * 
     */
    public function getScatterData($dataArea, $subArea)
    {
        if (!$this->validateAreas($dataArea, $subArea)) {
            return [];
        }

        return $this->convertTo2D($this->dataPoints[$dataArea][$subArea]);
    }

    /**
     * 
     */
    public function getBarData($dataArea, $bracketCount = 10)
    {
        if (!$this->validateArea($dataArea) || $bracketCount < 1) {
            return [];
        }

        $userVal  = $this->dataPoints[$dataArea]['user'][0];
        $data     = $this->dataPoints[$dataArea]['follows'];
        $dataSize = count($data);
        $interval = (self::MAX_VAL - self::MIN_VAL) / $bracketCount;
        $result   = [];
        $curIndex = 0;

        for ($i = 0; $i < $bracketCount; $i++) {
            $bracketStart = self::MIN_VAL + ($i * $interval);
            $bracketEnd = $bracketStart + $interval;
            $entries = [];

            while ($curIndex < $dataSize && $data[$curIndex] >= $bracketStart && $data[$curIndex] < $bracketEnd) {
                $entries[] = $data[$curIndex];
                $curIndex++;
            }

            $result[$i] = [
                'begin' => $bracketStart,
                'end' => $bracketEnd,
                'hasUser' => ($userVal >= $bracketStart && $userVal < $bracketEnd),
                'entries' => $entries,
            ];
        }

        return $result;
    }

    public function getChartJsScatterData($dataArea)
    {
        $result = [
            'labels' => ['Some Label'],
            'datasets' => [
                [
                    'label' => 'User',
                    'pointBorderColor' => 'rgba(38, 185, 154, 0.7)',
                    'pointBackgroundColor' => 'rgba(38, 185, 154, 0.7)',
                    'pointHoverBackgroundColor' => '#fff',
                    'pointHoverBorderColor' => 'rgba(220,220,220,1)',
                    'data' => $this->getScatterData($dataArea, 'user'),
                ],
                [
                    'label' => 'Left',
                    'pointBorderColor' => 'rgba(80, 80, 220, 0.7)',
                    'pointBackgroundColor' => 'rgba(80, 80, 220, 0.7)',
                    'pointHoverBackgroundColor' => '#fff',
                    'pointHoverBorderColor' => 'rgba(220,220,220,1)',
                    'data' => $this->getScatterData($dataArea, 'left'),
                ],
                [
                    'label' => 'Center',
                    'data' => $this->getScatterData($dataArea, 'center'),
                ],
                [
                    'label' => 'Right',
                    'pointBorderColor' => 'rgba(220, 80, 80, 0.7)',
                    'pointBackgroundColor' => 'rgba(220, 80, 80, 0.7)',
                    'pointHoverBackgroundColor' => '#fff',
                    'pointHoverBorderColor' => 'rgba(220,220,220,1)',
                    'data' => $this->getScatterData($dataArea, 'right'),
                ],
            ],
        ];

        return $result;
    }

    /**
     * 
     */
    public function getChartJsBarData($dataArea, $bracketCount = 10)
    {
        $data = $this->getBarData($dataArea, $bracketCount);

        if (count($data) < 1) {
            return [];
        }

        $result = [
            'labels' => [],
            'dataset' => [
                'label'           => 'This dataset',
                'fill'            => true,
                'borderWidth'     => 2,
                'data'            => [],
                'backgroundColor' => [],
                'borderColor'     => [],
            ],
        ];

        foreach ($data as $i => $entry) {
            if ($entry['hasUser']) {
                $colour = [38, 220, 38];
            } else {
                $colour = [80, 80, 80];

                if ($entry['end'] < -1) {
                    $multp = (($entry['begin'] + $entry['end']) / 2) / self::MIN_VAL;
                    $colour = [floor(60 * $multp), floor(60 * $multp), floor(220 * $multp)];
                } elseif ($entry['begin'] > 1) {
                    $multp = (($entry['begin'] + $entry['end']) / 2) / self::MAX_VAL;
                    $colour = [floor(220 * $multp), floor(60 * $multp), floor(60 * $multp)];
                }
            }

            $result['labels'][$i]                   = sprintf('%01.2f - %01.2f', $entry['begin'], $entry['end']);
            $result['dataset']['data'][]            = count($entry['entries']);
            $result['dataset']['backgroundColor'][] = "rgba({$colour[0]}, {$colour[1]}, {$colour[2]}, 0.4)";
            $result['dataset']['borderColor'][]     = "rgba({$colour[0]}, {$colour[1]}, {$colour[2]}, 0.8)";
        }

        $result['datasets'] = [$result['dataset']];
        unset($result['dataset']);

        return $result;
    }


    /**
     * 
     */
    private function validateAreas($dataArea, $subArea) {
        return ($this->validateArea($dataArea) && is_array($this->dataPoints[$dataArea][$subArea]));
    }

    /**
     * 
     */
    private function validateArea($dataArea) {
        return (is_array($this->dataPoints[$dataArea]));
    }
    
    /**
     * 
     */
    private function convertTo2D($values)
    {
        $result = [];

        foreach ($values as $value) {
            $result[] = [
                'x' => $value,
                'y' => (mt_rand(0, 100) / 100),
            ];
        }

        return $result;
    }

    /**
     * 
     */
    private function addDataValue($value, $listName)
    {
        $this->dataPoints[$listName]['follows'][] = $value;

        if ($value < -1) {
            $this->dataPoints[$listName]['left'][] = $value;
        } elseif ($value > 1) {
            $this->dataPoints[$listName]['right'][] = $value;
        } else {
            $this->dataPoints[$listName]['center'][] = $value;
        }
    }

    /**
     * 
     */
    private function addDataValues($values)
    {
        $this->addDataValue($values['analysis'], 'analysis');
        $this->addDataValue($values['mi'], 'mi');

        $this->dataPoints['media']['follows'][]              =  $values['media'];
        $this->dataPoints['sentiment']['follows'][]          =  $values['sentiment'];
        $this->dataPoints['tweetCounts']['follows']['total'] += $values['tweet_count'];
    }
}