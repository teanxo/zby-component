<?php

namespace Hyperf\Zby\Command;


use Hyperf\Command\Annotation\Command;
use Hyperf\Zby\Helper\Str;
use Hyperf\Zby\ZbyCommand;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class JwtCommand extends ZbyCommand
{

    protected ?string $name = "zby:gen-jwt";

    protected function configure()
    {
        parent::configure();
        $this->setHelp('run "php bin/hyperf.php zby:gen-jwt" create the new jwt secret ');
        $this->setDescription('ZbySoa system gen jwt command');
    }

    public function handle()
    {
        $jwtSecret = Str::upper($this->input->getOption('jwtSecret'));
        if (empty($jwtSecret)) {
            $this->line('Missing parameter <--jwtSecret < jwt secret name>>', 'error');
        }

        $envPath = BASE_PATH . '/.env';
        if (! file_exists($envPath)) {
            $this->line('.env file not is exists!', 'error');
        }

        $key = base64_encode(random_bytes(64));

        if (Str::contains(file_get_contents($envPath), $jwtSecret) === false) {
            file_put_contents($envPath, "\n{$jwtSecret}={$key}\n", FILE_APPEND);
        } else {
            file_put_contents($envPath, preg_replace(
                "~{$jwtSecret}\s*=\s*[^\n]*~",
                "{$jwtSecret}=\"{$key}\"",
                file_get_contents($envPath)
            ));
        }

        $this->info('jwt secret generator successfully:' . $key);

    }

    protected function getOptions(): array
    {
        return [
            ['jwtSecret', '', InputOption::VALUE_REQUIRED, 'Please enter the jwtSecret to be generated'],
        ];
    }
}