<?php

namespace App\Scheduler\MessageHandler;

use Doctrine\DBAL\Connection;
use App\Scheduler\Message\WeeklyDatabaseBackup;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Doctrine\DBAL\Schema\Table;

#[AsMessageHandler]
final class WeeklyDatabaseBackupHandler{

    private readonly string $backupPath ; 
    private readonly Connection $connection;
    private readonly LoggerInterface $logger;

    public function __construct(Connection $connection, LoggerInterface $logger)
    {
        $this->backupPath = sprintf("backup/%s_backup.sql", date('d_m_Y-His'));
        $this->connection = $connection;
        $this->logger = $logger;
    }

    private function convertTableDataIntoSqlString(array $valuesFromDatabase) : string {
        $tmpSqlDump = "";
        foreach($valuesFromDatabase as $entity){
            $EntityIntoArrayOfStrings = array_map(fn ($item) => match(gettype($item)){
                "string" => sprintf("'%s'", addslashes($item)),
                "NULL" => "NULL",
                "unknown type" => "NULL",
                default => $item
            }, $entity);
            $tmpSqlDump .= sprintf("\t(%s)\n", implode(", ", $EntityIntoArrayOfStrings));
        }
        return "$tmpSqlDump;\n";
    }

    public function __invoke(WeeklyDatabaseBackup $message) : void
    {
        $this->logger->info("BACKUPSTARTING");
        $sqlDump = "";
        $platform = $this->connection->getDatabasePlatform();
        $schemaManager = $this->connection->createSchemaManager();
        $tables = $schemaManager->listTables();
        $sqlDump .= implode(";\n", $platform->getCreateTablesSQL($tables)).";\n";
        foreach($tables as $table){
            $sqlQuery = sprintf("SELECT * FROM %s ;", $table->getName());
            $tableData = $this->connection
                ->prepare($sqlQuery)
                ->executeQuery()
                ->fetchAllAssociative();
            if(!empty($tableData)){
                $columns = implode(', ', array_keys($tableData[0]));
                $sqlDump .= sprintf("INSERT INTO %s (%s) VALUES \n", $table->getName(), $columns);
                $sqlDump .= $this->convertTableDataIntoSqlString($tableData);
            }
        }
        $filesystem = new Filesystem();
        try {
            $filesystem->touch(
                $this->backupPath,
            );
            $filesystem->appendToFile($this->backupPath, $sqlDump);
        } catch (IOExceptionInterface $exception) {
            echo "An error occurred while creating your directory at ".$exception->getPath();
        } finally {
            return;
        }
    }
}
