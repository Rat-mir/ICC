# Соответствие критериям КП 3–12 и ТЗ ICC

Основание: два исходных DOCX в docs/sources и условия Classroom. База и проверки — MySQL 8.4.8. Скриншоты — реальный браузер OSPanel.

| КП | Требования | Артефакты | Проверка |
|---|---|---|---|
| 3 | Физическая модель, 3НФ, DDL, данные и JOIN | sql/; physical-model.*; mysql-ddl-check.json; join-results.md | DDL и INSERT выполнены в MySQL; 5 SELECT с JOIN |
| 4 | PHP/Composer/MySQL, API, health, окружение, Git | README.md; .osp/; .vscode/; ospanel-check.json | OSPanel Apache/PHP/MySQL, HTTPS health 200 |
| 5 | Модели, миграции, Enum, фабрики, сидеры, Requests/Resources | database/; app/Models; app/Enums; tinker-mysql.txt | migrate:fresh --seed; связи в tinker; >=10 строк |
| 6 | REST, маршруты, контроллеры, сервисы, API-контракт | routes/api.php; app/Services; endpoints.csv; public/docs/ | 37 маршрутов, CRUD и коды проверены |
| 7 | CRUD, M:N, русская валидация, единые ошибки | app/Http/Requests; bootstrap/app.php; screenshots/ | 33 Feature ICC, 404/409/422 и владение |
| 8 | Sanctum, токены, роли, Policy, время жизни | app/Policies; app/Services/AuthService.php | 401/403, выход и отзыв токена проверены |
| 9 | Сценарий, >=3 правила, транзакции, фильтры, meta, статистика | business-rules.md; mysql-lock-check.json | полный цикл; два соединения InnoDB; откат |
| 10 | >=15 кейсов, >=8 Feature, логи, API-документация | test-cases.csv; postman-results.json; public/docs/ | 37 тестов/178 проверок; Postman 30/35 |
| 11 | Отдельный клиент, CORS, fetch, вход, user, logout, Network | client/; config/cors.php; screenshots/23-devtools-network.png | HTTPS-origin раздельны; Bearer/OPTIONS проверены |
| 12 | Фильтры, карточки, формы, удаление, сценарий, файлы и роли | client/; browser-results.json; screenshots/ | полный браузерный сценарий в OSPanel пройден |


## Функциональные требования ТЗ

| № | Требование | Реализация | Скриншот / проверка |
|---|---|---|---|
| 1 | Публичный каталог | MakerService/PostService, Policy | 01,21,22 |
| 2 | Уникальная регистрация | RegisterRequest/AuthService | 05 + Feature |
| 3 | Вход, выход, токен | Sanctum, AuthController | 10 + Postman |
| 4 | Поиск и категории | CatalogRequest, MakerService | 02,03 |
| 5 | PNG и порядок слоёв | client builder, pivot layer_order | 04,06 |
| 6 | Гостевой PNG-экспорт | canvas.toBlob/toDataURL | 04 + guest-character.png |
| 7 | Собственные персонажи CRUD | CharacterService/Policy | 06,09 + Feature |
| 8 | Публикации и удаление | PostService/Policy | 07 + Feature |
| 9 | Свои готовые PNG | PostRequest/ImageService | 07 + Postman upload |
| 10 | Комментарии | InteractionController/CommentPolicy | 08 + Feature |
| 11 | Один лайк и снятие | UNIQUE, PostService | 08 + Postman |
| 12 | Черновики креатора CRUD | MakerService/Policy | 11,12 |
| 13 | Загрузка категорий и слоёв | AssetRequest/ImageService | 12 |
| 14 | Отправка на модерацию | MakerService::submit | 13,14 |
| 15 | Решение с причиной | ReviewRequest, MakerService::review | 16,17,18 |
| 16 | Скрытие нарушений | admin hide endpoints | 20 + Feature |
| 17 | Доработка и повторная проверка | revision → pending → approved | 17,18,21 |


## Нефункциональные требования

PHP/Laravel REST JSON, HTTPS в OSPanel, MySQL 8.4/InnoDB, Sanctum, hashed-пароли, Policy, Form Request, 401/403/404/409/422, настоящий PNG до 5 МБ с проверкой MIME и размеров, paginate(20), документация Scribe/OpenAPI/Postman — реализованы и проверены. .env и .env.testing приватны. Локальный Git содержит этапы работ.

Postman-запросы отправлены runner Newman, без утверждения о ручном прогоне мышью. Репозиторий опубликован: https://github.com/Rat-mir/ICC. Сдача в Classroom и утверждение модели преподавателем не выполнялись.


12 ноября 2026: повторный последовательный прогон в C:\OSPanel\home\ICC: 37 тестов, 178 assertions, 0 ошибок и failures. Проверка двух соединений MySQL повторена: 1205 при конкурентном изменении; rollback сохраняет данные. Актуальный список middleware обновлён в docs/routes.json.
