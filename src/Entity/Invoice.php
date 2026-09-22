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
use ApiPlatform\OpenApi\Model\Operation;
use App\Controller\InvoiceIncrementationController;
use App\Entity\User;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
#[ApiResource(
    paginationEnabled: true,
    paginationItemsPerPage: 20,
    order: ['sentAt' => 'DESC'],
    normalizationContext: ['groups' => ['invoices_read']],
    denormalizationContext: [
        'groups' => ['invoices_write'],
        'disable_type_enforcement' => true
    ],
    operations: [//On liste les opérations utilisées => Dans la swagger, on ne vera que les opérations listées ci-dessous
        new Get(),
        new Post(
        ),
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
//Nouveau path qui servira à incrémenter le chrono d'une facture donnée
#[ApiResource(operations: [
    new Post(
            uriTemplate: '/invoices/{id}/increment',
            controller: InvoiceIncrementationController::class,
            name: 'invoice_increment',
            read: true, // API Platform récupère automatiquement l'entité Invoice grâce à l'{id} de l'URL
            openapi: new Operation(//On enrichie les informations qui seront affichées dans la Swagger Interface
                summary: 'Incrémente le numéro de facture',
                description: "Incrémente le chrono d'une facture donnée"
            )
        )
    ],
    normalizationContext: ['groups' => ['invoices_read']]
)]
#[ApiFilter(OrderFilter::class, properties: ["amount", "sentAt"])]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['invoices_read', 'customers_read', 'invoices_subresource', 'invoices_write'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['invoices_read', 'customers_read', 'invoices_subresource', 'invoices_write'])]
    #[Assert\NotBlank(message: "Le montant de la facture est obligatoire.")]
    #[Assert\Type(
        type: 'numeric',
        message: 'Le montant de la facture doit être un numérique.',
    )]
    private $amount = null; //Je retire ?float pour que ce soit le Assert\Type() qui déclenche l'erreur

    #[ORM\Column]
    #[Groups(['invoices_read', 'customers_read', 'invoices_subresource', 'invoices_write'])]
    #[Assert\Date(message: "La date doit être au format YYYY-MM-DD.")]
    #[Assert\NotBlank(message: "La date d'envoie est obligatoire.")]
    private $sentAt = null; //Je retire ?\DateTime pour que ce soit le Assert\Type() qui déclenche l'erreur

    #[ORM\Column(length: 255)]
    #[Groups(['invoices_read', 'customers_read', 'invoices_subresource', 'invoices_write'])]
    #[Assert\NotBlank(message: "Le statut est obligatoire.")]
    #[Assert\Choice(choices: ['SENT', 'PAID', 'CANCELLED'], message: 'Le statut doit être SENT ou PAID ou CANCELLED')]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['invoices_read', 'invoices_write'])]
    #[Assert\NotBlank(message: "Le customer de la fature est obligatoire.")]
    private ?Customer $customer = null;

    #[ORM\Column]
    #[Groups(['invoices_read', 'customers_read', 'invoices_subresource', 'invoices_write'])]
    #[Assert\NotBlank(message: "Le chrono est obligatoire.")]
    #[Assert\Type(
        type: 'integer',
        message: 'Le chrono doit être un integer.',
    )]
    private $chrono = null; //Je retire ?int pour que ce soit le Assert\Type() qui déclenche l'erreur

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

    public function setAmount($amount): static //Je retire float juste avant $amount pour que ce soit le Assert\Type() qui déclenche l'erreur
    {
        $this->amount = $amount;

        return $this;
    }

    public function getSentAt()//: ?\DateTime //JE COMMENTE POUR VOIR //Sinon je suis passé en Date, et non plus DateTime
    {
        return $this->sentAt;
    }

    public function setSentAt($sentAt): static //Je retire \DateTime juste avant $sentAt pour que ce soit le Assert\Type() qui déclenche l'erreur
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

    public function setChrono($chrono): static //Je retire int juste avant $amount pour que ce soit le Assert\Type() qui déclenche l'erreur
    {
        $this->chrono = $chrono;

        return $this;
    }
}
