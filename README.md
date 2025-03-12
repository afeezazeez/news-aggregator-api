# Challenge
The challenge is to build the backend functionality for a news aggregator website that pulls articles from various sources and serves them to the frontend application.
# Solution

## News Aggregator API

## Description
The News Aggregator API is a Laravel-based backend system that aggregates news articles from multiple external sources—such as The Guardian, NewsAPI, and The New York Times—and exposes endpoints to retrieve and filter this content. It supports paginated article listings and detailed views by slug, and uses dynamic filtering (by category, source, author, and date) along with caching to improve performance and scalability.


## Running the App
To run the App, you must have:
- **PHP** (https://www.php.net/downloads)
- **MySQL** (https://www.mysql.com/downloads/)
- **Composer** (https://getcomposer.org/download/)
- **PHPUnit** (https://phpunit.de/getting-started.html)

Clone the repository to your local machine using the command
```console
$ git clone https://github.com/afeezazeez/news-aggregator-api.git
```
## Configure app
Create an `.env` and copy `.env.example` content into it using the command.

```console
$ cp .env.example .env
```

### Environment
Configure environment variables in `.env` for dev environment based on your MYSQL database configuration and other configurations.
Please note that you have to set valid NEWSAPI_KEY, GUARDIAN_KEY, NYTIMES_KEY for news to be imported successfully.
Also set a default category to be used to filter NewsApi . You can leave it as default 'technology' 

```  
DB_CONNECTION=<YOUR_DB_CONNECTION>
DB_HOST=<YOUR_MYSQL_HOST>
DB_PORT=<YOUR_MYSQL_PORT>
DB_DATABASE=<YOUR_DB_NAME>
DB_USERNAME=<YOUR_DB_USERNAME>
DB_PASSWORD=<YOUR_DB_PASSWORD>

NEWSAPI_KEY=
NEWSAPI_API_URL=https://newsapi.org/v2/everything

GUARDIAN_KEY=
GUARDIAN_API_URL=https://content.guardianapis.com/search

NYTIMES_KEY=
NYTIMES_API_URL=https://api.nytimes.com/svc/search/v2/articlesearch.json


```
### LARAVEL INSTALLATION
Install the dependencies and start the server

```console
$ composer install
$ php artisan key:generate
$ php artisan migrate
$ php artisan l5-swagger:generate
$ php artisan serve
```
You should be able to visit your app at your laravel app base url e.g http://localhost:8000 or http://news-aggregator-api.test// (Provided you use Laravel Valet).


### Run the below command to fetch news
```console
$ php artisan app:fetch-news
```

### Run the below command to start schedule worker to update news hourly
```console
$ php artisan schedule:work
```

Api swagger documentation - http://news-aggregator-api.test/api/documentation or http://localhost:8000/api/documentation

### Running tests

To run the tests, run the below command
```console
$ composer test
```


## Additional Information
For details on architectural design choices, programming decisions and assumptions, as well as suggestions for future improvements, please refer to [this document](https://docs.google.com/document/d/16R69Pl1BHEnCXEP1ZE7yAAgkaiNb4s7c56uJcYeqkXA).
