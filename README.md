# MVC Project

This is an MVC (Model-View-Controller) project built with Symfony. The application demonstrates object-oriented programming (OOP) principles through a card application and the game of 21.

## Features

* **OOP Card Application**: Implementation of a card deck and card hands using dedicated classes (`Card`, `CardGraphic`, `CardHand`, `DeckOfCards`). The deck state is maintained across requests using Symfony sessions.
* **21 Card Game**: A fully playable game of 21 against a bank (dealer), demonstrating a thin Controller and a thick Model (`Game` class).
    * **Game Rules**: The goal is to get as close to 21 as possible without exceeding it. [cite_start]Face cards are worth 10 points, and Aces are worth 1 or 11 points (chosen for the best score)[cite: 70]. [cite_start]The bank draws until its score is 17 or more[cite: 71].
* **Landing Pages**: Various routes with HTML responses rendered through Twig templates (`me`, `about`, `report`, `lucky`, `card`, `game`).
* **JSON API Endpoints**: Provides various API endpoints, including:
    * Getting the current deck.
    * Shuffling the deck (POST).
    * Drawing one or multiple cards (POST).
    * Checking the current 21-game status.
* **Session Management**: A dedicated page to view and delete the current session data, including the number of cards remaining in the deck.
* [cite_start]**Documentation**: Includes a documentation page (`/game/doc`) with the game's flowchart, pseudocode, and a description of the core classes[cite: 52].
* **Random Number Generation**: Generates and displays a random lucky number.

## Requirements

* **PHP** (version 7.4 or higher)
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

## Key Routes

| Route | Description | Template |
| :--- | :--- | :--- |
| `/` | Presentation of "Me" (LuDa) | [cite_start]`me.html.twig` [cite: 17] |
| `/about` | Information about the MVC course and architecture | [cite_start]`about.html.twig` [cite: 50] |
| `/report` | Collection of course moment (kmom) reports | [cite_start]`report.html.twig` [cite: 21] |
| `/lucky` | Displays a dynamic lucky number | [cite_start]`lucky_number.html.twig` [cite: 58] |
| `/api` | Landing page for all JSON API endpoints | [cite_start]`json_api.html.twig` [cite: 1] |
| `/card` | Home for the card application and class structure | `card/index.twig` |
| `/game` | Home and rules for the 21 game | [cite_start]`game/home.twig` [cite: 69] |
| `/game/play` | The active 21 game board | [cite_start]`game/play.twig` [cite: 9] |
| `/session` | View and manage current session data | [cite_start]`session.html.twig` [cite: 19] |

## Quality Assurance and Development Tools

The project leverages **Composer scripts** to enforce **high code quality**, run **unit tests**, and generate **API documentation**, ensuring the software's robustness and reliability.

---

### Code Quality Commands (Linters)

| Command | Tool | Purpose |
| :--- | :--- | :--- |
| `composer csfix` | **PHP CS Fixer** | Automatically fixes code style according to **PSR standards**. |
| `composer lint` | **PHPMD / PHPStan** | Runs **static analysis** to detect code flaws, potential bugs, and complexity issues. |

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
---