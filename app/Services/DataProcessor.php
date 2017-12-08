<?php

namespace App\Services;

class DataProcessor
{
    public $dataPoints = [
        'analysis' => [
            'user'   => [],
            'left'   => [],
            'center' => [],
            'right'  => [],
        ],
        'mi' => [
            'user'   => [],
            'left'   => [],
            'center' => [],
            'right'  => [],
        ],
        'media' => [
            'user'    => 0,
            'follows' => [],
        ],
        'sentiment' => [
            'user'    => 0,
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
        $this->dataPoints['media']['user'] = $user['media'];
        $this->dataPoints['sentiment']['user'] = $user['sentiment'];
        $this->dataPoints['tweetCounts']['user'] = $user['tweet_count'];

        foreach ($follows as $follow) {
            $this->addDataValues($follow);
        }

        $totalTweets = $this->dataPoints['tweetCounts']['follows']['total'];
        $this->dataPoints['tweetCounts']['follows']['average'] = floor($totalTweets / $followCount);

        return true;
    }

    public function convertTo2D($values) {
        $result = [];

        foreach ($values as $value) {
            $result[] = [
                'x' => $value,
                'y' => (mt_rand(-100, 100) / 100),
            ];
        }

        return $result;
    }

    private function addDataValue($value, $listName) {
        if ($value < -1) {
            $this->dataPoints[$listName]['left'][] = $value;
        } elseif ($value > 1) {
            $this->dataPoints[$listName]['right'][] = $value;
        } else {
            $this->dataPoints[$listName]['center'][] = $value;
        }
    }

    private function addDataValues($values) {
        $this->addDataValue($values['analysis'], 'analysis');
        $this->addDataValue($values['mi'], 'mi');

        $this->dataPoints['media']['follows'][]              =  $values['media'];
        $this->dataPoints['sentiment']['follows'][]          =  $values['sentiment'];
        $this->dataPoints['tweetCounts']['follows']['total'] += $values['tweet_count'];
    }
}