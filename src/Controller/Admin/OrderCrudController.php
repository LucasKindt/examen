<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Form\OrderProductType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\HiddenField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    private Security $security;

    public function __construct(
        Security $security

    ) {
        $this->security = $security;
    }

    public function index(AdminContext $context)
    {
        if (!$this->security->isGranted('ROLE_EMPLOYEE')) {
            return $this->redirectToRoute('app_login');
        }

        // Continue with the regular EasyAdmin dashboard rendering
        return parent::index($context);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Bestellingen')
            ->setPageTitle('edit', 'Bestelling bewerken')
            ->setPageTitle('new', 'Bestelling aanmaken')
            ->setPageTitle('detail', 'Bestelling')
            ->setEntityPermission('ROLE_EMPLOYEE')
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::SAVE_AND_ADD_ANOTHER)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Nieuwe Bestelling');
            })
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            TextField::new('Name', 'Naam'),
            EmailField::new('email', 'Emailadres'),
            TelephoneField::new('phone', 'Telefoonnummer'),
            DateTimeField::new('date', 'Besteldatum'),
            TextField::new('number', 'Bestelnummer')->HideOnForm(),
            ChoiceField::new('status')
                ->setChoices([
                    'Betaald' => 'paid',
                    'Klaar om af te halen' => 'ready',
                    'In-behandeling' => 'pending',
                    'Geannuleerd' => 'canceled',
                ]),
            CollectionField::new('orderProducts', 'Producten')
                ->setEntryType(OrderProductType::class) // Custom form for OrderProduct
                ->allowAdd()
                ->allowDelete()
                ->renderExpanded(true)
                ->onlyOnForms(),
            MoneyField::new('total', 'Totale Prijs')
                ->setCurrency('EUR')
                ->hideOnForm(), // or remove to show on all pages

        ];

        if ($pageName === 'detail') {
            $fields[] = CollectionField::new('orderProducts', 'Producten')
                ->setTemplatePath('admin/order/order_products_detail.html.twig'); // Use custom template
        }

        return $fields;
    }
}
