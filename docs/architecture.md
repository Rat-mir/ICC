# Архитектура

Клиент — отдельный каталог client, HTML/CSS/JavaScript с fetch, порт 5173. Сервер — Laravel REST API, порт 8000. Представления Blade не используются.

Запрос проходит routes/api.php → middleware (Sanctum, active, role) → FormRequest → Controller → Service → Eloquent → MySQL. Ответ — API Resource → JSON → общий клиентский api().

Контроллеры получают валидированные данные, применяют Gate/Policy и вызывают сервисы через внедрение зависимостей конструктора. MakerService отвечает за каталог и модерацию, CharacterService — конфигурации и M:N, PostService — публикации и реакции, AuthService — регистрацию и токены, ImageService — PNG.

Публичные GET используют optional.auth: токен позволяет автору видеть собственный черновик, а администратору — материал для проверки. Защищённые операции используют auth:sanctum и active. Политики автоматически обнаруживаются по соглашению ModelPolicy.

Токен передаётся в Authorization: Bearer, хранится у клиента в sessionStorage, на сервере — SHA-256 в personal_access_tokens. Время жизни — SANCTUM_EXPIRATION (минуты), по умолчанию 1440. Logout удаляет текущий токен. Регистрация возвращает только разрешённые поля UserResource и токен.

Ошибки: message для всех кодов; errors для 422; 409 — BusinessException; 404 — привязка отсутствующей модели; 403 — права; 401 — аутентификация. При APP_DEBUG=false нет SQL/стека в ответе.

Изображения лежат в storage/app/private. В БД хранятся пути, наружу отдаются URL API. ImageService выдаёт PNG с nosniff и private/no-store после проверки доступа. Предпросмотр клиента получает Blob через API с токеном, а canvas экспортируется в PNG прямо в браузере.

CORS разрешает только CLIENT_ORIGINS; Bearer-режим не использует cookie credentials. OPTIONS обрабатывает стандартный HandleCors Laravel.

ApiLogger пишет метод, путь без query string, id пользователя, код и длительность. Пароли, тела запросов, токены и заголовки авторизации не журналируются. События отправки и проверки мейкеров записываются отдельно.

Для публичного развёртывания нужны HTTPS на обратном прокси, APP_DEBUG=false, отдельный DB-пользователь, надёжные пароли вместо демонстрационных и резервное копирование БД/хранилища. Локальный запуск использует HTTP на loopback.
