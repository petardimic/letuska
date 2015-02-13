<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace TravelPortModule\Tools\Console\Command;

use Kdyby\Doctrine\DuplicateEntryException;
use Kdyby\Doctrine\EntityManager;
use Nette\DI\Container;
use Nette\Utils\Json;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\Tools\SchemaValidator;

/**
 * Command to import cities.

 */
class ImportCitiesCommand extends Command
{
    const ITEMS = 3000;


    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('travel:import-cities')
            ->setDescription('Import Cities.')
            ->setDefinition(array(
                new InputOption(
                    'truncate', null, InputOption::VALUE_NONE,
                    'If defined, all data will be cleared.'
                ),
            ))
            ->setHelp(<<<EOT
'Imports cities -> latitude, longitude, continent-code.'
EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em   = $this->getHelper('em')->getEntityManager();
        $conn = $em->getConnection();
        $exit = 0;

        if ($input->getOption('truncate')) {
            $conn->prepare("SET FOREIGN_KEY_CHECKS = 0")->execute();
            $conn->prepare("TRUNCATE TABLE city")->execute();
            $conn->prepare("TRUNCATE TABLE city_lang")->execute();
            $conn->prepare("SET FOREIGN_KEY_CHECKS = 1")->execute();
            $output->writeln('<info>[Truncate] OK - The cities was cleared.</info>');
            return $exit;
        }

        /** @var Container $container */
        $container = $this->getHelper('container')->getContainer();

        if (file_exists($path = $container->getParameters()['cities'])) {
            $iterate = $container->getParameters()['importItems'] ? $container->getParameters()['importItems'] : self::ITEMS;
            $lines   = file($path, FILE_IGNORE_NEW_LINES);
            $langs   = array('en', 'cs', 'hu', 'bg', 'sk', 'ru', 'uk', 'pl', 'fr', 'es', 'de', 'it', 'pt');

            $conn->prepare("START TRANSACTION")->execute();

            $sql  = "SELECT MAX(id) FROM city";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $max = $stmt->fetchColumn();
            $key = -1;

            foreach ($lines as $key => $line) {
                if ($key < $max) continue;
                if ($iterate-- <= 0) break;

                $dLine = Json::decode($line);

                $lat = isset($dLine->gps)
                    ? $dLine->gps->lat
                    : 'NULL';

                $lon = isset($dLine->gps)
                    ? $dLine->gps->lon
                    : 'NULL';

                try {
                    $sql  = "INSERT INTO city (iata, country_code, lat, lon) VALUES (:iata, :countryCode, :lat, :lon)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindValue("iata", $dLine->iata);
                    $stmt->bindValue("countryCode", $dLine->countryCode);
                    $stmt->bindValue("lat", $lat);
                    $stmt->bindValue("lon", $lon);

                    $stmt->execute();
                    $lastId = $conn->lastInsertId();

                } catch (DuplicateEntryException $exc) {
                    continue;
                }

                foreach ($langs as $lang) {
                    if (isset($dLine->$lang)) {
                        $countryName = $dLine->$lang->countryName;
                        $name        = $dLine->$lang->name;
                        $pattern     = $dLine->$lang->pattern;

                        $sql  = "INSERT INTO city_lang (lang, country_name, name, pattern, country_id) VALUES (:lang, :countryName,:name, :pattern, :countryId)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindValue("lang", $lang);
                        $stmt->bindValue("countryName", $countryName);
                        $stmt->bindValue("name", $name);
                        $stmt->bindValue("pattern", $pattern);
                        $stmt->bindValue("countryId", $lastId);

                        $stmt->execute();
                    }
                }
            }
            $conn->prepare("COMMIT")->execute();

            if ($key < count($lines) - 1) {
                $output->writeln("<error> generate not complete [" . $key . " / " . count($lines) . " is needed]</error>");
                $exit += 1;
                return $exit;
            }

            $output->writeln('<info>[Import]  OK - The cities is all loaded.</info>');
            return $exit;
        }

        $exit += 2;
        $output->writeln("<error> file $path not found </error>");

        return $exit;
    }
}