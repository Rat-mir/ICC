# ICC — проект для OSPanel и VS Code

ICC (Interactive Character Catalog) выполнен по КП 3–12 курса «ИС-24-22б Основы backend» и двум исходным файлам: ICC_КП2.docx и ICC_Техническое_задание_ICC.docx. Копии исходных документов находятся в docs/sources.

Стек: OSPanel 6.5, Apache, PHP 8.4, Laravel 13, MySQL 8.4.8, Sanctum. Клиент — отдельная папка client с HTML/CSS/JavaScript, без Node.js и сборки.

## Как открыть уже установленный проект

1. Запустите OSPanel. Включите Apache, PHP-8.4 и MySQL-8.4.
2. В VS Code откройте папку C:\OSPanel\home\ICC.
3. Откройте https://icc.local — клиент. API: https://api.icc.local/api/health.
4. Документация API: https://api.icc.local/docs/.

Домены определены в .osp/project.ini. Для клиента используется client, для API — public. .env, исходники PHP и приватные PNG не находятся в web-root. Клиент и сервер работают на разных HTTPS-origin, CORS разрешает только настроенные адреса.

Рабочая база: icc_backend. Тестовая: icc_backend_test. Проверочная SQL-база: icc_backend_ddl. Существовавшие ранее базы icc_pr3 и laravel не изменяются. SQLite не используется ни в запуске, ни в тестах; конфигурация Laravel содержит только MySQL.

## Аккаунты для проверки

| Роль | Email | Пароль |
|---|---|---|
| Администратор | admin@icc.test | DemoPass123! |
| Креатор | creator@icc.test | DemoPass123! |
| Пользователь | user@icc.test | DemoPass123! |

Регистрация создаёт user. Назначить creator может администратор в разделе «Аккаунты».

## Установка из архива

1. Распакуйте проект в OSPanel\home\ICC.
2. Включите MySQL-8.4, PHP-8.4 и Apache; перезапустите OSPanel после добавления проекта.
3. Откройте терминал проекта со средой PHP-8.4 в OSPanel. Выполните:

       composer install
       php tools/setup-mysql.php
       php artisan migrate --seed

Скрипт setup-mysql.php создаёт только базы ICC и локального пользователя icc_app; существующие данные не удаляет. По умолчанию используется MySQL OSPanel по адресу 127.0.1.31:3306. Для другой установки задаются ICC_MYSQL_HOST, ICC_MYSQL_PORT, ICC_MYSQL_ADMIN и ICC_MYSQL_ADMIN_PASSWORD. Пароли записываются только в локальные .env и .env.testing.

Повторный запуск setup-mysql.php не удаляет данные. Для нового заполнения пустой базы используется migrate --seed. migrate:fresh --seed удаляет таблицы выбранной базы — его применяют только к собственной демонстрационной базе, когда нужно пересоздать её полностью.

## Что реализовано

- Каталог одобренных мейкеров, поиск по названию, фильтры, сортировка и meta-пагинация.
- PNG-слои, категории, порядок слоёв, проверка MIME/содержимого/размеров/лимита 5 МБ.
- Гостевой конструктор и скачивание PNG в браузере.
- Сохранение, просмотр, редактирование и удаление своих персонажей.
- Публикации своих PNG, комментарии, один лайк на пользователя и публикацию.
- Мастерская креатора и модерация draft/revision → pending → approved/revision/rejected.
- Административное скрытие мейкеров/постов, удаление комментариев, роли и блокировка аккаунтов.
- Sanctum, Policy, Form Request, API Resource, транзакции, lockForUpdate, единые ошибки и журнал запросов.

## Ключевой сценарий

Креатор создаёт мейкер в мастерской, загружает body и face одинакового размера и отправляет на проверку. Admin возвращает с комментарием; креатор исправляет и отправляет повторно; admin одобряет. Гость видит набор в каталоге, собирает и скачивает PNG. Пользователь сохраняет персонажа и публикует работу.

Повторная отправка pending и правка неподходящего статуса дают 409; отказ без причины — 422; чужие данные и неверная роль — 403. Изменение approved снимает набор с публикации до новой проверки.

## Проверки в VS Code

В меню Terminal → Run Task доступны:

- ICC: тесты MySQL;
- ICC: миграции;
- ICC: документация API.

Либо в терминале со средой PHP-8.4:

    php artisan test
    php artisan route:list --path=api
    php artisan scribe:generate

37 тестов и 178 проверок пройдены на отдельной MySQL-базе. RefreshDatabase не затрагивает рабочую базу. Дополнительно выполнены 30 запросов Postman-коллекции с 35 проверками и полный браузерный сценарий на доменах OSPanel.

## Где материалы для КП

| Материал | Путь |
|---|---|
| Отчёты КП 3–12 | docs/reports/КП_03.docx … КП_12.docx |
| Матрица критериев | docs/compliance.md |
| Физическая модель | docs/physical-model.md, .dbml, .png |
| SQL и тестовые данные | sql/schema.mysql.sql, sql/seed.mysql.sql, sql/joins.sql |
| Результаты запросов и блокировки | docs/join-results.md, docs/mysql-lock-check.json |
| API | public/docs/index.html, public/docs/openapi.yaml, docs/API.md |
| Postman | docs/ICC.postman_collection.json, docs/ICC.smoke.postman_collection.json |
| Тест-кейсы и результаты | docs/test-cases.csv, docs/postman-results.json, docs/test-results.xml |
| Скриншоты | docs/screenshots/ |
| Подробная проверка | docs/verification.md |

В Postman импортируйте коллекцию, задайте base_url=https://api.icc.local. Login сохраняет token; в PNG-запросах выберите локальный файл. Для повторяемого сценария используйте ICC.smoke.postman_collection.json из корня проекта, чтобы относительные пути PNG указывали на storage/app/private/demo.

Пароли хранятся хешами, PNG — в storage/app/private, в БД — пути и метаданные. Изображения выдаёт API после проверки доступа, поэтому storage:link для приватных наборов не требуется. .env и .env.testing исключены из Git и архивов. Журнал не содержит паролей и Bearer-токенов.

Основная модель из исходных DOCX сохранена: User, Maker, Asset, Character, Post, Comment, Like. Вспомогательное поле asset_character.layer_order хранит порядок выбранных слоёв. Статус hidden реализует административное скрытие. Последнее решение модерации хранится в Maker, без отдельной истории.
