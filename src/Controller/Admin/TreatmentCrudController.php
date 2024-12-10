<?php

namespace App\Controller\Admin;

use App\Entity\Treatment;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bundle\SecurityBundle\Security;

class TreatmentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Treatment::class;
    }

    private Security $security;

    public function __construct(
        Security $security

    ) {
        $this->security = $security;
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
            ->setPageTitle('index', 'Behandelingen')
            ->setPageTitle('new', 'Behandeling aanmaken')
            ->setPageTitle('edit', 'Behandeling bewerken')
            ->setPageTitle('detail', 'Behandeling')
            ->setEntityPermission('ROLE_ADMIN')
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::SAVE_AND_ADD_ANOTHER)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Nieuwe behandeling');
            })
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Naam'),
            TextareaField::new('description', 'Beschrijving'),
            ImageField::new('image', 'Afbeelding')
                ->setUploadDir('public/images/treatments/')
                ->setBasePath('images/treatments/')
                ->setRequired($pageName === Crud::PAGE_NEW)
        ->setUploadedFileNamePattern('[randomhash].[extension]'),
            MoneyField::new('price', 'Prijs')->setCurrency('EUR'),
        ];
    }
}
