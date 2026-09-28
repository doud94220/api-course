<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Customer;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Security;

class CustomerUserProcessor implements ProcessorInterface
{
    private $security;

    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        Security $security
    )
    {
        $this->security = $security;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof Customer && $operation instanceof \ApiPlatform\Metadata\Post)
        {
            $data->setUser($this->security->getUser());

            return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        }
    }
}