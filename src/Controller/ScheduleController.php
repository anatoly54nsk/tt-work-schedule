<?php


namespace App\Controller;


use App\Entity\IDay;
use App\Entity\ITimeInterval;
use App\Entity\Vacation;
use App\Entity\WorkSchedule;
use App\Repository\VacationRepository;
use App\Service\DayFilter;
use App\Service\DayMapHandler;
use App\Service\IDayFactory;
use App\Service\ITimeIntervalFactory;
use App\Service\NegativeDayFilterHandler;
use App\Service\NegativeDayMapper;
use App\Service\PositiveDayMapper;
use DateTimeImmutable;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    public function __construct(IDayFactory $dayFactory, ITimeIntervalFactory $timeIntervalFactory)
    {
        $this->dayFactory = $dayFactory;
        $this->timeIntervalFactory = $timeIntervalFactory;
    }

    /**
     * @Route("/schedule")
     */
    public function index()
    {
        $dateBegin = '2019-04-01';
        $dateEnd = '2019-09-01';
        $staffId = 2;

        $dtBegin = DateTimeImmutable::createFromFormat(ITimeInterval::FORMAT_DATE, $dateBegin)
            ->modify('midnight');
        $dtEnd = DateTimeImmutable::createFromFormat(ITimeInterval::FORMAT_DATE, $dateEnd)
            ->modify('midnight');

        $days = $this->getDays($dtBegin, $dtEnd);

        try {
            $excludedDates = $this->getVacationDates($staffId, $dtBegin, $dtEnd);
        } catch (Exception $e) {
            return new JsonResponse(['error' => "({$e->getCode()}): {$e->getMessage()}"]);
        }

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

        $filter = new DayFilter($excludedDates);
        $filterHandler = new NegativeDayFilterHandler($filter);
        $days = $filterHandler->filter($days);

        $positiveMapper = new PositiveDayMapper([$workInterval], $this->timeIntervalFactory);
        $negativeMapper = new NegativeDayMapper([$lunchBreakInterval], $this->timeIntervalFactory, $positiveMapper);
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
     * @return array
     */
    private function getDays(
        DateTimeImmutable $dtBegin,
        DateTimeImmutable $dtEnd,
        array $days = []
    ): array
    {
        if ($dtBegin->getTimestamp() <= $dtEnd->getTimestamp()) {
            $start = $dtBegin;
            $end = $dtEnd->getTimestamp();
            while ($start->getTimestamp() <= $end) {
                $days[] = $this->dayFactory->create($start);
                $start = $start->modify('+1 day');
            }
        }
        return $days;
    }
}
