<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\HiddenField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{

    private UserPasswordHasherInterface $hasher;

    private Security $security;

    public function __construct(
        UserPasswordHasherInterface $hasher,
        Security $security

    ) {
        $this->hasher = $hasher;
        $this->security = $security;
    }
    public static function getEntityFqcn(): string
    {
        return User::class;
    }
    public function index(AdminContext $context)
    {
        // Check if the user has ROLE_ADMIN
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_login');
        }

        // Continue with the regular EasyAdmin dashboard rendering
        return parent::index($context);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Gebruikers')
            ->setPageTitle('new', 'Gebruiker aanmaken')
            ->setPageTitle('edit', 'Gebruiker bewerken')
            ->setPageTitle('detail', 'Gebruiker')
            ->setEntityPermission('ROLE_ADMIN')
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $requestPassword = Action::new('requestPassword', 'Wachtwoord versturen', 'fa fa-file')
            ->linkToCrudAction('sendPassword');
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::SAVE_AND_ADD_ANOTHER)
            ->add(Crud::PAGE_DETAIL, $requestPassword)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Nieuwe gebruiker');
            });
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('Name', 'Naam'),
            EmailField::new('Email', 'Email'),
            ChoiceField::new('roles', 'Rollen')->setChoices(['Gebruiker' => 'ROLE_USER', 'Medewerker' => 'ROLE_EMPLOYEE', 'Eigenaar' => 'ROLE_ADMIN'])->allowMultipleChoices(),
            TextField::new( 'NewPassword', 'Nieuw wachtwoord' )
                ->setFormType(PasswordType::class)
                ->setEmptyData( '' )
                ->onlyWhenUpdating()
                ->setRequired( false ),
            HiddenField::new( 'password', 'wachtwoord' )
                ->onlyWhenUpdating()
                ->setRequired( false ),
        ];
    }

    // Send password to user via user detail page
    public function sendPassword(AdminContext $context, MailerInterface $mailer, EntityManagerInterface $em)    {
        // Get user from Crud
        $user = $context->getEntity()->getInstance();
        // Generate password
        $password = bin2hex(random_bytes(6));
        // Encode password
        $encodedPassword = $this->hasher->hashPassword($user, $password);
        // Set user encoded password
        $user->setPassword($encodedPassword);
        // Upload to database
        $em->persist($user);
        $em->flush();
        // Setup email for user with new password
        $email = (new TemplatedEmail())
            ->from(new Address('lucaskindt77@gmail.com', 'Je haar zit goed!'))
            ->to($user->getEmail())
            ->subject('Wachtwoord verzoek')
            ->htmlTemplate('emails/pasword_email.html.twig')
            ->context([
                'password' => $password,
            ])
        ;
        // Send email
        $mailer->send($email);

        // redirect back to crud
        $url = $this->container->get(AdminUrlGenerator::class)
            ->setAction(Action::INDEX)
            ->setEntityId($context->getEntity()->getPrimaryKeyValue())
            ->generateUrl();

        return $this->redirect($url);
    }
}
