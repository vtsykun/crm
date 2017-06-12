<?php

namespace OroCRM\Bundle\ReportBundle\Command;

use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\Query;

use Oro\Bundle\CronBundle\Command\CronCommandInterface;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Class ReportUpdateCommand
 * @package OroCRM\Bundle\ReportBundle\Command
 */
class ReportUpdateCommand extends ContainerAwareCommand implements CronCommandInterface
{
    public function getDefaultDefinition()
    {
        return '* */2 * * *';
    }

    /**
     * Console command configuration
     */
    public function configure()
    {
        $this->setName('oro:report:update');
        $this->setDescription('Update report transactional tables');
    }

    /**
     * Runs command
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     *
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->getDescription());

        // place for custom report logic

        $output->writeln('Completed');
    }

    /**
     * @param $table
     * @param $data
     *
     * @return bool
     */
    protected function importData($table, $data)
    {
        if (empty($data)) {
            return false;
        }

        $conn = $this->getConn();

        $conn->beginTransaction();
        $conn->executeQuery($conn->getDatabasePlatform()->getTruncateTableSQL($table));

        $query = "INSERT INTO %s VALUES (" . str_repeat('?, ', count($data[0])) . " ?)";
        $stmt  = $conn->prepare(sprintf($query, $table));

        foreach ($data as $rec) {
            $i = 1;

            // first param always an autogenerated id
            $stmt->bindValue($i++, null);

            foreach ($rec as $field) {
                $stmt->bindValue($i++, $field);
            }

            $stmt->execute();
            $stmt->closeCursor();
        }

        try {
            $conn->commit();
        } catch (ConnectionException $e) {
            $conn->rollBack();

            return false;
        }

        return true;
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    protected function getConn()
    {
        return $this->getContainer()->get('doctrine.dbal.default_connection');
    }
}