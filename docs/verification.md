# Проверка ICC на MySQL и OSPanel

11 ноября 2026. Рабочая папка: C:\OSPanel\home\ICC.

| Проверка | Фактический результат | Доказательство |
|---|---|---|
| СУБД | MySQL 8.4.8, InnoDB, utf8mb4 | ospanel-check.json |
| Рабочая / тестовая базы | icc_backend / icc_backend_test | .env / .env.testing (не в Git) |
| Пересоздание схемы и сидирование | migrate:fresh --seed выполнено | mysql-migrate-seed.txt |
| Feature / Unit | 37 тестов, 178 проверок, 0 ошибок | test-results.xml, test-output-mysql.txt |
| DDL + INSERT | SQL выполнен в icc_backend_ddl | mysql-ddl-check.json |
| SELECT с JOIN | 5 запросов выполнены в MySQL | join-results.md |
| Параллельные изменения | 2 PDO-соединения: блокировка 1205, откат сохраняет данные | mysql-lock-check.json |
| HTTPS | TLS 1.3, сертификат проверен; домены OSPanel работают без отключения проверки TLS | https-check.json, ospanel-check.json |
| Браузер | Полный сценарий на https://icc.local пройден | browser-results.json, screenshots/ |
| Postman | 30 запросов, 35 проверок, 0 ошибок, runner Newman | postman-results.json |
| CORS / Bearer / OPTIONS | Проверены в браузере и тестах | browser-results.json, screenshots/23-devtools-network.png |
| Документация Scribe | HTML, OpenAPI, Postman сгенерированы | public/docs/, scribe-generation.txt |

Сидирование создаёт минимум 10 строк в каждой основной сущности и 60 PNG-элементов. После проверки браузером и Postman в рабочей базе остаются дополнительные демонстрационные записи.

Браузерный сценарий: гость скачивает PNG; новый пользователь регистрируется, сохраняет и редактирует персонажа, публикует PNG, комментирует и ставит лайк; креатор создаёт и загружает набор; администратор возвращает заявку с причиной; креатор исправляет и отправляет повторно; администратор одобряет; набор появляется в публичном каталоге. Проверены 409, 422 и ручной запрос к admin от user с 403.

Команды из терминала среды PHP-8.4 в OSPanel либо задача VS Code:

    php artisan test
    php artisan scribe:generate
    php tools/verify-mysql.php
    php tools/verify-lock.php

Postman-коллекция проверена автоматическим runner Newman; это не ручной прогон мышью в приложении Postman. Результаты всех фактически отправленных запросов сохранены. Материалы в Classroom не загружались. Git-репозиторий локальный; удалённая публикация не выполнялась.
