# Log Ingestion Service

This service have two parts:

* This repository implements a fully dockerized Symfony 7+ setup that integrates with Kafka for log ingestion. The service is designed to process logs through Kafka topics, persist them into a database, and handle failures with a Dead Letter Queue (DLQ).

* Second is to expose endpoint **/count** via REST Api, which is responsible to return the count of logs and also allow filtering for 
•	serviceNames
•	statusCode
•	startDate
•	endDate

## Features

- **Domain-Driven Design (DDD)**: The architecture focuses on modeling the core business logic and building around it. Entities, Value Objects, and Repositories define the core domain.
- **Hexagonal Architecture**: The system uses Hexagonal Architecture (also known as Ports and Adapters) to decouple the application from external dependencies like Kafka and MySQL. This makes the system more flexible and easier to test.
- **Dockerized Setup**: Easily build and run the application using Docker.
- **Symfony 7+**: The application uses Symfony for backend logic.
- **LogOffsetTracker** Uses file based offset tracking to resume ingestion from last processed offset in case of service crash etc.
- **LogTailer**: Uses SimpleLogParser to parse the log file using Regex, handle log rotation etc.
- **Kafka + Zookeeper**: Consumes log messages from Kafka topics and produces messages to topics (including a DLQ for failed messages).
- **MySQL**: Logs are persisted in a MySQL database.
- **DTOs**: Data Transfer Objects (DTOs) are used to structure the data for communication across different layers.
- **PHPUnit 9+**: PHPUnit is used as a testing framework.

## Prerequisites

- Docker and Docker Compose should be installed on your local machine.

## Getting Started

### 1. Clone the repository

```bash
git clone https://github.com/gr8abbasi/log-ingestion.git
cd log-ingestion
```

### 2. Build and run the Docker containers

```bash
docker-compose up --build -d
```
This will build and run the following services:
* PHP 8.3 with Symfony
* MySQL 8
* Apache Kafka and Zookeeper
* PHPUnit

<img width="1400" alt="Screenshot 2025-04-29 at 18 50 58" src="https://github.com/user-attachments/assets/1a9b82f1-7dd5-4cb3-b228-74516066d23e" />

### 4. Consume Kafka Messages
```bash
docker-compose exec php php bin/console log-ingestion:consume-kafka
```
Run this command in terminal, it's responsible to consume messages from **Kafka** and persist to MySql databases in batches.

For first time this command might return error **[Kafka Error] Broker: Unknown topic or partition**
which completely fine as topic is created by **ingest-logs** command.

### 5. Ingest Log
```bash
docker-compose exec php php bin/console log-ingestion:ingest-logs
```
Run this command in new tab in terminal to better see log-ingestion and messages consumption, this will continue watching for the log and fire an event and publish to **Kafka** as soon as there is a new line add/written in log file.

Sample log file is located in symfony project root `/log-ingestion/data/logs.log` and offset tracking is done in `/log-ingestion/data/logs.log.offset`

**Tip:** Once log file is processed successfully and offset is update, in order to re-run same log file e.g. for testing delete the offset file.

### 6. Access the application
Once the containers are up and running, the application should be accessible at http://localhost:8000 or wherever your Docker setup is mapped.

- `/count` endpoint http://localhost:8000/count
- with filters http://localhost:8000/count?serviceNames[]=USER-SERVICE&serviceNames[]=INVOICE-SERVICE&statusCode=201&startDate=2024-01-01T00:00:00Z&endDate=2024-12-31T23:59:59Z

### 7. Testing
To run unit tests:
```bash
docker-compose run --rm phpunit
```
### 8. Packaging using Composer

Application is packaged using composer, `composer.json` have everything setup.

### 9. Helpful Kafka Commands

- Run below command to use kafka bash in container:

```bash
docker exec -it log_ingestion_kafka bash
```

- To get 50 messages from start

```bash
kafka-console-consumer.sh --bootstrap-server localhost:9092 --topic log.alerts --from-beginning --max-messages 50
```
- Get offset of kafka for topic

```bash
kafka-run-class.sh kafka.tools.GetOffsetShell --broker-list localhost:9092 --topic log.alerts --time -1
```
- Delete the topic

```bash
kafka-topics.sh --bootstrap-server localhost:9092 --topic log.alerts --delete
```

- Reset offset of kafka topic to earliest, consumer can consume already processed messages.

```bash
docker-compose exec kafka kafka-consumer-groups.sh \
--bootstrap-server kafka:9092 \
--group log-consumer-group \
--topic log.alerts \
--reset-offsets --to-earliest --execute
```

## Future Improvements
- Configurable batch size for publishing and consumption of messages
- Improve API response
- Asynchronous writing to the database (currently written from the consumer thread).
- Asynchronous retries or error handling that block processing if something fails.
- Environment specific configuration files
- Proper logging using libraries e.g. monolog
- Add Feature and Integration tests
- Use library for Kafka communication rather than using extension directly
