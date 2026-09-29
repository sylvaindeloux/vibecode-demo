<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProfileRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The freelancer's identity, printed on every CRA. The table holds at most one row.
 */
#[ORM\Entity(repositoryClass: ProfileRepository::class)]
final class Profile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Indiquez votre nom.')]
    #[Assert\Length(max: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $company = null;

    /** The 14 digits only: spaces are handled by the form (BR-16). */
    #[ORM\Column(length: 14)]
    #[Assert\Sequentially([
        new Assert\NotBlank(message: 'Le SIRET compte 14 chiffres.'),
        new Assert\Regex(pattern: '/^\d{14}$/', message: 'Le SIRET compte 14 chiffres.'),
        new Assert\Luhn(message: 'Ce numéro SIRET n’est pas valide : vérifiez les chiffres saisis.'),
    ])]
    private ?string $siret = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Indiquez votre adresse.')]
    private ?string $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Email(message: 'Cette adresse e-mail n’est pas valide (exemple : prenom@societe.fr).')]
    #[Assert\Length(max: 255)]
    private ?string $email = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): void
    {
        $this->company = $company;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(?string $siret): void
    {
        $this->siret = $siret;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }
}
