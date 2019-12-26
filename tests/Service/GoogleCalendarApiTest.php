<?php


namespace App\Tests\Service;


use App\Service\GoogleCalendarApi;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\NativeHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class GoogleCalendarApiTest extends TestCase
{
    public function testGetHolidays()
    {
        $dt = (new DateTimeImmutable())->modify('midnight');
        $items = [
            'items' => [
                [
                    'start' => [
                        'date' => $dt->modify('+5 day')->format(GoogleCalendarApi::FORMAT_API_CALENDAR_RESPONSE_DATE),
                    ],
                ],
                [
                    'start' => [
                        'date' => $dt->modify('+10 day')->format(GoogleCalendarApi::FORMAT_API_CALENDAR_RESPONSE_DATE),
                    ],
                ],
            ],
        ];
        $expected = [
            $dt->modify('+5 day'),
            $dt->modify('+10 day'),
        ];
        $response = $this->createMock(MockResponse::class);
        $response->expects($this->once())
            ->method('getStatusCode')->willReturn(200);
        $response->expects($this->once())
            ->method('toArray')->willReturn($items);
        /** @var NativeHttpClient | MockObject $httpClient */
        $httpClient = $this->createMock(MockHttpClient::class);
        $httpClient->expects($this->once())->method('request')
            ->with('GET', $this->logicalAnd(
                $this->stringContains(GoogleCalendarApi::API_CALENDAR_EVENTS_URL),
                $this->stringContains($dt->format(GoogleCalendarApi::FORMAT_API_CALENDAR_REQUEST_DATE)),
                $this->stringContains($dt->modify('+1 month')->format(GoogleCalendarApi::FORMAT_API_CALENDAR_REQUEST_DATE))
            ))
            ->willReturn($response);

        $api = new GoogleCalendarApi($httpClient);
        $days = $api->getHolidays($dt, $dt->modify('+1 month'));
        self::assertEquals($expected, $days);
    }
}
