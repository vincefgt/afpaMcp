<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\RouterInterface;

class ExportRoutesInFileCommand extends Command
{
    const CSV_PATH = '/var/www/html/var/log/export-routes.csv';

    const CSV_HEADERS = ['path', 'methods', 'parameters', 'service Flex', 'function Flex'];

    const ROUTE_DEFAULT_SERVICE_NAME = 'api-service';

    const ROUTE_DEFAULT_FUNCTION_NAME = 'api-function';

    const ROUTE_DEFAULT_PARAMETERS_NAME = 'parameters';

    private $router;

    protected static $defaultName = 'app:export-routes';

    /**
     * ExportRoutesCommand constructor.
     *
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        parent::__construct();

        $this->router = $router;
    }

    /**
     * Add configuration
     */
    protected function configure(): void
    {
        $this->setDescription('Export all routes.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $input->validate();

        $output->writeln('Begin export');

        $routes = $this->router->getRouteCollection()->all();

        $file = fopen(self::CSV_PATH, 'w');
        fputcsv($file, self::CSV_HEADERS);

        foreach ($routes as $route) {
            if (!empty($route->getDefault(self::ROUTE_DEFAULT_SERVICE_NAME))) {
                $methods = implode(',', $route->getMethods());
                $parameter = $route->getDefault(self::ROUTE_DEFAULT_PARAMETERS_NAME);
                $parameters = (empty($parameter) || !is_array($parameter)) ? '' : implode(',', $parameter);
                fputcsv($file, [
                    $route->getPath(),
                    $methods,
                    $parameters,
                    $route->getDefault(self::ROUTE_DEFAULT_SERVICE_NAME),
                    $route->getDefault(self::ROUTE_DEFAULT_FUNCTION_NAME)
                ]);
            }
        }
        fclose($file);

        $output->writeln('End export');
        return 0;
    }
}
