<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\OrderProductType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\Image;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
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
            ->setPageTitle('index', 'Producten')
            ->setPageTitle('new', 'Product aanmaken')
            ->setPageTitle('edit', 'Product bewerken')
            ->setPageTitle('detail', 'Product')
            ->setEntityPermission('ROLE_ADMIN')
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::SAVE_AND_ADD_ANOTHER)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Nieuw Product');
            })
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Naam')
            ,MoneyField::new('price', 'Prijs')->setCurrency('EUR')
            ,TextEditorField::new('description', 'Beschrijving')
            ,NumberField::new('stock', 'Voorraad')
            ,ImageField::new('image', 'Afbeelding')
                ->setUploadDir('public/images/products/')
                ->setBasePath('images/products/')
                ->setRequired($pageName === Crud::PAGE_NEW)
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setFileConstraints(new Image([
                    'maxHeight' => 300,
                    'maxWidth' => 300
                ]))
            ,AssociationField::new('productCategories', 'Categorieën')->autocomplete()->setFormTypeOptionIfNotSet('by_reference', false)
        ];
    }
}
