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

        $viewServers = [];
        foreach ($result['servers'] as $serverRow) {
            $serverName = $serverRow['server_name'];
            if (stripos($serverName, 'xn--') !== false) {
                $serverName = $idn->convertUrl($serverName);
            }

            $total['req_count'] += $serverRow['req_count'];
            $total['error_count'] += $serverRow['error_count'];

            $viewServers[] = [
                'server_name' => $serverName,
                'req_per_sec' => number_format($serverRow['req_per_sec'], 3, ',', ''),
                'req_count'   => $serverRow['req_count'],
                'error_count' => $serverRow['error_count'],
            ];
        }
        $result['servers'] = $viewServers;

        $result['total'] = $total;
        $result['menu'] = (new BeforeController($this->entityManager))->actionBefore($hostsRegexp);

        return $this->render('index.html.twig', $result);
    }
}
