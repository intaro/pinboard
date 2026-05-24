<?php

declare(strict_types=1);

namespace App\Controller;

use Algo26\IdnaConvert\ToUnicode;
use App\Repository\IpmReportByServerNameRepository;
use App\Utils\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/', methods: ['GET'])]
    public function indexAction(IpmReportByServerNameRepository $ipmReportByServerNameRepository): Response
    {
        $result = [];

        $hostsRegexp = Utils::getUserHostsRegexp($this->getUser());
        $result['servers'] = $ipmReportByServerNameRepository->findAllServers($hostsRegexp);

        $idn = new ToUnicode();

        $total = [
            'req_count' => 0,
            'error_count' => 0
        ];

        foreach ($result['servers'] as &$item) {
            if (stripos($item['server_name'], 'xn--') !== false) {
                $item['server_name'] = $idn->convertUrl($item['server_name']);
            }

            $item['req_per_sec'] = number_format($item['req_per_sec'], 3, ',', '');

            $total['req_count'] += $item['req_count'];
            $total['error_count'] += $item['error_count'];
        }

        $result['total'] = $total;
        $result['base_url'] = '/';
        // Временно нижняя строка, тест
        $result['menu'] = (new BeforeController($this->entityManager))->actionBefore($hostsRegexp);

        return $this->render('index.html.twig', $result);
    }
}
