# Log Ingestion Service

This repository implements a fully dockerized Symfony 6+ setup that integrates with Kafka for log ingestion. The service is designed to process logs through Kafka topics, persist them into a database, and handle failures with a Dead Letter Queue (DLQ).

The system follows **Domain-Driven Design (DDD)** principles and utilizes **Hexagonal Architecture** to structure the code in a way that decouples the core business logic from external systems like Kafka, MySQL, and other services.

## Features

- **Domain-Driven Design (DDD)**: The architecture focuses on modeling the core business logic and building around it. Entities, Value Objects, and Repositories define the core domain.
- **Hexagonal Architecture**: The system uses Hexagonal Architecture (also known as Ports and Adapters) to decouple the application from external dependencies like Kafka and MySQL. This makes the system more flexible and easier to test.
- **Dockerized Setup**: Easily build and run the application using Docker.
- **Symfony 6+**: The application uses Symfony for backend logic.
- **Kafka**: Consumes log messages from Kafka topics and produces messages to topics (including a DLQ for failed messages).
- **MySQL**: Logs are persisted in a MySQL database.
- **DTOs**: Data Transfer Objects (DTOs) are used to structure the data for communication across different layers.

## Prerequisites

- Docker and Docker Compose should be installed on your local machine.
- Symfony 6+ is used in the project.
- Kafka and Zookeeper services are used for message handling.

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

### 4. Access the application
Once the containers are up and running, the application should be accessible at http://localhost:8000 or wherever your Docker setup is mapped.

### 5. Kafka Topics
The Kafka consumer listens for messages on specified topics, and on failure, messages are redirected to a Dead Letter Queue (DLQ). Ensure that Kafka is running and the topics are set up accordingly.

### 6. Testing
To run unit tests:
```bash
docker-compose run --rm phpunit
```