<?php

namespace App\Domain\Users\Entities;
use App\Domain\Users\ValueObjects\Phone;

class User
{
    private string $id;
    private string $type;
    private Phone $phone;
    private Phone $whatsApp;
    private ?string $email;
    private string $passwordHash;

    //Customer
    private string $customerId;
    private ?string $firstName;
    private ?string $lastName;
    private ?string $gender;

    //Partner & Enterprise
    private string $partnerId;
    private ?string $name;
    private string $enterpriseId;


    public function __construct(string $id, string $type, ?string $firstName, ?string $lastName, ?string $gender, Phone $phone, Phone $whatsApp, string $email, string $passwordHash, ?string $name, ?string $customerId, ?string $partnerId, ?string $enterpriseId)
    {
       $this->firstName = $firstName;
       $this->lastName = $lastName;
       $this->gender = $gender;
       $this->phone = $phone;
       $this->whatsApp = $whatsApp;
       $this->email = $email;
       $this->passwordHash = $passwordHash;
       $this->type = $type;
       $this->id = $id;
       $this->name = $name;

       $this->customerId = $customerId;
       $this->partnerId = $partnerId;
       $this->enterpriseId = $enterpriseId;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
       return $this->firstName;
    }

    public function getLastName(): string
    {
       return $this->lastName;
    }

    /**
     * @return string|null
     */
    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function getPhone(): Phone
    {
       return $this->phone;
    }

    public function getWhatsApp(): Phone
    {
        return $this->whatsApp;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPartnerId(): string
    {
        return $this->partnerId;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function getEnterpriseId(): string
    {
        return $this->enterpriseId;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

}
