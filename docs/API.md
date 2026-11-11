# ICC REST API

Справочник получен из фактического route:list. Все запросы и примеры схем ответов — в ICC.postman_collection.json. Коллекция не опубликована во внешнем сервисе.

Authorization: Bearer <token>; Accept: application/json. JSON-тело: Content-Type application/json. PNG: multipart/form-data. Ответ одиночного ресурса: data; списка: data[], links, meta(current_page,last_page,per_page,total).

Каталог GET /api/makers: search ≤100 символов; category=background/body/clothes/face/hair/accessory; status=Enum; from/to=даты; sort=id/title/created_at/updated_at; order=asc/desc; page≥1; per_page=1–100, по умолчанию20; mine=1 для своих наборов при наличии токена. Гость видит только approved. Пример: /api/makers?category=body&search=Лунный&sort=title&order=asc&per_page=2&page=1.

401: аутентификация;403: права;404: нет записи;409: бизнес-правило;422: валидация;429: лимит;500: внутренняя ошибка;503: health без БД. Формат ошибок: {"message":"…"}, для422 дополнительно {"errors":{"field":["Ошибка"]}}.

| Метод | URL | Контроллер | Описание | Доступ | Тело | Успех | Ошибки |
|---|---|---|---|---|---|---|---|
| GET | /api/admin/makers | MakerController@moderation | Список для модерации | admin | — | 200 | 401,403,404,409,422,429,500 |
| POST | /api/admin/makers/{maker}/hide | MakerController@hide | Скрытие | admin | — | 200 | 401,403,404,409,422,429,500 |
| POST | /api/admin/makers/{maker}/review | MakerController@review | Решение администратора | admin | {"decision": "request-revision", "review_comment": "Добавьте вариант причёски."} | 200 | 401,403,404,409,422,429,500 |
| GET | /api/admin/posts | PostController@moderation | Список для модерации | admin | — | 200 | 401,403,404,409,422,429,500 |
| POST | /api/admin/posts/{post}/hide | PostController@hide | Скрытие | admin | — | 200 | 401,403,404,409,422,429,500 |
| GET | /api/admin/statistics | MakerController@statistics | Агрегированная статистика | admin | — | 200 | 401,403,404,409,422,429,500 |
| GET | /api/admin/users | AdminUserController@index | Список | admin | — | 200 | 401,403,404,409,422,429,500 |
| PATCH | /api/admin/users/{user} | AdminUserController@update | Изменение | admin | {"role": "creator", "is_blocked": false} | 200 | 401,403,404,409,422,429,500 |
| GET | /api/assets/{asset}/image | AssetController@image | PNG с проверкой доступа | approved всем; приватные автору/admin | — | 200 | 401,403,404,409,422,429,500 |
| GET | /api/characters | CharacterController@index | Список | авторизованный; свой объект/admin | — | 200 | 401,403,404,409,422,429,500 |
| POST | /api/characters | CharacterController@store | Создание | авторизованный; свой объект/admin | {"name": "Мой герой", "maker_id": 1, "asset_ids": [1, 2, 3, 4, 5, 6]} | 201 | 401,403,404,409,422,429,500 |
| GET | /api/characters/{character} | CharacterController@show | Карточка | авторизованный; свой объект/admin | — | 200 | 401,403,404,409,422,429,500 |
| PUT | /api/characters/{character} | CharacterController@update | Изменение | авторизованный; свой объект/admin | {"name": "Мой герой", "maker_id": 1, "asset_ids": [1, 2, 3, 4, 5, 6]} | 200 | 401,403,404,409,422,429,500 |
| DELETE | /api/characters/{character} | CharacterController@destroy | Удаление | авторизованный; свой объект/admin | — | 204 | 401,403,404,409,422,429,500 |
| DELETE | /api/comments/{comment} | InteractionController@destroy | Удаление | авторизованный; свой объект/admin | — | 204 | 401,403,404,409,422,429,500 |
| GET | /api/health | HealthController | Проверка приложения и БД | гость | — | 200 | 401,403,404,409,422,429,500,503 |
| POST | /api/login | AuthController@login | Вход и токен | гость | {"email": "creator@icc.test", "password": "DemoPass123!"} | 200 | 401,403,404,409,422,429,500 |
| POST | /api/logout | AuthController@logout | Отзыв токена | авторизованный; свой объект/admin | — | 204 | 401,403,404,409,422,429,500 |
| GET | /api/makers | MakerController@index | Список | approved всем; приватные автору/admin | — | 200 | 401,403,404,409,422,429,500 |
| POST | /api/makers | MakerController@store | Создание | creator/admin; владелец | {"title": "Новый мейкер", "description": "Прозрачные PNG 512×512."} | 201 | 401,403,404,409,422,429,500 |
| GET | /api/makers/{maker} | MakerController@show | Карточка | approved всем; приватные автору/admin | — | 200 | 401,403,404,409,422,429,500 |
| PUT | /api/makers/{maker} | MakerController@update | Изменение | creator/admin; владелец | {"title": "Новый мейкер", "description": "Прозрачные PNG 512×512."} | 200 | 401,403,404,409,422,429,500 |
| DELETE | /api/makers/{maker} | MakerController@destroy | Удаление | creator/admin; владелец | — | 204 | 401,403,404,409,422,429,500 |
| POST | /api/makers/{maker}/assets | AssetController@store | Создание | creator/admin; владелец | {"name": "Основа", "category": "body", "layer_order": 10, "image": "<PNG>"} | 201 | 401,403,404,409,422,429,500 |
| PUT | /api/makers/{maker}/assets/{asset} | AssetController@update | Изменение | creator/admin; владелец | {"name": "Основа", "category": "body", "layer_order": 10} | 200 | 401,403,404,409,422,429,500 |
| DELETE | /api/makers/{maker}/assets/{asset} | AssetController@destroy | Удаление | creator/admin; владелец | — | 204 | 401,403,404,409,422,429,500 |
| POST | /api/makers/{maker}/submit | MakerController@submit | Отправить на модерацию | creator/admin; владелец | — | 200 | 401,403,404,409,422,429,500 |
| GET | /api/posts | PostController@index | Список | видимые всем; скрытые автору/admin | — | 200 | 401,403,404,409,422,429,500 |
| POST | /api/posts | PostController@store | Создание | авторизованный; свой объект/admin | {"title": "Мой герой", "image": "<PNG>", "character_id": 1} | 201 | 401,403,404,409,422,429,500 |
| GET | /api/posts/{post} | PostController@show | Карточка | видимые всем; скрытые автору/admin | — | 200 | 401,403,404,409,422,429,500 |
| DELETE | /api/posts/{post} | PostController@destroy | Удаление | авторизованный; свой объект/admin | — | 204 | 401,403,404,409,422,429,500 |
| POST | /api/posts/{post}/comments | InteractionController@comment | Добавление комментария | авторизованный; свой объект/admin | {"body": "Отличный образ!"} | 201 | 401,403,404,409,422,429,500 |
| GET | /api/posts/{post}/image | PostController@image | PNG с проверкой доступа | видимые всем; скрытые автору/admin | — | 200 | 401,403,404,409,422,429,500 |
| PUT | /api/posts/{post}/like | InteractionController@like | Поставить лайк | авторизованный; свой объект/admin | — | 200 | 401,403,404,409,422,429,500 |
| DELETE | /api/posts/{post}/like | InteractionController@unlike | Снять лайк | авторизованный; свой объект/admin | — | 200 | 401,403,404,409,422,429,500 |
| POST | /api/register | AuthController@register | Регистрация | гость | {"name": "Новый участник", "email": "new@icc.test", "password": "DemoPass123!", "password_confirmation": "DemoPass123!"} | 201 | 401,403,404,409,422,429,500 |
| GET | /api/user | AuthController@user | Текущий пользователь | авторизованный; свой объект/admin | — | 200 | 401,403,404,409,422,429,500 |


PNG: до5МБ,16–2048px, MIME image/png, одинаковые размеры в наборе. POST/PATCH assets принимает name/category/layer_order/is_active/image; multipart PATCH в PHP отправляют POST с _method=PATCH. Character: name/maker_id/asset_ids; порядок массива сохраняется. Post: title/image/character_id(необязательно). Comment: body≤2000. PUT/DELETE like идемпотентны. Review: decision=approve/reject/request-revision, review_comment обязателен при отказе/возврате. См. business-rules.md.

Полные поля ответов определены в app/Http/Resources; пароли/хеши не возвращаются. Токен отдаётся только при входе/регистрации. Срок1440минут, настройка SANCTUM_EXPIRATION.


## Схемы объектов ответа

User

~~~json
{
  "id": 2,
  "name": "Креатор ICC",
  "email": "creator@icc.test",
  "role": "creator",
  "is_blocked": false,
  "created_at": "2026-11-11T00:00:00Z"
}
~~~

Maker (карточка; assets[] при загрузке связи)

~~~json
{
  "id": 1,
  "user_id": 2,
  "title": "Лунный путешественник",
  "description": "Набор PNG 512×512",
  "status": "approved",
  "creator": {
    "id": 2,
    "name": "Креатор ICC"
  },
  "submitted_at": "2026-11-11T00:00:00Z",
  "reviewed_at": "2026-11-11T00:00:00Z",
  "created_at": "2026-11-11T00:00:00Z",
  "updated_at": "2026-11-11T00:00:00Z",
  "assets": [
    {
      "id": 1,
      "maker_id": 1,
      "name": "Основа",
      "category": "body",
      "layer_order": 10,
      "is_active": true,
      "image_url": "http://127.0.0.1:8000/api/assets/1/image"
    }
  ]
}
~~~

Asset

~~~json
{
  "id": 1,
  "maker_id": 1,
  "name": "Основа",
  "category": "body",
  "layer_order": 10,
  "is_active": true,
  "image_url": "http://127.0.0.1:8000/api/assets/1/image"
}
~~~

Character

~~~json
{
  "id": 1,
  "user_id": 3,
  "maker_id": 1,
  "name": "Мой герой",
  "maker": {
    "id": 1,
    "user_id": 2,
    "title": "Лунный путешественник",
    "description": "Набор PNG 512×512",
    "status": "approved",
    "creator": {
      "id": 2,
      "name": "Креатор ICC"
    },
    "submitted_at": "2026-11-11T00:00:00Z",
    "reviewed_at": "2026-11-11T00:00:00Z",
    "created_at": "2026-11-11T00:00:00Z",
    "updated_at": "2026-11-11T00:00:00Z"
  },
  "assets": [
    {
      "id": 1,
      "maker_id": 1,
      "name": "Основа",
      "category": "body",
      "layer_order": 10,
      "is_active": true,
      "image_url": "http://127.0.0.1:8000/api/assets/1/image",
      "selected_layer_order": 0
    }
  ],
  "created_at": "2026-11-11T00:00:00Z",
  "updated_at": "2026-11-11T00:00:00Z"
}
~~~

Post (карточка)

~~~json
{
  "id": 1,
  "user_id": 3,
  "character_id": 1,
  "title": "Моя работа",
  "is_visible": true,
  "image_url": "http://127.0.0.1:8000/api/posts/1/image",
  "author": {
    "id": 3,
    "name": "Пользователь ICC"
  },
  "likes_count": 1,
  "comments_count": 1,
  "comments": [
    {
      "id": 1,
      "user_id": 3,
      "post_id": 1,
      "body": "Отличный образ!",
      "author": {
        "id": 3,
        "name": "Пользователь ICC"
      },
      "created_at": "2026-11-11T00:00:00Z"
    }
  ],
  "created_at": "2026-11-11T00:00:00Z"
}
~~~

Comment

~~~json
{
  "id": 1,
  "user_id": 3,
  "post_id": 1,
  "body": "Отличный образ!",
  "author": {
    "id": 3,
    "name": "Пользователь ICC"
  },
  "created_at": "2026-11-11T00:00:00Z"
}
~~~

Связанные объекты и подсчёты выводятся только при загрузке соответствующих отношений; поля review_comment/reviewed_by — автору/admin. Примеры описывают формат, а не фактический ответ каждого запроса.
