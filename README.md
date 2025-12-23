# MVC Project

[![Build Status](https://scrutinizer-ci.com/g/LuDar01/mvc.02/badges/build.png?b=master)](https://scrutinizer-ci.com/g/LuDar01/mvc.02/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/LuDar01/mvc.02/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/LuDar01/mvc.02/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/LuDar01/mvc.02/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/LuDar01/mvc.02/?branch=master)

This is an MVC (Model-View-Controller) project built with Symfony. The application demonstrates object-oriented programming (OOP) principles through a card application, the game of 21, and a comprehensive CRUD library.

## Features

* **OOP Card Application**: Implementation of a card deck and card hands using dedicated classes (`Card`, `CardGraphic`, `CardHand`, `DeckOfCards`). The deck state is maintained across requests using Symfony sessions.
* **21 Card Game**: A fully playable game of 21 against a bank (dealer), demonstrating a thin Controller and a thick Model (`Game` class).
    * **Game Rules**: The goal is to get as close to 21 as possible without exceeding it. Face cards are worth 10 points, and Aces are worth 1 or 11 points (chosen for the best score). The bank draws until its score is 17 or more.
* **Library CRUD (Create, Read, Update, Delete)**: A dedicated application built with Doctrine ORM to manage a collection of books.
    * **Database Integration**: Implements full CRUD functionality against a database table (`Book` entity).
    * **File Uploads**: Handles file upload for book cover images (`image_file`) with persistence of the file path.
    * **Old File Management**: Includes logic to safely **delete the old cover image file** when a book is updated with a new one.
* **Landing Pages**: Various routes with HTML responses rendered through Twig templates (`me`, `about`, `report`, `lucky`, `card`, `game`).
* **JSON API Endpoints**: Provides various API endpoints, including:
    * Getting the current deck.
    * Shuffling the deck (POST).
    * Drawing one or multiple cards (POST).
    * Checking the current 21-game status.
    * **Library API**: Endpoints to fetch all books (`/api/library/books`) and a specific book by its ISBN (`/api/library/book/<isbn>`).
* **Session Management**: A dedicated page to view and delete the current session data, including the number of cards remaining in the deck.
* **Documentation**: Includes a documentation page (`/game/doc`) with the game's flowchart, pseudocode, and a description of the core classes.
* **Random Number Generation**: Generates and displays a random lucky number.

## Requirements

* **PHP** (version 8.2 or higher recommended)
* **Composer** for dependency management
* **Symfony CLI** (optional but recommended)

## Installation

1.  **Clone the repository:**
    ```bash
    git clone [https://github.com/LuDar01/mvc.02.git](https://github.com/LuDar01/mvc.02.git)
    cd mvc.02
    ```

2.  **Install dependencies:**
    ```bash
    composer install
    ```

3.  **Run the application (using Symfony CLI):**
    ```bash
    symfony serve
    ```
    The application will typically be available at `http://127.0.0.1:8000`.

## Deployment and Environment Fixes

To ensure the application functions correctly both locally and when deployed to the student web server (running under a sub-directory and requiring `.htaccess` routing), the following adjustments were made:

* **Image Path Handling:** Twig templates use **conditional logic** (`app.request.host`) to generate correct absolute image paths. This is essential because the standard `asset()` function fails to resolve relative paths correctly när applikationen ligger i en underkatalog på BTH-servern.
* **Controller Consistency:** The `LibraryController` was refactored to ensure variable definitions (`$imagePath`) and file management logic were robust and consistent across `create` and `update` operations, eliminating "undefined variable" errors during file uploads.

## Key Routes

| Route | Description | Template |
| :--- | :--- | :--- |
| `/` | Presentation of "Me" (LuDa) | `me.html.twig` |
| `/about` | Information about the MVC course and architecture | `about.html.twig` |
| `/report` | Collection of course moment (kmom) reports | `report.html.twig` |
| `/lucky` | Displays a dynamic lucky number | `lucky_number.html.twig` |
| `/api` | Landing page for all JSON API endpoints | `json_api.html.twig` |
| `/card` | Home for the card application and class structure | `card/index.twig` |
| `/game` | Home and rules for the 21 game | `game/home.twig` |
| `/game/play` | The active 21 game board | `game/play.twig` |
| `/session` | View and manage current session data | `session.html.twig` |
| `/library/show` | List all books in the library | `library/show.html.twig` |
| `/library/create` | Add a new book | `library/create.html.twig` |
| `/metrics` | Analysis of code quality (kmom06) | `metrics/index.html.twig` |

## Quality Assurance and Development Tools

The project leverages **Composer scripts** to enforce **high code quality**, run **unit tests**, and generate **API documentation**, ensuring the software's robustness and reliability.

---

### Code Quality Commands (Linters)

| Command | Tool | Purpose |
| :--- | :--- | :--- |
| `composer csfix` | **PHP CS Fixer** | Automatically fixes code style according to **PSR standards**. |
| `composer lint` | **PHPMD / PHPStan** | Runs **static analysis** to detect code flaws, potential bugs, and complexity issues. |
| `composer metrics` | **Phpmetrics** | Generates quality metrics and HTML reports for visual analysis. |

---
### Unit Testing and Coverage

A comprehensive **test suite** is included for all model classes (**Card**, **DeckOfCards**, **Game**, etc.) to ensure reliable game logic.

| Command | Tool | Purpose |
| :--- | :--- | :--- |
| `composer phpunit` | **PHPUnit (with Xdebug)** | Executes the test suite and generates the **code coverage report**. |

> **Note:** The model classes currently maintain **100% code coverage** on all logic paths. The full HTML coverage report is generated in the `docs/coverage` directory.

---

### API Documentation

The documentation is generated automatically from **DocBlock comments** within the source code.

| Command | Tool | Purpose |
| :--- | :--- | :--- |
| `composer phpdoc` | **phpDocumentor** | Generates user-friendly **API documentation** for the source classes. |