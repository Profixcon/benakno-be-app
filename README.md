# Benakno API - Laravel Backend for Bengkel Management

## Introduction

Welcome to the Benakno API project! This Laravel-based API serves as the backend for your web and mobile applications related to managing "bengkel" (workshops). The name "Benakno" is derived from the Indonesian word meaning "to know," reflecting the API's purpose of providing comprehensive workshop management.

## Getting Started

Follow these steps to set up and run the Benakno API on your local machine.

### Prerequisites

Make sure you have the following prerequisites installed on your system:

- PHP (version 7.4 or higher)
- Composer
- Laravel (install using `composer global require laravel/installer`)

### Installation

1. Clone the repository:

    ```bash
    git clone https://github.com/Profixcon/benakno-be-app.git
    ```

2. Navigate to the project directory:

    ```bash
    cd benakno-be-app
    ```

3. Install dependencies:

    ```bash
    composer install
    ```

4. Copy the `.env.example` file and rename it to `.env`. Update the database and other relevant configurations.

    ```bash
    cp .env.example .env
    ```

5. Generate the application key:

    ```bash
    php artisan key:generate
    ```

6. Start the development server:

    ```bash
    php artisan serve
    ```

The API is now running locally at `http://localhost:8000`.

## API Documentation

Refer to the API documentation for details on available endpoints and how to interact with them. You can access the documentation by visiting `http://localhost:8000/api/documentation` after starting the development server.

## Features

- **Workshops**: Manage information about workshops.
- **Services**: Define and update various services offered by workshops.
- **Appointments**: Schedule appointments for services.
- **Users**: Handle user authentication and authorization.

## Contributing

If you'd like to contribute to the development of the Benakno API, please follow our [contribution guidelines](CONTRIBUTING.md).

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

We would like to express our gratitude to the Laravel community for providing a robust framework for building web applications.

Happy coding with Benakno API!
