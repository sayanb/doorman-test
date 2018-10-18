<?php

require __DIR__ . '/vendor/autoload.php';
use AsyncPHP\Doorman\Manager\ProcessManager;
use AsyncPHP\Doorman\Task\ProcessCallbackTask;
use AsyncPHP\Remit\Client\ZeroMqClient;
use AsyncPHP\Remit\Server\ZeroMqServer;
use AsyncPHP\Remit\Location\InMemoryLocation;

try {
	$numTasks = 10;
	$manager = new ProcessManager();
	echo("\ncreated manager");
	$server = new ZeroMqServer(
	   	new InMemoryLocation("127.0.0.1", 5555)
	);
	echo("\ncreated server");
	$server->addListener("message-from-child-process", function ($message) {
		echo("\nmessage from child process: {$message}");
	});
	echo("\nadded listener");

	for ($i = 0; $i < $numTasks; $i++) { 
		if ($task = getNextTask($i)) {
			//print_r($task);
      		$manager->addTask($task);
      		echo("\nadded task");
   		}
	}

	while ($manager->tick()) {
		echo("\nlistening");
		$server->tick();
		usleep(250);
	}
} catch (Error $e) {
	echo($e->getMessage());
} catch (Exception $e) {
	echo($e->getMessage());
}

function getNextTask($taskNumber) {
	try {
		$task = new ProcessCallbackTask(function ()  use ($taskNumber) {
			echo "\nIn child";
		    $client = new ZeroMqClient(
		        new InMemoryLocation("127.0.0.1", 5555)
		    );

		    $data = "task number:  $taskNumber";
		    $client->emit(
		        "message-from-child-process", [$data]
		    );
		});
		return $task;
	} catch (Error $e) {
		log_message("error",$e->getMessage());
	} catch (Exception $e) {
		log_message("error",$e->getMessage());
	}
}