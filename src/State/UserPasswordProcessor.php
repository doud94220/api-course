<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/*
Instanciation (Conteneur de services) : Symfony gère le conteneur de services.
Dès qu'une requête arrive sur une opération concernant un User, Symfony crée (ou récupère) une instance de UserPasswordProcessor
 en lui injectant automatiquement les dépendances dont elle a besoin dans le constructeur ($persistProcessor et $passwordHasher).
*/

class UserPasswordProcessor implements ProcessorInterface
{
    //Truc inhabituel : les propriétés privées ne sont pas définis en dehorsdu constructeur, mais dedans !

    public function __construct(
        /*
        C'est un attribut PHP moderne. Il indique explicitement à Symfony : "Pour le paramètre qui suit, injecte-moi précisément
         le processeur natif d'API Platform qui gère l'enregistrement (persist) des entités en base de données via Doctrine".
        Sans cela, Symfony ne saurait pas quel processeur par défaut utiliser pour sauvegarder notre objet.
        */
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        /*
        C'est la syntaxe de promotion de propriété (fonctionnalité PHP 8).
        En écrivant cela dans le constructeur, PHP crée automatiquement une propriété privée $persistProcessor typée en ProcessorInterface
         et y stocke le service injecté à la ligne précédente.
        On en a besoin pour que, une fois notre mot de passe modifié, on puisse dire à Doctrine de finaliser l'enregistrement.
        */
        private ProcessorInterface $persistProcessor,
        /*
        C'est le service natif de Symfony indispensable pour hasher les mots de passe en toute sécurité.
        Contrairement au premier, pas besoin d'attribut #[Autowire] ici : grâce au type hint (UserPasswordHasherInterface),
         le système d'autodétection (autowiring) de Symfony reconnaît le type de service demandé et l'injecte tout seul.
        */
        private UserPasswordHasherInterface $passwordHasher
    )
    {
        //Constructeur vide, inhabituel !
    }

    /*
        Exécution : SF appelle automatiquement la méthode process(), ce qui permet d'intercepter la requête,
         de modifier le mot de passe, puis de passer le relais à Doctrine pour l'enregistrement final.
    */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        // dd($context); //DEBUG !

        // On vérifie qu'on manipule bien un User, et qu'il y a un mot de passe à hasher, et que la méthode HTTP est POST
        if ($data instanceof User && $data->getPassword() && $operation instanceof \ApiPlatform\Metadata\Post)
        {
            $hashedPassword = $this->passwordHasher->hashPassword($data, $data->getPassword());

            $data->setPassword($hashedPassword);
            
            // dd($data); //DEBUG !
        }

        // On délégue l'enregistrement réel à Doctrine (sauvegarde en BDD)
        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}