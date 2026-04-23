<?php

/**
 * Výjimka a třída pro práci s datumem a časem
 *
 * @name TDateTime
 * @version 1.12
 * @author vladimir.horky
 * @copyright Vladimír Horký, 2018
 *
 * version 1.12
 * small changes
 * added new function TimeStampToStr
 * changed function DateTimeToStr
 *
 * version 1.11
 * added new function timeStampRound
 *
 * version 1.10
 * added new funciton InitStartKonecUTC
 *
 * version 1.9
 * added DateTimeISO8601
 *
 * version 1.8
 * added mask support 
 *
 * version 1.7
 * fixed DateEN2CZ
 * fixed DateTimeCZ2EN
 *
 * version 1.6
 * fixed Sec2Time
 *
 * version 1.5
 * odstraněny některé chyby u datumových a časových funkcí
 *
 * version 1.4
 * Added new function DateCmp
 * Added new function DateTimeCmp
 * Added new function TimeCmp
 *
 * version 1.3
 * Added new function nextMonth
 * Added new function nextDay
 * Added new function nextDate
 *
 * version 1.2
 * Modifed function UTCToCET, added parameter timezone
 * Added new function CETToUTC
 * Added new function UTCToLocal
 * Added new function LocalToUTC
 *
 * version 1.1
 * Added new function Years
 **/

declare(strict_types=1);

class TDateTime
{
	const int MINUTE	= 60;
	const int HOUR		= 3600;
	const int DAY 		= 86400;
	const int YEAR		= 31536000;

    /**
     * Porovná dva datumy a vrátí 0, pokud jsou stejné
     *
     * @param ?string $date1
     * @param ?string $date2
     * @param string $mask
     * @return int
     */
	public static function DateCmp(?string $date1, ?string $date2, string $mask = 'Ymd') :int
	{
        if($date1 != '')
        {
		    $tm1 = strtotime($date1);
            if(!$tm1)
                $tm1 = 0;
        } else
            $tm1 = 0;

        if($date2 != '')
        {
            $tm2 = strtotime($date2);
            if(!$tm2)
                $tm2 = 0;
        } else
            $tm2 = 0;

		$tm1_format = Date($mask, $tm1);
		$tm2_format = Date($mask, $tm2);

		return strcmp($tm1_format, $tm2_format);
	}

    /**
     * Porovná dva datumy + časy a vrátí 0, pokud jsou stejné
     *
     * @param string|null $datetime1
     * @param string|null $datetime2
     * @param string $mask
     * @return int
     */
	public static function DateTimeCmp(?string $datetime1, ?string $datetime2, string $mask = 'YmdHis') :int
	{
        if($datetime1 != '')
        {
		    $tm1 = strtotime($datetime1);
            if(!$tm1)
                $tm1 = 0;
        } else
            $tm1 = 0;

        if($datetime2 != '')
        {
		    $tm2 = strtotime($datetime2);
            if(!$tm2)
                $tm2 = 0;
        } else
            $tm2 = 0;

		$tm1_format = Date($mask, $tm1);
		$tm2_format = Date($mask, $tm2);

		return strcmp($tm1_format, $tm2_format);
	}

    /**
     * Porovná dva časy a vrátí 0, pokud jsou stejné
     *
     * @param ?string $time1
     * @param ?string $time2
     * @param string $mask
     * @return int
     */
	public static function TimeCmp(?string $time1, ?string $time2, string $mask = 'His') :int
	{
        if($time1 != '')
        {
		    $tm1 = strtotime($time1);
            if(!$tm1)
                $tm1 = 0;
        } else
            $tm1 = 0;

        if($time2 != '')
        {
		    $tm2 = strtotime($time2);
            if(!$tm2)
                $tm2 = 0;
        } else
            $tm2 = 0;

		$tm1_format = Date($mask, $tm1);
		$tm2_format = Date($mask, $tm2);

		return strcmp($tm1_format, $tm2_format);
	}

	/**
	 * Doplní před číslo nuly dle zadaného počtu míst
	 *
	 * @param string $str
	 * @param int $count
	 * @return string
	 */
	public static function FillZero(string $str, int $count) :string
	{
		return str_pad($str, $count, '0', STR_PAD_LEFT);
	}

	/**
	 * Doplní nulu před číslo měsíce
	 *
	 * @param int $number
	 * @return int
	 */
	public static function FullNumberMesic(int $number) :string
	{
		if($number < 10)
			return '0'.(string)$number;
		else
			return (string)$number;
	}

	/**
	 * Doplní rok na čtyřmístný
	 *
	 * @param int $number
	 * @return string
	 */
	public static function FullNumberRok(int $number) :string
	{
		if(mb_strlen((string)$number) <= 3)
			return '2'.self::FillZero((string) $number, 3);
		else
			return (string)$number;
	}

    /**
     * Převede sekundy na formátovaný čas
     *
     * @param int $sec
     * @param bool $hours
     * @return string
     */
	public static function Sec2Time(int $sec, bool $hours = true) :string
	{
        $sec = round($sec);

		if($sec < 0)
		{
			$znamenko = '-';
			$sec = abs($sec);
		} else
			$znamenko = '';

		if($sec >= 60)
		{
			$cas = [];
			if($hours === true)
			{
				if($sec >= 3600)
				{
					$ho = $sec % 3600;
					$cas['hodiny'] = ($sec - $ho) / 3600;
					$sec = $ho;
				} else
					$cas['hodiny'] = '00';
			}

			$re = $sec % 60;
			$cas['minuty'] 	= ($sec - $re) / 60;
			$cas['sekundy'] = $re;
		} else {
			$cas = [
				'sekundy'=> $sec,
				'minuty' => '00',
				'hodiny' => '00'
			];
		}

		if($hours)
			return $znamenko.self::FillZero((string)$cas['hodiny'],2).':'.self::FillZero((string)$cas['minuty'],2).':'.self::FillZero((string)$cas['sekundy'],2);
		else
			return $znamenko.self::FillZero((string)$cas['minuty'],2).':'.self::FillZero((string)$cas['sekundy'],2);
	}

	/**
	 * Funkce která vrací vormát H:mm ze zadaného počtu minut
	 *
	 * @param ?mixed $minutes
	 * @return string
	 */
	public static function Min2Time(mixed $minutes) :string
	{
		if(intval($minutes) > 0)
		{
			$hours = floor($minutes/60);
			$min = $minutes % 60;
			if($min < 10)
				$min = '0'.$min;

			return $hours.':'.$min;
		} else
			return '0:00';
	}

    public static function Time2Sec(string $time) :int
	{
        if($time != '')
        {
            $items = explode(':', $time);

            return match (count($items)) {
                1 => intval($items[0]),
                2 => intval($items[0]) * 60 + intval($items[1]),
                3 => intval($items[0]) * 3600 + intval($items[1]) * 60 + intval($items[2]),
                default => 0,
            };
        } else
            return 0;
	}
	/**
	 * Zjišťuje, zda datum $date leží v intervalu $begin a end
	 *
	 * @param ?mixed $date
	 * @param string $begin
	 * @param string $end
	 * @return bool
	 */
	public static function betweenDates(mixed $date, string $begin, string $end) :bool
	{
		if(is_string($date))
			$tm = strtotime($date);
		else
			$tm = $date;

		if($end == '')
		{
			if($begin != '')
				return $tm >= strtotime($begin);
			else
				return false;
		} else
			if($begin == '')
				return $tm <= strtotime($end);
			else
				return ($tm >= strtotime($begin) && $tm <= strtotime($end));
	}

	/**
	 * Funkce formátuje datum do řetězce dle zadané masky
	 *
	 * @param ?int $date
	 * @param string $format
	 * @return string
	 */
	public static function DateToStr(?int $date, string $format = 'd.m.Y') :string
	{
		return Date($format, $date);
	}

	/**
	 * Funkce formátuje čas do řetězce dle zadané masky
	 *
	 * @param ?int $time
	 * @param string $format
	 * @return string
	 */
	public static function TimeToStr(?int $time, string $format = 'H:i:s') :string
	{
		return Date($format, $time);
	}

    /**
     * Funkce formátuje datum a čas do řetězce dle zadané masky
     *
     * @param int|string|null $datetime
     * @param string $format
     * @return string
     */
	public static function DateTimeToStr(null|int|string $datetime, string $format = 'd.m.Y H:i:s') :string
	{
        if($datetime != '')
        {
            if(is_string($datetime))
                $datetime = strtotime($datetime);

            return Date($format, $datetime);
        } else
            return '';
	}

    public static function TimeStampToStr(?int $timestamp, string $format = 'd.m.Y H:i:s') :string
    {
        return Date($format, $timestamp);
    }

    public static function format(?int $datetime, string $format = 'd.m.Y H:i') :string
    {
        return Date($format, $datetime);
    }

    public static function formatCZ(?int $timeStamp, string $format = 'd.m.Y H:i') :string
    {
        if($timeStamp != '')
            return Date($format, $timeStamp);
        else
            return '';
    }

    public static function formatUTC(?int $datetime, string $format = 'd.m.Y H:i'): string
    {
        return gmDate($format, $datetime);
    }

    public static function DateTimeToISO8601(?int $datetime, bool $to_utc = true, string $format = 'Y-m-d\TH:i:s\Z'): string
    {
        if($to_utc)
            return gmdate($format, $datetime);
        else
            return Date($format, $datetime);
    }

	public static function TimeStampToISO8601(null|string|int $datetime, bool $toUtc = true, string $format = 'Y-m-d\TH:i:s\Z') :string
	{
        if(is_string($datetime))
            $datetime = strtotime($datetime);

		if($toUtc)
			return gmdate($format, $datetime);
		else
			return Date($format, $datetime);
	}

    public static function DateToISO8601(null|string|int $date, string $format = 'Y-m-d') :string
    {
        if(is_string($date))
            $date = strtotime($date);

        return Date($format, $date);
    }

    // OVERENO FUNKCNI
    public static function iso8601ToDateTime(int|null|string $datetime_string, bool $toUtc = false, string $format = 'Y-m-d H:i:s') :string
    {
        $datetime = self::iso8601ToTimeStamp($datetime_string);

        if($toUtc)
            $result = gmDate($format, $datetime);
         else
            $result = Date($format, $datetime);

        return $result;
    }

    // OVERENO FUNKCNI
    public static function iso8601ToTimeStamp(null|string|int $datetime, string $timezone = 'UTC') :int
    {
        if(is_string($datetime))
            $datetime = strtotime($datetime);
        else
            $datetime = intval($datetime);

        return intval(Date('U', $datetime));
    }

    public static function initStartKonecTimeStamp(string $datum, string $timezone, ?int &$startTimeStamp, ?int &$konecTimeStamp) :void
    {
        $startTimeStamp = strtotime($datum.' 00:00:00');
        $konecTimeStamp = strtotime(Date('d.m.Y H:00:00', $startTimeStamp).' +1 day');
    }

    public static function initMonthStartKonecTimeStamp(string $datum, string $timezone, ?int &$startTimeStamp, ?int &$konecTimeStamp) :void
    {
        $tm = strtotime($datum);

        $startTimeStamp = strtotime(Date('1.m.Y 00:00:00', $tm));
        $konecTimeStamp = strtotime(Date('t.m.Y H:00:00', $tm).' +1 day');
    }

	/**
	 *
	 * Funkce převádí tvar datumů z 23.12.2018 na 2018-12-23
	 *
	 * @param ?string $date
	 * @param string $inseparator
	 * @param string $outseparator
	 * @return string
	 */
	public static function DateCZ2EN(?string $date, string $inseparator = '.', string $outseparator = '-') :string
	{
		$pole = explode($inseparator, explode(' ', $date)[0]);
		$pole = array_reverse($pole);

		return implode($outseparator, $pole);
	}

	/**
	 * Funcke převrátí tvar datumů z 2018-12-23 na 23.12.2018
	 *
	 * @param ?string $date
	 * @param string $inseparator
	 * @param string $outseparator
	 * @return string
	 */
	public static function DateEN2CZ(?string $date, string $inseparator = '-', string $outseparator = '.') :string
	{
		return self::DateCZ2EN($date, $inseparator, $outseparator);
	}

	/**
	 * Funkce převrátí tvar data a času z 23.12.2018 10:50:00 na 2018-12-23 10:50:00
	 *
	 * @param ?string $datetime
	 * @param string $inseparator
	 * @param string $outseparator
	 * @return string
	 */
	public static function DateTimeCZ2EN(?string $datetime, string $inseparator = '.', string $outseparator = '-') :string
	{
		$casti = explode(' ',$datetime);
		if(count($casti) === 2)
		{
			$pole = explode($inseparator, $casti[0]);
			$pole = array_reverse($pole);
			return implode($outseparator, $pole).' '.$casti[1];
		} else
			if(count($casti) == 1)
				return self::DateCZ2EN($datetime);
			else
				return '';
	}

	/**
	 * Funkce převrátí tvar data a času z 2018-12-23 10:50:00 na 23.12.2018 10:50:00
	 *
	 * @param ?string $datetime
	 * @param string $inseparator
	 * @param string $outseparator
	 * @return string
	 */
	public static function DateTimeEN2CZ(?string $datetime, string $inseparator = '-', string $outseparator = '.') :string
	{
		return self::DateTimeCZ2EN($datetime, $inseparator, $outseparator);
	}

	/**
	 * Funkce doplní nuly před den a měsíc, doplní 20 před rok
	 *
	 * @param ?string $date
	 * @param string $separator
	 * @return string
	 */
	public static function FillDate(?string $date, string $separator = '.') :string
	{
		$items = explode($separator, trim($date));
		if(count($items) > 1)
		{
			while(mb_strlen($items[0]) < 2)
				$items[0] = '0'.$items[0];

			while(mb_strlen($items[1]) < 2)
				$items[1] = '0'.$items[1];

			if(mb_strlen($items[2]) < 3)
				$items[2] = '0'.$items[2];

			if(mb_strlen($items[2]) < 4)
				$items[2] = '2'.$items[2];

			return implode($separator, $items);
		} else
			return '';
	}

	/**
	 * Funkce odstraní nuly u dne a měsíce
	 *
	 * @param ?string $date
	 * @param string $separator
	 * @return string
	 */
	public static function DeFillDate(?string $date, string $separator = '.') :string
	{
		$items = explode($separator, trim($date));
		if(count($items) > 1)
		{
			while(mb_substr($items[0],0,1) == '0')
				$items[0] = mb_substr($items[0], 1);

			while(mb_substr($items[1],0,1) == '0')
				$items[1] = mb_substr($items[1], 1);

			return implode($separator, $items);
		} else
			return '';
	}

	/**
	 * Funkce doplní nuly před hodinu, minutu a sekundu
	 *
	 * @param ?string $time
	 * @param string $separator
	 * @return string
	 */
	public static function FillTime(?string $time, string $separator = ':') :string
	{
		$items = explode($separator, trim($time));

		if(mb_strlen($items[0]) < 2)
			$items[0] = '0'.$items[0];

		if(count($items) > 1)
		{
			if(mb_strlen($items[1]) < 2)
				$items[1] = '0'.$items[1];

			if(count($items) > 2)
				if(mb_strlen($items[2]) < 2)
					$items[2] = '0'.$items[2];
		}

		return implode($separator, $items);
	}

	/**
	 * Funkce odstraní nuly před hodinou, minutou a sekundou
	 *
	 * @param ?string $time
	 * @param string $separator
	 * @return string
	 */
	public static function DeFillTime(?string $time, string $separator = ':') :string
	{
		$items = explode($separator, trim($time));

		if(count($items) > 1)
		{
			while(mb_substr($items[0], 0, 1) == '0')
				$items[0] = mb_substr($items[0], 1);

			while(mb_substr($items[1], 0, 1) == '0')
				$items[1] = mb_substr($items[1], 1);

			while(mb_substr($items[2], 0, 1) == '0')
				$items[2] = mb_substr($items[2], 1);

			return implode($separator, $items);
		} else
			return '';
	}

	/**
	 * Funkce zjistí, zda je zadaný rok přestupní
	 *
	 * @param int $year
	 * @return bool
	 */
	public static function isTransitionalYear(int $year) :bool
	{
		return (bool) date('L', mktime(0, 0, 0, 1, 1, $year));
	}

	/**
	 * Funkcec vrací počet dnů v měsíci daného roku
	 *
	 * @param int $month
	 * @param int $year
	 * @return int
	 */
	public static function DaysInMonth(int $month, int $year) :int
	{
		return cal_days_in_month(CAL_GREGORIAN, $month, $year);
	}

	public static function DaysDiffDates(string $dateFrom, string $dateTo) :int
	{
		if($dateFrom != '' && $dateTo != '')
		{
			$valid_from	= Date('d.m.Y', strtotime($dateFrom));
			$valid_to	= Date('d.m.Y', strtotime($dateTo));

			$date_from	= date_create($valid_from, new DateTimeZone(TConfig::TIMEZONE_LOCAL));
			$date_to	= date_create($valid_to, new DateTimeZone(TConfig::TIMEZONE_LOCAL));

			$result		= intval(date_diff($date_from, $date_to)->format('%a')) + 1;
		} else
			$result = 0;

		return $result;
	}

	/**
	 * Funkce vrací následující měsíc daného roku
	 *
	 * @param int $month
	 * @param int $year
	 * @return int
	 */
	public static function nextMonth(int $month, int $year) :int
	{
		if($month >= 12)
			return 1;
		else
			return $month + 1;
	}

	/**
	 * Funkce vrací následující den daného měsíce a roku
	 *
	 * @param int $day
	 * @param int $month
	 * @param int $year
	 * @return int
	 */
	public static function nextDay(int $day, int $month, int $year) :int
	{
		$days_in_month = self::DaysInMonth($month, $year);
		if($day >= $days_in_month)
			return 1;
		else
			return $day + 1;
	}

	/**
	 * Funkce vrací následující datum v datovém typu int
	 *
	 * @param int $day
	 * @param int $month
	 * @param int $year
	 * @return int
	 */

	public static function nextDate(int $day, int $month, int $year) :int
	{
		$tm = strtotime($day.'.'.$month.'.'.$year) + self::DAY;
		return $tm;
	}

	/**
	 * Funkce vrací pořadí dne (po, út, ...) v týdnu prvního dne v mesíci daného roku
	 *
	 * @param int $month
	 * @param int $year
	 * @return int
	 */
	public static function FirstDay(int $month, int $year) :int
	{
		$num = intval(date('w', mktime(0, 0, 0, $month, 1, $year)));
		return ($num == 0) ? 7 : $num;
	}

	/**
	 * Číselník mesíců
	 *
	 * @return string[]
	 */
	public static function Months(): array
    {
		return [
			1  => __('month.january', 'leden'),
			2  => __('month.februrary', 'únor'),
			3  => __('month.march', 'březen'),
			4  => __('month.april', 'duben'),
			5  => __('month.may', 'květen'),
			6  => __('month.june', 'červen'),
			7  => __('month.july', 'červenec'),
			8  => __('month.august', 'srpen'),
			9  => __('month.september', 'září'),
			10 => __('month.october', 'říjen'),
			11 => __('month.november', 'listopad'),
			12 => __('month.december', 'prosinec')
		];
	}

	/**
	 * Funkce, která vrací položku z císelníku měsícu dle zadaného klíče resp. čísla měsíce
	 *
	 * @param int $month
	 * @return string
	 */
	public static function Month(int $month) :string
	{
		$zal = self::Months();
		return $zal[$month];
	}

	/**
	 * Funkce, která vrací číselník měsíců rozšířený o $result do ComboBoxu
	 *
	 * @param array $result
	 * @return array
	 */
	public static function optionsMonths(array $result = []): array
    {
		return $result + self::Months();
	}

	/**
	 * Funkce vrací seznam názvů měsíců v poli (indexy pole začínají od 0)
	 *
	 * @return string[]
	 */
	public static function listMonths(): array
    {
		$result = [];

		$months = self::Months();
		foreach($months as $month_id => $name)
			$result[] = $name;

		return $result;
	}

	/**
	 * Číselník názvů dnů v týdnu
	 *
	 * @return array
	 */
	public static function Days() :array
	{
		return [
			'ne' => __('day.sunday', 'neděle'),
			'po' => __('day.monday', 'pondělí'),
			'út' => __('day.tuesday', 'úterý'),
			'st' => __('day.wednesday', 'středa'),
			'čt' => __('day.thursday', 'čtvrtek'),
			'pá' => __('day.friday', 'pátek'),
			'so' => __('day.saturday', 'sobota'),
		];
	}

	/**
	 * Funkce, která vrací zkratku názvu dne dle zadaného pořadí
	 *
	 * @param int $day
	 * @throws EDateTime
	 * @return string
	 */

	public static function Day(int $day) :string
	{
        return match ($day) {
            0 => __('day.sun', 'Ne'),
            1 => __('day.mon', 'Po'),
            2 => __('day.tue', 'Út'),
            3 => __('day.wed', 'St'),
            4 => __('day.thu', 'Čt'),
            5 => __('day.fri', 'Pá'),
            6 => __('day.sat', 'So'),
            default => throw new EDateTime('Unknown day ".$day." in parameter!'),
        };
	}

	/**
	 * Funkce, která vrací pole roků počínaje od aktuálního roku až do $from sestupně
	 *
	 * @param array $result
	 * @param int $from
	 * @return array
	 */
	public static function Years(array $result = [], int $from = 2010) :array
	{
		for($i = Date('Y'); $i >= $from; $i--)
			$result[$i] = $i;

		return $result;
	}

	/**
	 * Funkce, která vrací pole roků počínaje od aktuálního roku až do $from sestupně s textem Rok (Year)
	 *
	 * @param array $result
	 * @param int $from
	 * @return array
	 */
	public static function Seasons(array $result = [], int $from = 2010) :array
	{
		for($i = Date('Y'); $i >= $from; $i--)
			$result[$i] = __('year', 'Rok').' '.$i;

		return $result;
	}

	/**
	 * Funkce vrací zkratku dne v týdnu společně se zadaným datem
	 *
	 * @param ?string $date
	 * @param string $default
	 * @return string
	 */
	public static function DayWithDate(?string $date, string $default = '-') :string
	{
		if($date != '' && $date != '00.00.0000' && $date !== null)
		{
			return self::Day((int)Date('w', strtotime($date))).' '.$date;
		} else
			return $default;
	}

	/**
	 * Funkce převádí čas UTC na středoevropský
	 *
	 * @param int $datetime
	 * @return int
	 */
 	public static function UTCToCET(int $datetime) :int
	{
		return self::UTCToLocal($datetime, 'Europe/Prague');
	}

	/**
	 * Funkce převádí čas UTC na lokální dle zadané časové zóny
	 *
	 * @param int $datetime
	 * @param string timezone
	 * @return int
	 */
	public static function UTCToLocal(int $datetime, string $timezone) :int
	{
		$default_timezone = date_default_timezone_get();
		date_default_timezone_set($timezone);
		$result = $datetime + intval(Date('Z'));
		date_default_timezone_set($default_timezone);

		return $result;
	}

	/**
	 * Funkce převádí středoevropský čas na UTC
	 *
	 * @param int $datetime
	 * @return int
	 */
	public static function CETToUTC(int $datetime) :int
	{
		return self::LocalToUTC($datetime, 'Europe/Prague');
	}

	/**
	 * Funkce převádí lokální čas na UTC dle zadané časové zóny
	 *
	 * @param int $datetime
	 * @param string $timezone
	 * @return int
	 */
	public static function LocalToUTC(int $datetime, string $timezone) :int
	{
		$default_timezone = date_default_timezone_get();
		date_default_timezone_set($timezone);
		$result = $datetime - Date('Z');
		date_default_timezone_set($default_timezone);

		return $result;
	}

    /**
     * @throws Exception
     */
    public static function changeDateTimeTZ($datetime, string $timezoneFromString, string $timezoneToString, string $format = 'd.m.Y H:i:s') :string
    {
        if(is_int($datetime))
            $datetime = Date($format, $datetime);

        $date = new DateTime($datetime, new DateTimeZone($timezoneFromString));
        $date->setTimezone(new DateTimeZone($timezoneToString));
        return $date->format($format);
    }

    /**
     * @throws Exception
     */
    public static function changeTimeStampTZ($timestamp, string $timezoneFromString, string $timezoneToString, ?string $format = null) :int
    {
        if(!$timestamp)
            return 0;

        if(is_string($timestamp))
            $timestamp = strtotime($timestamp);

        if($timezoneFromString !== $timezoneToString)
        {
            $dateTimeZoneFrom = new DateTimeZone($timezoneFromString);
            $dateTimeZoneTo   = new DateTimeZone($timezoneToString);

            $dateTimeFrom   = new DateTime(Date('d.m.Y H:i', $timestamp), $dateTimeZoneFrom);
            $dateTimeTo     = new DateTime(Date('d.m.Y H:i', $timestamp), $dateTimeZoneTo);

            $timeOffset = $dateTimeTo->getOffset() - $dateTimeFrom->getOffset();
        } else {
            $timeOffset = 0;
        }

        // Odseknout cas a zbyde jenom datum
        if($format != '')
            return strtotime(Date($format, $timestamp + $timeOffset));
        else
            return $timestamp + $timeOffset;
    }

    public static function timeStampRound(int $time, int $roundTo): int
    {
        return (int)floor($time / $roundTo) * $roundTo;
    }

    /**
     * @throws Exception
     */
    public static function changeTimeStampCZ(?int $timestamp) :?int
    {
        return self::changeTimeStampTZ($timestamp,'UTC', 'Europe/Prague');
    }

    /**
     * @throws Exception
     */
    public static function changeDateTimeCZ(?string $datetime, string $format = 'd.m.Y H:i') :?string
    {
        return self::changeDateTimeTZ($datetime,'UTC', 'Europe/Prague', $format);
    }

    /**
     * @throws Exception
     */
    public static function changeTimeStampUTC(?int $timestamp, string $localTimeZone = 'Europe/Prague') :?int
    {
        return self::changeTimeStampTZ($timestamp, $localTimeZone,'UTC');
    }

    /**
     * @throws Exception
     */
    public static function changeDateTimeUTC(?string $datetime, string $localTimeZone = 'Europe/Prague', string $format = 'd.m.Y H:i') :?string
    {
        return self::changeDateTimeTZ($datetime, $localTimeZone,'UTC', $format);
    }

}
