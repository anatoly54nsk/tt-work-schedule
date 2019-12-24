<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StaffRepository")
 */
class Staff
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $first_name;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $last_name;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\WorkSchedule", mappedBy="staff", cascade={"persist", "remove"})
     */
    private $workSchedule;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Vacation", mappedBy="staff", orphanRemoval=true)
     */
    private $vacations;

    public function __construct()
    {
        $this->vacations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): self
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): self
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getWorkSchedule(): ?WorkSchedule
    {
        return $this->workSchedule;
    }

    public function setWorkSchedule(WorkSchedule $workSchedule): self
    {
        $this->workSchedule = $workSchedule;

        // set the owning side of the relation if necessary
        if ($workSchedule->getStaff() !== $this) {
            $workSchedule->setStaff($this);
        }

        return $this;
    }

    /**
     * @return Collection|Vacation[]
     */
    public function getVacations(): Collection
    {
        return $this->vacations;
    }

    public function addVacation(Vacation $vacation): self
    {
        if (!$this->vacations->contains($vacation)) {
            $this->vacations[] = $vacation;
            $vacation->setStaff($this);
        }

        return $this;
    }

    public function removeVacation(Vacation $vacation): self
    {
        if ($this->vacations->contains($vacation)) {
            $this->vacations->removeElement($vacation);
            // set the owning side to null (unless already changed)
            if ($vacation->getStaff() === $this) {
                $vacation->setStaff(null);
            }
        }

        return $this;
    }
}
