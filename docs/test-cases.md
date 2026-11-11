# Тест-кейсы ICC

Результаты получены автоматическими Feature-тестами, не ручным Postman. В тестах используются фабрики и изолированная SQLite-база. Полный журнал — test-output.txt.

| № | Описание | Запрос | Данные | Ожидаемый результат | Фактический результат (Feature) |
|---|---|---|---|---|---|
| 1 | health checks database | GET /api/health | Без тела | 200; database=ok | Пройдено: 200; database=ok |
| 2 | registration hashes password and cannot assign role | POST /api/register | name/email/password/confirmation; затем role=admin | 201 и роль user; с role=admin —422; пароль хеширован | Пройдено: 201 и роль user; с role=admin —422; пароль хеширован |
| 3 | registration rejects duplicate email and bad confirmation | POST /api/register | Существующий email; неверное подтверждение | 422; errors.email и errors.password | Пройдено: 422; errors.email и errors.password |
| 4 | login token user and logout revocation | POST /api/login; GET /api/user; POST /api/logout | Валидный пароль; затем выданный Bearer | 200 вход;200 user;204 выход;401 после отзыва | Пройдено: 200 вход;200 user;204 выход;401 после отзыва |
| 5 | invalid password and missing token are 401 | POST /api/login; GET /api/characters; GET /api/user | Неверный пароль; нет токена; неверный токен | 401 для всех случаев | Пройдено: 401 для всех случаев |
| 6 | blocked user cannot login or use protected api | POST /api/login; GET /api/characters | is_blocked=true | 403 для входа и защищённого запроса | Пройдено: 403 для входа и защищённого запроса |
| 7 | guest catalog excludes private statuses | GET /api/makers | В БД approved/pending/hidden | 200; ровно1 approved; meta.total=1 | Пройдено: 200; ровно1 approved; meta.total=1 |
| 8 | maker creation requires creator role | POST /api/makers | title,description; user и creator | user403; creator201; status=draft и верный user_id | Пройдено: user403; creator201; status=draft и верный user_id |
| 9 | maker owner policy and missing record | PATCH /api/makers/{maker}; GET /api/makers/99999 | Чужой мейкер; отсутствующий ID | 403;404 с message | Пройдено: 403;404 с message |
| 10 | owner sees only own drafts in mine | GET /api/makers?mine=1 | Два черновика разных владельцев | 200; только1 свой | Пройдено: 200; только1 свой |
| 11 | asset png upload and metadata update | POST /api/makers/{maker}/assets; PATCH /api/makers/{maker}/assets/{asset} | name/category/body/layer_order; PNG64×64 | 201; затем200 и layer_order=20 | Пройдено: 201; затем200 и layer_order=20 |
| 12 | non png and wrong dimensions are rejected | POST /api/makers/{maker}/assets | JPEG; PNG8×8; ложный PNG; файл5121КБ | 422; errors.image | Пройдено: 422; errors.image |
| 13 | nested asset cannot be changed through another maker | PATCH /api/makers/{maker}/assets/{asset} | Asset другого мейкера | 404; изменение не выполнено | Пройдено: 404; изменение не выполнено |
| 14 | submit requires body and face and rolls back | POST /api/makers/{maker}/submit | Только слой body, без face | 422; status=draft, submitted_at=null | Пройдено: 422; status=draft, submitted_at=null |
| 15 | submit rejects mismatched canvas sizes | POST /api/makers/{maker}/submit | body64×64 и face128×128 | 422; errors.assets | Пройдено: 422; errors.assets |
| 16 | full revision resubmission and approval scenario | POST /api/makers/{maker}/submit; POST /api/admin/makers/{maker}/review; PATCH /api/makers/{maker} | draft→pending→revision→pending→approved; причина обязательна | 200 на переходах;409 повтор submit/правка pending;422 без причины | Пройдено: 200 на переходах;409 повтор submit/правка pending;422 без причины |
| 17 | non admin cannot review and repeat review is conflict | POST /api/admin/makers/{maker}/review | creator; затем admin с reject и повтор approve | 403;200 rejected;409 повторное решение | Пройдено: 403;200 rejected;409 повторное решение |
| 18 | approved edit returns to draft and removes from catalog | PATCH /api/makers/{maker}; GET /api/makers | Изменение описания approved | 200; status=draft; в каталоге0 записей | Пройдено: 200; status=draft; в каталоге0 записей |
| 19 | private png not available to guests | GET /api/assets/{asset}/image | Гость; draft, затем approved | 403 для draft;200 image/png для approved | Пройдено: 403 для draft;200 image/png для approved |
| 20 | character crud and ordered many to many | POST/GET/PUT/DELETE /api/characters/{character} | name/maker_id/asset_ids с перестановкой | 201/200/200/204; pivot сохраняет порядок и удаляется | Пройдено: 201/200/200/204; pivot сохраняет порядок и удаляется |
| 21 | foreign or duplicate category assets do not create character | POST /api/characters | Asset чужого мейкера; два body | 422; нет новых characters и pivot | Пройдено: 422; нет новых characters и pivot |
| 22 | character from unapproved maker is conflict | POST /api/characters | Неодобренный maker | 409 | Пройдено: 409 |
| 23 | foreign character is forbidden and list is private | GET/DELETE /api/characters/{character}; GET /api/characters | Токен чужого владельца | 403;403; пустой свой список | Пройдено: 403;403; пустой свой список |
| 24 | post upload comments likes and deletion | POST /api/posts; POST comments; PUT/DELETE like; DELETE post | PNG64×64,title,body; повтор PUT like | 201;201; один лайк при повторе; снятие0;204 удаления | Пройдено: 201;201; один лайк при повторе; снятие0;204 удаления |
| 25 | post cannot reference someone elses character | POST /api/posts | Чужой character_id; корректный PNG | 403 | Пройдено: 403 |
| 26 | comment validation and ownership | POST /api/posts/{post}/comments; DELETE comment/post | Пустой body; чужие объекты | 422 body;403 для чужих удалений | Пройдено: 422 body;403 для чужих удалений |
| 27 | hidden posts are absent from public feed and image | GET /api/posts; GET /api/posts/{post}; GET image | is_visible=false, гость | Пустая лента;404 карточка и файл | Пройдено: Пустая лента;404 карточка и файл |
| 28 | admin hide statistics and user management | POST /api/admin/makers/{maker}/hide; POST /api/admin/posts/{post}/hide; GET statistics; PATCH users | Admin; роль creator; блокировка; самоблокировка | 200 hidden;200 false; верная статистика;200 изменения;409 себе | Пройдено: 200 hidden;200 false; верная статистика;200 изменения;409 себе |
| 29 | filter sort and pagination with meta | GET /api/makers?search=Лунный&category=body&sort=title&order=asc&per_page=2&page=2 | 5 совпадений; затем sort=password/per_page=999 | 200;2 строки,total5,page2,last_page3;422 плохие фильтры | Пройдено: 200;2 строки,total5,page2,last_page3;422 плохие фильтры |
| 30 | referenced maker and asset deletion return 409 | DELETE /api/makers/{maker}; DELETE /api/makers/{maker}/assets/{asset} | Есть сохранённый Character с выбранным Asset | 409 в обоих случаях; строки сохранены | Пройдено: 409 в обоих случаях; строки сохранены |
| 31 | cors preflight allows only configured client | OPTIONS /api/makers | Origin127.0.0.1:5173, затем untrusted.example | 204; ACAO только для разрешённого origin | Пройдено: 204; ACAO только для разрешённого origin |
| 32 | api error does not expose stack or sql | GET /api/makers/not-an-id | APP_DEBUG=false | 404; нет trace и exception в JSON | Пройдено: 404; нет trace и exception в JSON |
| 33 | database unique like constraint | SQL INSERT likes | Повторная пара user_id/post_id | QueryException; UNIQUE защищает целостность | Пройдено: QueryException; UNIQUE защищает целостность |
