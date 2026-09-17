<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\User;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
#[ApiResource(
    paginationEnabled: true,
    paginationItemsPerPage: 20,
    order: ['sentAt' => 'DESC'],
    normalizationContext: ['groups' => ['invoices_read']],
    operations: [//On liste les opérations utilisées => Dans la swagger, on ne vera que les opérations listées ci-dessous
        new Get(),
        new Post(),
        new GetCollection(
            uriTemplate: 'customers/{id}/invoices', //Nouvelle URL ! Sinon /api est déjà ajouté automatiquement par APIP selon la config globale
            uriVariables: [
                            // 'id' correspond au paramètre {id} de l'URL
                            // fromClass indique que l'on vient de Customer
                            // toProperty indique la propriété 'customer' dans Invoice, car c'est elle qui permet de remonter jusqu'au customer
                            'id' => new Link(fromClass: Customer::class, toProperty: 'customer')
                          ],
            normalizationContext: ['groups' => ['invoices_subresource']]
        ),
        new Delete(),
        new Patch(),
        new Put()
    ]
)]
#[ApiFilter(OrderFilter::class, properties: ["amount", "sentAt"])]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['invoices_read', 'customers_read', 'invoices_subresource'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['invoices_read', 'customers_read', 'invoices_subresource'])]
    private ?float $amount = null;

    #[ORM\Column]
    #[Groups(['invoices_read', 'customers_read', 'invoices_subresource'])]    
    private ?\DateTime $sentAt = null;

    #[ORM\Column(length: 255)]
    #[Groups(['invoices_read', 'customers_read', 'invoices_subresource'])]  
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['invoices_read'])]    
    private ?Customer $customer = null;

    #[ORM\Column]
    #[Groups(['invoices_read', 'customers_read', 'invoices_subresource'])]   
    private ?int $chrono = null;

    /**
     * Permet de récupérer le User à qui appartient la facture
     *
     * @return User
     */
    #[Groups(['invoices_read', 'invoices_subresource'])]
    public function getUser() : User
    {
        return $this->customer->getUser();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getSentAt(): ?\DateTime
    {
        return $this->sentAt;
    }

    public function setSentAt(\DateTime $sentAt): static
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function getChrono(): ?int
    {
        return $this->chrono;
    }

    public function setChrono(int $chrono): static
    {
        $this->chrono = $chrono;

        return $this;
    }
}
