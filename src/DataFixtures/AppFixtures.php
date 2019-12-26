<?php

namespace App\DataFixtures;

use App\Entity\CorporateParty;
use App\Entity\ITimeInterval;
use App\Entity\Staff;
use App\Entity\Vacation;
use App\Entity\WorkSchedule;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $this->addCorporateParty($manager);
        $this->addStaff($manager);
        $this->addWorkSchedule($manager);
        $this->addVacation($manager);
        $manager->flush();
    }

    private function addCorporateParty(ObjectManager $em)
    {
        $corporateParties = [
            [
                'date' => (DateTimeImmutable::createFromFormat(ITimeInterval::FORMAT_DATE, '2018-12-31'))
                    ->modify('midnight')
                    ->getTimestamp(),
                'start' => '15:00',
                'length' => 9,
            ],
            [
                'date' => (DateTimeImmutable::createFromFormat(ITimeInterval::FORMAT_DATE, '2019-12-30'))
                    ->modify('midnight')
                    ->getTimestamp(),
                'start' => '16:00',
                'length' => 8,
            ],
            [
                'date' => (DateTimeImmutable::createFromFormat(ITimeInterval::FORMAT_DATE, '2020-12-29'))
                    ->modify('midnight')
                    ->getTimestamp(),
                'start' => '12:00',
                'length' => 12,
            ],
        ];
        foreach ($corporateParties as $corporateParty) {
            $party = new CorporateParty();
            $party->setDate($corporateParty['date']);
            $party->setStart($corporateParty['start']);
            $party->setLength($corporateParty['length']);
            $em->persist($party);
        }
    }

    private function addStaff(ObjectManager $em)
    {
        $users = [
            ['first_name' => 'Test', 'last_name' => 'UserOne',],
            ['first_name' => 'Test', 'last_name' => 'UserTwo',],
            ['first_name' => 'Test', 'last_name' => 'UserThree',],
        ];
        foreach ($users as $index => $user) {
            $staff = new Staff();
            $staff->setFirstName($user['first_name']);
            $staff->setLastName($user['last_name']);
            $em->persist($staff);
            $this->addReference("staff_{$index}", $staff);
        }
    }


    private function addWorkSchedule(ObjectManager $em)
    {
        $schedules = [
            [
                'work_day_start' => '06:00',
                'work_day_length' => 510,
                'lunch_break_start' => '12:00',
                'lunch_break_length' => 30,
            ],
            [
                'work_day_start' => '08:00',
                'work_day_length' => 540,
                'lunch_break_start' => '12:00',
                'lunch_break_length' => 60,
            ],
            [
                'work_day_start' => '12:00',
                'work_day_length' => 540,
                'lunch_break_start' => '16:00',
                'lunch_break_length' => 60,
            ],
        ];
        foreach ($schedules as $index => $schedule) {
            $workSchedule = new WorkSchedule();
            $workSchedule->setWorkDayStart($schedule['work_day_start']);
            $workSchedule->setWorkDayLength($schedule['work_day_length']);
            $workSchedule->setLunchBreakStart($schedule['lunch_break_start']);
            $workSchedule->setLunchBreakLength($schedule['lunch_break_length']);
            /** @var Staff $staff */
            $staff = $this->getReference("staff_{$index}");
            $workSchedule->setStaff($staff);
            $em->persist($workSchedule);
        }
    }

    private function addVacation(ObjectManager $em)
    {
        $vacations = [
            [
                [
                    'start' =>
                        (DateTimeImmutable::createFromFormat(ITimeInterval::FORMAT_DATE, '2019-02-01'))
                            ->modify('midnight')
                            ->getTimestamp(),
                    'length' => 25,
                ],
                [
                    'start' =>
                        (DateTimeImmutable::createFromFormat(ITimeInterval::FORMAT_DATE, '2019-10-15'))
                            ->modify('midnight')
                            ->getTimestamp(),
                    'length' => 5,
                ],
            ],
            [
                [
                    'start' =>
                        (DateTimeImmutable::createFromFormat(ITimeInterval::FORMAT_DATE, '2019-05-01'))
                            ->modify('midnight')
                            ->getTimestamp(),
                    'length' => 15,
                ],
                [
                    'start' =>
                        (DateTimeImmutable::createFromFormat(ITimeInterval::FORMAT_DATE, '2019-11-01'))
                            ->modify('midnight')
                            ->getTimestamp(),
                    'length' => 15,
                ],
            ],
            [
                [
                    'start' =>
                        (DateTimeImmutable::createFromFormat(ITimeInterval::FORMAT_DATE, '2019-03-01'))
                            ->modify('midnight')
                            ->getTimestamp(),
                    'length' => 10,
                ],
                [
                    'start' =>
                        (DateTimeImmutable::createFromFormat(ITimeInterval::FORMAT_DATE, '2019-06-01'))
                            ->modify('midnight')
                            ->getTimestamp(),
                    'length' => 20,
                ],
            ],
        ];
        foreach ($vacations as $index => $staffVacations) {
            /** @var Staff $staff */
            $staff = $this->getReference("staff_{$index}");
            foreach ($staffVacations as $staffVacation) {
                $vacation = new Vacation();
                $vacation->setStart($staffVacation['start']);
                $vacation->setLength($staffVacation['length']);
                $vacation->setStaff($staff);
                $em->persist($vacation);
            }
        }
    }
}
