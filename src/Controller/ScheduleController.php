<?php


namespace App\Controller;


use App\Entity\CorporateParty;
use App\Entity\IDay;
use App\Entity\ITimeInterval;
use App\Entity\Vacation;
use App\Entity\WorkSchedule;
use App\Repository\CorporatePartyRepository;
use App\Repository\VacationRepository;
use App\Service\DayFilter;
use App\Service\DayMapHandler;
use App\Service\ICalendarApi;
use App\Service\IDayFactory;
use App\Service\IDayMapper;
use App\Service\ITimeIntervalFactory;
use App\Service\NegativeCorporatePartyDayMapper;
use App\Service\NegativeDayFilterHandler;
use App\Service\NegativeDayMapper;
use App\Service\PositiveCorporatePartyDayMapper;
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
    public function workSchedule(Request $request)
    {
        try {
            list ($dateBegin, $dateEnd, $staffId) = $this->getFromRequestParams($request);
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()]);
        }

        $days = $this->dayFactory->createDaysByRange($dateBegin, $dateEnd, $this->dayFactory::MODE_WITHOUT_WEEKENDS);

        try {
            $excludedDates = $this->getVacationDates($staffId, $dateBegin, $dateEnd);
        } catch (Exception $e) {
            return new JsonResponse(['error' => "({$e->getCode()}): {$e->getMessage()}"]);
        }

        $holidays = $this->calendarApi->getHolidays($dateBegin, $dateEnd);
        $excludedDates = array_merge($excludedDates, $holidays);

        $filter = new DayFilter($excludedDates);
        $filterHandler = new NegativeDayFilterHandler($filter);
        $days = $filterHandler->filter($days);

        try {
            list($workInterval, $lunchBreakInterval) = $this->getWorkSheduleIntervals($staffId);
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()]);
        }

        $positiveMapper = new PositiveDayMapper([$workInterval], $this->timeIntervalFactory);
        $negativeMapper = new NegativeDayMapper([$lunchBreakInterval], $this->timeIntervalFactory, $positiveMapper);

        $negativeMapper = $this->addPartiesMapper($dateBegin, $dateEnd, $negativeMapper, $this->timeIntervalFactory);

        $mapHandler = new DayMapHandler($negativeMapper);
        $days = $mapHandler->map($days);

        return new JsonResponse($days);
    }

    /**
     * @Route("/rest-schedule")
     * @param Request $request
     * @return JsonResponse
     */
    public function restSchedule(Request $request)
    {
        try {
            list ($dateBegin, $dateEnd, $staffId) = $this->getFromRequestParams($request);
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()]);
        }

        $fullDayInterval = $this->timeIntervalFactory->create('00:00', 24, ITimeInterval::UNITS_HOUR);

        $restDays = $this->dayFactory->createDaysByRange($dateBegin, $dateEnd, $this->dayFactory::MODE_WEEKENDS);
        $holidays = $this->calendarApi->getHolidays($dateBegin, $dateEnd);
        $restDays = $this->dayFactory->createFromDatesArray($holidays, $this->dayFactory::MODE_WITHOUT_WEEKENDS, $restDays);
        $restDayMapper = new PositiveDayMapper([$fullDayInterval], $this->timeIntervalFactory);

        $restMapHandler = new DayMapHandler($restDayMapper);
        $restDays = $restMapHandler->map($restDays);

        $workDays = $this->dayFactory->createDaysByRange($dateBegin, $dateEnd, $this->dayFactory::MODE_WITHOUT_WEEKENDS);
        $filter = new DayFilter($holidays);
        $filterHandler = new NegativeDayFilterHandler($filter);
        $workDays = $filterHandler->filter($workDays);

        try {
            list($workInterval, $lunchBreakInterval) = $this->getWorkSheduleIntervals($staffId);
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()]);
        }

        $mapper = new PositiveDayMapper([$fullDayInterval], $this->timeIntervalFactory);
        $mapper = new NegativeDayMapper(
            [$workInterval], $this->timeIntervalFactory, $mapper
        );
        $mapper = $this
            ->addPartiesMapper($dateBegin, $dateEnd, $mapper, $this->timeIntervalFactory, true);
        $mapper = new PositiveDayMapper(
            [$lunchBreakInterval], $this->timeIntervalFactory, $mapper
        );
        $dayMapHandler = new DayMapHandler($mapper);
        $days = array_merge($restDays, $dayMapHandler->map($workDays));

        usort($days, function ($dayA, $dayB) {
            /**
             * @var IDay $dayA
             * @var IDay $dayB
             */
            return $dayA->getDt()->getTimestamp() - $dayB->getDt()->getTimestamp();
        });

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
     * @param Request $request
     * @return array
     * @throws Exception
     */
    private function getFromRequestParams(Request $request)
    {
        if (!$request->query->get('startDate')) {
            throw new Exception("Parameter startDate require.");
        }
        if (!$request->query->get('endDate')) {
            throw new Exception("Parameter endDate require.");
        }
        if (!$request->query->get('userId')) {
            throw new Exception("Parameter userId require.");
        }

        return [
            DateTimeImmutable::createFromFormat(
                ITimeInterval::FORMAT_DATE,
                $request->query->get('startDate')
            )
                ->modify('midnight'),
            DateTimeImmutable::createFromFormat(
                ITimeInterval::FORMAT_DATE,
                $request->query->get('endDate')
            )
                ->modify('+1 day')
                ->modify('midnight -1 second'),
            $request->query->get('userId'),
        ];
    }

    private function addPartiesMapper(
        DateTimeImmutable $dtBegin,
        DateTimeImmutable $dtEnd,
        IDayMapper $previousMapper,
        ITimeIntervalFactory $factory,
        $workTime = false
    )
    {
        /** @var CorporatePartyRepository $corporatePartyRepo */
        $corporatePartyRepo = $this->getDoctrine()->getRepository(CorporateParty::class);
        $parties = $corporatePartyRepo->findBetweenDates($dtBegin, $dtEnd);
        $previous = $previousMapper;
        if (!empty($parties)) {
            foreach ($parties as $party) {
                /** @var CorporateParty $party */
                $dt = (new DateTimeImmutable())->setTimestamp($party->getDate())->modify('midnight');
                $interval = $factory->create($party->getStart(), $party->getLength(), ITimeInterval::UNITS_HOUR);
                if ($workTime) {
                    $previous = new PositiveCorporatePartyDayMapper([$interval], $factory, $previous);
                } else {
                    $previous = new NegativeCorporatePartyDayMapper([$interval], $factory, $previous);
                }
                $previous->setDate($dt);
            }
        }
        return $previous;
    }

    /**
     * @param $staffId
     * @return array
     * @throws Exception
     */
    private function getWorkSheduleIntervals($staffId): array
    {
        $result = [];

        $workSchedulesRepo = $this->getDoctrine()->getRepository(WorkSchedule::class);
        /** @var WorkSchedule $workSchedule */
        $workSchedule = $workSchedulesRepo->findOneBy(['staff' => $staffId]);

        if (empty($workSchedule)) {
            throw new Exception("Work schedule for staff with id: {$staffId} not exists.");
        }
        /** @var ITimeInterval $workInterval */
        $result[] = $this->timeIntervalFactory->create(
            $workSchedule->getWorkDayStart(),
            $workSchedule->getWorkDayLength()
        );
        /** @var ITimeInterval $lunchBreakInterval */
        $result[] = $this->timeIntervalFactory->create(
            $workSchedule->getLunchBreakStart(),
            $workSchedule->getLunchBreakLength()
        );
        return $result;
    }
}
