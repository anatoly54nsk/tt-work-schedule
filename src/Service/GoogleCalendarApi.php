<?php


namespace App\Service;


use DateTimeImmutable;
use Symfony\Component\HttpClient\NativeHttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GoogleCalendarApi implements ICalendarApi
{
    private const API_KEY = 'AIzaSyCVLRu1GUnuEsQ8927QaZBBBPvBt73KYnM';
    public const FORMAT_API_CALENDAR_REQUEST_DATE = 'Y-m-d\TH:i:s\Z';
    public const FORMAT_API_CALENDAR_RESPONSE_DATE = 'Y-m-d';
    public const API_CALENDAR_EVENTS_URL = 'https://www.googleapis.com/calendar/v3/calendars/russian%40holiday.calendar.google.com/events';

    /**
     * @var NativeHttpClient
     */
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param DateTimeImmutable $start
     * @param DateTimeImmutable $end
     * @return DateTimeImmutable[]
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function getHolidays(DateTimeImmutable $start, DateTimeImmutable $end): array
    {
        $queryParams = [
            'key' => self::API_KEY,
            'timeMin' => $start->format(self::FORMAT_API_CALENDAR_REQUEST_DATE),
            'timeMax' => $end->format(self::FORMAT_API_CALENDAR_REQUEST_DATE),
            'singleEvents' => 'false',
        ];

        $url = $this->buildUrl(self::API_CALENDAR_EVENTS_URL, $queryParams);
        $response = $this->httpClient->request('GET', $url);

        $dates = [];
        if ($response->getStatusCode() === 200) {
            $holidays = ($response->toArray())['items'];
            if (count($holidays)) {
                foreach ($holidays as $holiday) {
                    $dates[] = (DateTimeImmutable::createFromFormat(self::FORMAT_API_CALENDAR_RESPONSE_DATE, $holiday['start']['date']))
                        ->modify('midnight');
                }
            }
        }
        return $dates;
    }

    private function buildUrl(string $url, array $queryParams)
    {
        $queryString = implode('&', array_map(function ($parameter, $key) {
            return implode('=', [$key, $parameter]);
        }, $queryParams, array_keys($queryParams)));
        return "{$url}?{$queryString}";
    }
}
