<?php



namespace WhyooOs\Util;


/**
 * Util class for generating datetime intervals (for statistics)
 */
class UtilDate
{

    /**
     * generates array with DateTime objects of all MONDAYS starting with last monday before $startDate
     * until last monday from today
     * all dates have time 00:00:00
     *
     * marketer v2
     *
     * @param \DateTime $startDate
     * @return \DateTime[]
     */
    public static function getMondaysUntilToday(\DateTime $startDate)
    {
        $mondayBeforeStartDate = strtotime('last monday', $startDate->getTimestamp());
        $nextMonday = strtotime('next monday');
        $mondays = [];
        for ($timestamp = $mondayBeforeStartDate; $timestamp < $nextMonday; $timestamp += 3600 * 24 * 7) {
            $d = new \DateTime();
            $d->setTimestamp($timestamp);
            $mondays[] = $d;
        }
        return $mondays;
     }


    /**
     * marketer v2
     * generates array with DateTime objects of all Days starting with $startDay (time 00:00:00) until today 00:00:00
     *
     * @param \DateTime $startDate
     * @return \DateTime[] days
     */
    public static function getDaysUntilToday( \DateTime $startDate)
    {
        $startAtNoon = strtotime('noon', $startDate->getTimestamp());
        $today = (new \DateTime())->getTimestamp();
        $days = [];
        for ($timestamp = $startAtNoon; $timestamp < $today; $timestamp += 3600 * 24) {
            $d = new \DateTime();
            $d->setTimestamp($timestamp);
            $days[] = $d;
        }

        return $days;
    }


    /**
     * marketer v2
     * @return \DateTime[] last 24 hours
     */
    public static function getLast24Hours()
    {
        // to = now Full Hour
        $now = new \DateTime();
        $nowHour = (int)$now->format('H'); // eg 12
        $to = $now->setTime($nowHour, 0, 0); // full hour .. eg. 2015-08-30 13:00

        // from = 24h ago
        $from = (clone($to));
        $from->modify( "-24 hours");

        $hours = [];
        for ($timestamp = $from->getTimestamp(); $timestamp < $to->getTimestamp(); $timestamp += 3600) {
            $d = new \DateTime();
            $d->setTimestamp($timestamp);
            $hours[] = $d;
        }

        return $hours;
    }


    /**
     * from ebaygen used for monthly reports
     *
     * @param int $numMonths
     * @return \DateTime[] last N months
     */
    public static function getMonthlyIntervals(int $numMonths)
    {
        // to = now Full Hour
        $now = new \DateTime("+1 month");
        $nowMonth = (int)$now->format('m'); // eg 11
        $nowYear = (int)$now->format('Y'); // eg 11
        $to = $now->setTime(0, 0, 0); // full hour .. eg. 2015-08-30 00:00
        $to = $to->setDate($nowYear, $nowMonth, 1); // eg 1.4.2017

        $months = [];
        for ($numMonthsBack = 0; $numMonthsBack <= $numMonths; $numMonthsBack++) {
            $date = clone($to);
            $date->modify("-$numMonthsBack months");
            $months[] = clone($date);;
        }

        return array_reverse($months);
    }


    /**
     * fom ebaygen
     *
     * used for statistics
     *
     * @param $to
     * @param $intervalLength
     * @return array
     */
    public static function getInterval(\DateTime $now, string $intervalLength)
    {

        $to = clone $now;

        $to->setTime(0, 0);
        if ($intervalLength == '1d') {
            $to->modify('+1 day');
        }
        if ($intervalLength == '1w') {
            $to->modify('monday this week');
            $to->modify('+1 week');
        }
        if (substr($intervalLength, strlen($intervalLength) - 1) == 'm') {
            $monthLength = (int)substr($intervalLength, 0, strlen($intervalLength) - 1); // 1,3,6,12
            $month = $to->format('n');
            $periodStartedNMonthsAgo = $month % $monthLength; // eg 4%6 = 4; 8%6 = 2
            if ($periodStartedNMonthsAgo > 0) {
                $toAdd = $monthLength - $periodStartedNMonthsAgo; // eg 6-4; 6-2
                echo "month: $month\n";
                echo "monthLength: $monthLength\n";
                echo "periodStartedNMonthsAgo: $periodStartedNMonthsAgo\n";
                echo "toAdd: $toAdd\n";
                $to->modify("+$toAdd months");
            }
            $to->modify('first day of this month');
            $to->modify('+1 month');
        }

        $from = clone $to;

        $modify = [
            '1d' => '-1 day',
            '1w' => '-1 week',
            '1m' => '-1 month',
            '3m' => '-3 month',
            '6m' => '-6 month',
            '12m' => '-12 month',
        ];

        $from->modify($modify[$intervalLength]);

        return [
            'from' => $from,
            'to' => $to,
        ];
    }




    /**
     * used by marketer
     *
     * @param $isoString
     * @return \DateTime
     */
    public static function isoStringToDateTime($isoString)
    {
        return \DateTime::createFromFormat('Y-m-d\TH:i:s+', $isoString);
    }


    /**
     * 03/2018 used for (C) in eqipoo
     */
    public static function getYear()
    {
        return date('Y');
    }

}

