<?php

namespace Hyperf\Zby\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Schema\Schema;
use Hyperf\Zby\Helper\Str;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;

#[Command]
class GenBeanCommand extends JwtCommand
{

    /**
     * @var ContainerInterface
     */
    private $container;

    protected ?string $name = "zby:gen-bean";


    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct('gen:bean');
    }

    protected function configure()
    {
        $this->setDescription('Generate Bean from database table')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the table');
    }

    public function handle()
    {
        $tableName = $this->input->getArgument('name');
        $className = Str::studly($tableName) . 'Bean';
        $resolver = $this->container->get(ConnectionResolverInterface::class);


        $connection = $resolver->connection();
        $columns = Schema::getColumnListing($tableName);

        $prefix = \Hyperf\Support\env('DB_PREFIX');

        $columnTypes = [];
        foreach ($columns as $column) {
            $columnType = Schema::getColumnType($tableName, $column);
            $columnTypes[$column] = $columnType;
        }


        $fileContent = "<?php\n\nnamespace App\Bean;\n\n";
        $fileContent .= "class {$className}\n{\n";

        foreach ($columns as $column) {
            $name = Str::camel($column);
            $columnComment = $connection->getDoctrineColumn($prefix.$tableName, $column)->getComment();
            $fileContent .= "    /**\n";
            $fileContent .= "     * @var {$columnTypes[$column]} {$columnComment}\n";
            $fileContent .= "     */\n";
            $fileContent .= "    public \${$name};\n\n";
        }

        foreach ($columns as $column) {
            $name = Str::camel($column);
            $method = Str::studly($column);
            $fileContent .= "\tpublic function get$method()\n\t{\n\t\treturn \$this->$name;\n\t}\n\n";
            $fileContent .= "\tpublic function set$method(\$value)\n\t{\n\t\t\$this->$name = \$value;\n\t}\n\n";
        }

        $fileContent .= "}\n";

        file_put_contents(BASE_PATH . "/app/Bean/{$className}.php", $fileContent);

        $this->output->writeln("<info>Generated Bean: {$className}</info>");

    }
}