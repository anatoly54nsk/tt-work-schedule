<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\WorkScheduleRepository")
 */
class WorkSchedule
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $work_day_start;

    /**
     * @ORM\Column(type="integer")
     */
    private $work_day_length;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $lunch_break_start;

    /**
     * @ORM\Column(type="integer")
     */
    private $lunch_break_length;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Staff", inversedBy="workSchedule", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $staff;

    public function getWorkDayStart(): ?string
    {
        return $this->work_day_start;
    }

    public function setWorkDayStart(string $work_day_start): self
    {
        $this->work_day_start = $work_day_start;

        return $this;
    }

    public function getWorkDayLength(): ?int
    {
        return $this->work_day_length;
    }

    public function setWorkDayLength(int $work_day_length): self
    {
        $this->work_day_length = $work_day_length;

        return $this;
    }

    public function getLunchBreakStart(): ?string
    {
        return $this->lunch_break_start;
    }

    public function setLunchBreakStart(string $lunch_break_start): self
    {
        $this->lunch_break_start = $lunch_break_start;

        return $this;
    }

    public function getLunchBreakLength(): ?int
    {
        return $this->lunch_break_length;
    }

    public function setLunchBreakLength(int $lunch_break_length): self
    {
        $this->lunch_break_length = $lunch_break_length;

        return $this;
    }

    public function getStaff(): ?Staff
    {
        return $this->staff;
    }

    public function setStaff(Staff $staff): self
    {
        $this->staff = $staff;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }
}
