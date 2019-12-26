<?php


namespace App\Controller;


use App\Entity\CorporateParty;
use App\Entity\IDay;
use App\Entity\ITimeInterval;
use App\Entity\Vacation;
use App\Entity\WorkSchedule;
use App\Repository\CorporatePartyRepository;
use App\Repository\VacationRepository;
use App\Service\CorporatePartyDayMapper;
use App\Service\DayFilter;
use App\Service\DayMapHandler;
use App\Service\ICalendarApi;
use App\Service\IDayFactory;
use App\Service\IDayMapper;
use App\Service\ITimeIntervalFactory;
use App\Service\NegativeDayFilterHandler;
use App\Service\NegativeDayMapper;
use App\Service\PositiveDayMapper;
use DateTimeImmutable;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ScheduleController extends AbstractController
{
    /**
     * @var IDayFactory
     */
    private $dayFactory;
    /**
     * @var ITimeIntervalFactory
     */
    private $timeIntervalFactory;
    /**
     * @var ICalendarApi
     */
    private $calendarApi;

    public function __construct(IDayFactory $dayFactory, ITimeIntervalFactory $timeIntervalFactory, ICalendarApi $calendarApi)
    {
        $this->dayFactory = $dayFactory;
        $this->timeIntervalFactory = $timeIntervalFactory;
        $this->calendarApi = $calendarApi;
    }

    /**
     * @Route("/schedule")
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $dateBegin = $request->query->get('startDate');
        $dateEnd = $request->query->get('endDate');
        $staffId = $request->query->get('userId');

        $dtBegin = DateTimeImmutable::createFromFormat(ITimeInterval::FORMAT_DATE, $dateBegin)
            ->modify('midnight');
        $dtEnd = DateTimeImmutable::createFromFormat(ITimeInterval::FORMAT_DATE, $dateEnd)
            ->modify('midnight');

        $days = $this->getDays($dtBegin, $dtEnd, true);

        try {
            $excludedDates = $this->getVacationDates($staffId, $dtBegin, $dtEnd);
        } catch (Exception $e) {
            return new JsonResponse(['error' => "({$e->getCode()}): {$e->getMessage()}"]);
        }

        $holidays = $this->calendarApi->getHolidays($dtBegin, $dtEnd);
        $excludedDates = array_merge($excludedDates, $holidays);

        $filter = new DayFilter($excludedDates);
        $filterHandler = new NegativeDayFilterHandler($filter);
        $days = $filterHandler->filter($days);

        $workSchedulesRepo = $this->getDoctrine()->getRepository(WorkSchedule::class);
        /** @var WorkSchedule $workSchedule */
        $workSchedule = $workSchedulesRepo->findOneBy(['staff' => $staffId]);

        /** @var ITimeInterval $workInterval */
        $workInterval = $this->timeIntervalFactory->create(
            $workSchedule->getWorkDayStart(),
            $workSchedule->getWorkDayLength()
        );
        /** @var ITimeInterval $lunchBreakInterval */
        $lunchBreakInterval = $this->timeIntervalFactory->create(
            $workSchedule->getLunchBreakStart(),
            $workSchedule->getLunchBreakLength()
        );

        $positiveMapper = new PositiveDayMapper([$workInterval], $this->timeIntervalFactory);
        $negativeMapper = new NegativeDayMapper([$lunchBreakInterval], $this->timeIntervalFactory, $positiveMapper);

        $negativeMapper = $this->addPartiesMapper($dtBegin, $dtEnd, $negativeMapper, $this->timeIntervalFactory);

        $mapHandler = new DayMapHandler($negativeMapper);
        $days = $mapHandler->map($days);

        return new JsonResponse($days);
    }

    /**
     * @param int $staffId
     * @param DateTimeImmutable $begin
     * @param DateTimeImmutable $end
     * @param array $excludedDates
     * @return DateTimeImmutable[]
     * @throws Exception
     */
    private function getVacationDates(
        int $staffId,
        DateTimeImmutable $begin,
        DateTimeImmutable $end,
        array $excludedDates = []
    ): array
    {
        /** @var VacationRepository $staffRepo */
        $vacationRepo = $this->getDoctrine()->getRepository(Vacation::class);
        $vacations = $vacationRepo->findByStaffBetweenDates($staffId, $begin, $end);
        if (!empty($vacations)) {
            $dt = new DateTimeImmutable();
            foreach ($vacations as $vacation) {
                /** @var Vacation $vacation */
                $dt = $dt->setTimestamp($vacation->getStart())->modify('midnight');
                $vacationEnd = $dt->modify("+{$vacation->getLength()} day")->getTimestamp();
                while ($dt->getTimestamp() < $vacationEnd) {
                    $excludedDates[] = $dt;
                    $dt = $dt->modify('+1 day');
                }
            }
        }
        return $excludedDates;
    }

    /**
     * @param DateTimeImmutable $dtBegin
     * @param DateTimeImmutable $dtEnd
     * @param IDay[] $days
     * @param bool $excludeWeekends
     * @return array
     */
    private function getDays(
        DateTimeImmutable $dtBegin,
        DateTimeImmutable $dtEnd,
        $excludeWeekends = false,
        array $days = []
    ): array
    {
        if ($dtBegin->getTimestamp() <= $dtEnd->getTimestamp()) {
            $start = $dtBegin;
            $end = $dtEnd->getTimestamp();
            while ($start->getTimestamp() <= $end) {
                if ($excludeWeekends && ((int)$start->format('w') === 0 || (int)$start->format('w') === 6)) {
                    $start = $start->modify('+1 day');
                    continue;
                }
                $days[] = $this->dayFactory->create($start);
                $start = $start->modify('+1 day');
            }
        }
        return $days;
    }

    private function addPartiesMapper(
        DateTimeImmutable $dtBegin,
        DateTimeImmutable $dtEnd,
        IDayMapper $negativeMapper,
        ITimeIntervalFactory $factory
    )
    {
        /** @var CorporatePartyRepository $corporatePartyRepo */
        $corporatePartyRepo = $this->getDoctrine()->getRepository(CorporateParty::class);
        $parties = $corporatePartyRepo->findBetweenDates($dtBegin, $dtEnd);
        $previous = $negativeMapper;
        if (!empty($parties)) {
            foreach ($parties as $party) {
                /** @var CorporateParty $party */
                $dt = (new DateTimeImmutable())->setTimestamp($party->getDate())->modify('midnight');
                $interval = $factory->create($party->getStart(), $party->getLength(), ITimeInterval::UNITS_HOUR);
                $previous = new CorporatePartyDayMapper([$interval], $factory, $previous);
                $previous->setDate($dt);
            }
        }
        return $previous;
    }
}
