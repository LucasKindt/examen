<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Entity\Treatment;
use App\Repository\OrderRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Google\Client;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\User;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use Google\Service\AnalyticsData;
use Google\Service\AnalyticsData\RunReportRequest;
use Google\Service\AnalyticsData\Dimension;
use Google\Service\AnalyticsData\Metric;
use Google\Service\AnalyticsData\DateRange;

class DashboardController extends AbstractDashboardController
{
    private array $months;

    public function __construct(private ChartBuilderInterface $chartBuilder, private OrderRepository $orderRepository, private Security $security, private AdminUrlGenerator $adminUrlGenerator)
    {
        $this->months = ['Januari', 'Februari', 'Maart', 'April', 'Mei', 'Juni', 'Juli', 'Augustus', 'September', 'Oktober', 'November', 'December'];
    }

    public function configureAssets(): Assets
    {

        return parent::configureAssets()->addWebpackEncoreEntry('app', false);

    }

    #[Route('/beheer', name: 'admin')]
    public function index(): Response
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            if($this->security->isGranted('ROLE_EMPLOYEE'))
            {
                    return $this->redirect($this->adminUrlGenerator
                        ->setController(OrderCrudController::class)
                        ->generateUrl());
            }

            return $this->redirectToRoute('app_login');

        }

        $request = $this->container->get('request_stack')->getCurrentRequest(); // Access request manually

        $year = $request->query->get('year', (new \DateTime())->format('Y')); // Default to the current year

        // Fetch visitor data for the selected year
        $analytics = $this->initializeAnalyticsClient();
        $visitorData = $this->getYearlyVisitorsData($analytics, $year);

        $visitorChart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $visitorChart->setData([
            'labels' => array_keys($visitorData),
            'datasets' => [
                [
                    'label' => "Bezoekers per maand ($year)",
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'data' => array_values($visitorData),
                ],
            ],
        ]);

        // Fetch revenue data for the selected year
        $monthlyRevenue = $this->getYearlyRevenueData($year);

        $revenueChart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $revenueChart->setData([
            'labels' => array_keys($monthlyRevenue),
            'datasets' => [
                [
                    'label' => "Omzet ($year)",
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'data' => array_values($monthlyRevenue),
                ],
            ],
        ]);

        return $this->render('admin/index.html.twig', [
            'visitorChart' => $visitorChart,
            'revenueChart' => $revenueChart,
            'selectedYear' => $year,
            'years' => range(date('Y'), date('Y') - 10), // Last 5 years
        ]);
    }
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<img src="/images/logo/favicon-32x32.png">  Je haar zit goed!')
            ->setFaviconPath('/images/logo/favicon.ico')
            ->setDefaultColorScheme('dark');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home')
            ->setPermission('ROLE_ADMIN');
        yield MenuItem::LinktoCrud('Medewerkers', 'fa fa-user', User::class)
            ->setPermission('ROLE_ADMIN');
        yield MenuItem::LinktoCrud('Producten', 'fa fa-star', Product::class)
            ->setPermission('ROLE_ADMIN');
        yield MenuItem::LinktoCrud('Behandelingen', 'fa fa-scissors', Treatment::class)
            ->setPermission('ROLE_ADMIN');
        yield MenuItem::LinktoCrud('Bestellingen', 'fa fa-shopping-cart', Order::class)
            ->setPermission('ROLE_EMPLOYEE');
        yield MenUitem::section('<hr>');
        yield MenuItem::LinktoCrud('Categorieën', 'fa fa-user', ProductCategory::class)
            ->setPermission('ROLE_ADMIN');
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        return parent::configureUserMenu($user)
            // use the given $user object to get the user name
            ->setName($user->getEmail())
            // use this method if you don't want to display the name of the user
            ->displayUserName(false);
    }

    /**
     * Initializes and configures the Google Analytics client.
     *
     * @return AnalyticsData The configured Google Analytics Data client.
     * @throws \RuntimeException If the credentials file is missing or invalid.
     */
    private function initializeAnalyticsClient(): AnalyticsData
    {

        // Initialize the Google Client
        $client = new Client();
        // Load the credentials from the `credentials.json` file
        $client->setAuthConfig(json_decode(file_get_contents('cred.json'), true));
        // Add the Analytics Read-only scope
        $client->addScope(\Google\Service\AnalyticsData::ANALYTICS_READONLY);

        // Return the AnalyticsData service
        return new \Google\Service\AnalyticsData($client);
    }

    /**
     * Fetches monthly visitor data for a specific year from Google Analytics.
     *
     * @param AnalyticsData $analytics The Google Analytics Data client.
     * @param string $year The year for which to retrieve the data.
     * @return array An associative array with months as keys and visitor counts as values.
     */
    private function getYearlyVisitorsData(AnalyticsData $analytics, string $year): array
    {
        // Initialize all months with zero values
        $monthlyVisitors = array_fill_keys(
            $this->months,
            0
        );

        // Use the provided year to set the date range
        $dateRange = new DateRange();
        $dateRange->setStartDate("$year-01-01");
        $dateRange->setEndDate("$year-12-31");

        // Metrics and dimensions
        $sessions = new Metric();
        $sessions->setName("activeUsers");

        $dimensions = new Dimension();
        $dimensions->setName("month");

        // Create and execute the request
        $propertyId = $_ENV['GOOGLE_ANALYTICS_PROPERTY_ID'];
        $request = new RunReportRequest();
        $request->setProperty("properties/$propertyId");
        $request->setDateRanges([$dateRange]);
        $request->setMetrics([$sessions]);
        $request->setDimensions([$dimensions]);

        $response = $analytics->properties->runReport("properties/$propertyId", $request);

        // Process response data
        foreach ($response->getRows() as $row) {
            $monthIndex = (int)$row->getDimensionValues()[0]->getValue() - 1; // Convert month to zero-based index
            $sessions = (int)$row->getMetricValues()[0]->getValue();

            // Update the corresponding month in the array
            $monthName = array_keys($monthlyVisitors)[$monthIndex];
            $monthlyVisitors[$monthName] = $sessions;
        }

        return $monthlyVisitors;
    }


    /**
     * Fetches monthly revenue data for a specific year from the order repository.
     *
     * @param string $year The year for which to retrieve the revenue data.
     * @return array An associative array with months as keys and revenue amounts as values.
     */
    private function getYearlyRevenueData(string $year): array
    {
        // Initialize all months with zero values
        $monthlyRevenue = array_fill_keys(
            $this->months,
            0
        );

        // Fetch orders for the given year
        $orders = $this->orderRepository->getOrdersForYear($year);

        foreach ($orders as $order) {
            if ($order->getStatus() === 'paid') {
                $monthIndex = (int)$order->getDate()->format('n') - 1; // Convert month to zero-based index

                // Calculate revenue for the order
                $monthName = array_keys($monthlyRevenue)[$monthIndex];
                foreach ($order->getOrderProducts() as $orderProduct) {
                    $productPrice = $orderProduct->getProduct()->getPrice() / 100;
                    $quantity = $orderProduct->getAmount();
                    $monthlyRevenue[$monthName] += $productPrice * $quantity;
                }
            }
        }

        return $monthlyRevenue;
    }

}
