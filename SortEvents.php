<?php

class EventSort
{
    private $team1;
    private $events1;
    private $team2;
    private $events2;

    public function __construct($team1, array $events1, $team2, array $events2)
    {
        $this->team1 = $team1;
        $this->events1 = $events1;
        $this->team2 = $team2;
        $this->events2 = $events2;
    }

    public function getEventsOrder()
    {
        $result = [];

        $firstTeam = $this->extractEvents($this->events1, $this->team1);
        $secondTeam = $this->extractEvents($this->events2, $this->team2);

        $list = array_merge($firstTeam, $secondTeam);
        $firstHalf = array_filter($list, function ($item) {
            return $item['isFirstHalf'];
        });
        $secondHalf = array_filter($list, function ($item) {
            return !$item['isFirstHalf'];
        });

        $column = array_column($firstHalf, 'totalTime');
        $secondColumn = array_column($firstHalf, 'order');
        array_multisort($column, $secondColumn, SORT_ASC, $firstHalf);

        $column = array_column($secondHalf, 'totalTime');
        $secondColumn = array_column($secondHalf, 'order');
        array_multisort($column, $secondColumn, SORT_ASC, $secondHalf);

        foreach ($firstHalf as $event) {
            $result[] = $this->extractResultData($event);
        }

        foreach ($secondHalf as $event) {
            $result[] = $this->extractResultData($event);
        }

        return $result;
    }

    private function extractResultData($event)
    {
        $string = '';
        $string .= $event['teamName'] . ' ';
        $string .= $event['name'] . ' ';
        $string .= $event['time'];
        if (isset($event['extraTime'])) {
            $string .= '+' . $event['extraTime'] . ' ';
        } else {
            $string .= ' ';
        }
        $string .= $event['eventType'] . ' ';
        if ($event['eventType'] === 'S') {
            $string .= $event['secondPlayerName'];
        }

        return $string;
    }

    private function extractEvents($events, $team)
    {
        $response = [];
        $eventOrder = 'GYRS';

        foreach ($events as $index => $event) {
            $response[$index]['teamName'] = $team;
            $response[$index]['name'] = trim(preg_replace("/[^a-z ]+/", "", $event));
            $response[$index]['eventType'] = trim(preg_replace("/[^A-Z]+/", "", $event));
            $time = trim(preg_replace("/[^0-9+]+/", "", $event));

            if (strpos($time, '+') !== false) {
                $time = explode('+', $time);
                $response[$index]['time'] = $time[0];
                $response[$index]['extraTime'] = $time[1];
                $response[$index]['totalTime'] = $time[0] + $time[1];
            } else {
                $response[$index]['time'] = $time;
                $response[$index]['totalTime'] = $time;
            }

            if ($response[$index]['eventType'] === 'S') {
                $playerNameSplit = explode('S', $event);
                $secondPlayerName = trim($playerNameSplit[1]);
                $response[$index]['secondPlayerName'] = $secondPlayerName;
                $response[$index]['name'] = trim(preg_replace("/[^a-z ]+/", "", $playerNameSplit[0]));
            }

            $response[$index]['isFirstHalf'] = $response[$index]['time'] <= 45;
            $response[$index]['order'] = strpos($eventOrder, $response[$index]['eventType']) + 1;
        }

        return $response;
    }
}
