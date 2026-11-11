# Физическая модель ICC

MySQL 8.4 / InnoDB / utf8mb4. Семь основных сущностей и pivot asset_character. Служебные таблицы: migrations, password_reset_tokens, sessions, personal_access_tokens.

1НФ: поля атомарны, выбранные элементы вынесены в pivot. 2НФ: свойства Asset не дублируются в pivot, порядок зависит от всей пары ключей. 3НФ: имена авторов и свойства мейкеров получаются по FK, без транзитивного дублирования. КП2 уже задаёт нормализованную модель; физическая реализация добавляет типы, ограничения и индексы.

UNIQUE users.email; UNIQUE likes(user_id,post_id); составной PK asset_character(asset_id,character_id). Роли: user/creator/admin. Статусы: draft/pending/approved/revision/rejected/hidden. Категории: background/body/clothes/face/hair/accessory. Enum проверяет приложение, SQL хранит VARCHAR.

RESTRICT защищает используемых владельцев и мейкеры; CASCADE удаляет зависимые строки; SET NULL сохраняет пост при удалении персонажа и мейкер при удалении модератора. Дополнительный запрет удаления используемого Asset реализован в сервисе.

Индексы: makers.status, makers.title, makers(user_id,status), assets.category, assets(maker_id,layer_order), posts(is_visible,created_at), comments(post_id,created_at). LIKE %строка% не использует B-tree title эффективно; большому каталогу нужен полнотекстовый поиск.


## users

| Поле | MySQL | Laravel | Ограничения |
|---|---|---|---|
| id | BIGINT UNSIGNED | id() | PK; NOT NULL |
| name | VARCHAR(255) | string('name',255) | NOT NULL |
| email | VARCHAR(255) | string('email',255) | NOT NULL; UNIQUE |
| email_verified_at | TIMESTAMP | timestamp('email_verified_at') | NULL |
| password | VARCHAR(255) | string('password',255) | NOT NULL |
| remember_token | VARCHAR(100) | string('remember_token',100) | NULL |
| created_at | TIMESTAMP | timestamps() | NULL |
| updated_at | TIMESTAMP | timestamps() | NULL |
| role | VARCHAR(20) | string('role',20) | NOT NULL; DEFAULT 'user' |
| is_blocked | BOOLEAN | boolean('is_blocked') | NOT NULL; DEFAULT '0' |



## makers

| Поле | MySQL | Laravel | Ограничения |
|---|---|---|---|
| id | BIGINT UNSIGNED | id() | PK; NOT NULL |
| user_id | BIGINT UNSIGNED | foreignId('user_id') | NOT NULL; FK → users.id; ON DELETE RESTRICT |
| reviewed_by | BIGINT UNSIGNED | foreignId('reviewed_by') | NULL; FK → users.id; ON DELETE SET NULL |
| title | VARCHAR(255) | string('title',255) | NOT NULL |
| description | TEXT | text('description') | NOT NULL |
| status | VARCHAR(20) | string('status',20) | NOT NULL; DEFAULT 'draft' |
| review_comment | TEXT | text('review_comment') | NULL |
| submitted_at | DATETIME | dateTime('submitted_at') | NULL |
| reviewed_at | DATETIME | dateTime('reviewed_at') | NULL |
| created_at | TIMESTAMP | timestamps() | NULL |
| updated_at | TIMESTAMP | timestamps() | NULL |



## assets

| Поле | MySQL | Laravel | Ограничения |
|---|---|---|---|
| id | BIGINT UNSIGNED | id() | PK; NOT NULL |
| maker_id | BIGINT UNSIGNED | foreignId('maker_id') | NOT NULL; FK → makers.id; ON DELETE CASCADE |
| name | VARCHAR(255) | string('name',255) | NOT NULL |
| category | VARCHAR(30) | string('category',30) | NOT NULL |
| file_path | VARCHAR(255) | string('file_path',255) | NOT NULL |
| layer_order | INT UNSIGNED | unsignedInteger('layer_order') | NOT NULL; DEFAULT '0' |
| is_active | BOOLEAN | boolean('is_active') | NOT NULL; DEFAULT '1' |
| created_at | TIMESTAMP | timestamps() | NULL |
| updated_at | TIMESTAMP | timestamps() | NULL |



## characters

| Поле | MySQL | Laravel | Ограничения |
|---|---|---|---|
| id | BIGINT UNSIGNED | id() | PK; NOT NULL |
| user_id | BIGINT UNSIGNED | foreignId('user_id') | NOT NULL; FK → users.id; ON DELETE RESTRICT |
| maker_id | BIGINT UNSIGNED | foreignId('maker_id') | NOT NULL; FK → makers.id; ON DELETE RESTRICT |
| name | VARCHAR(255) | string('name',255) | NOT NULL |
| created_at | TIMESTAMP | timestamps() | NULL |
| updated_at | TIMESTAMP | timestamps() | NULL |



## asset_character

| Поле | MySQL | Laravel | Ограничения |
|---|---|---|---|
| asset_id | BIGINT UNSIGNED | foreignId('asset_id') | PK; NOT NULL; FK → assets.id; ON DELETE CASCADE |
| character_id | BIGINT UNSIGNED | foreignId('character_id') | PK; NOT NULL; FK → characters.id; ON DELETE CASCADE |
| layer_order | INT UNSIGNED | unsignedInteger('layer_order') | NOT NULL; DEFAULT '0' |
| created_at | TIMESTAMP | timestamps() | NULL |
| updated_at | TIMESTAMP | timestamps() | NULL |



## posts

| Поле | MySQL | Laravel | Ограничения |
|---|---|---|---|
| id | BIGINT UNSIGNED | id() | PK; NOT NULL |
| user_id | BIGINT UNSIGNED | foreignId('user_id') | NOT NULL; FK → users.id; ON DELETE RESTRICT |
| character_id | BIGINT UNSIGNED | foreignId('character_id') | NULL; FK → characters.id; ON DELETE SET NULL |
| title | VARCHAR(255) | string('title',255) | NOT NULL |
| image_path | VARCHAR(255) | string('image_path',255) | NOT NULL |
| is_visible | BOOLEAN | boolean('is_visible') | NOT NULL; DEFAULT '1' |
| created_at | TIMESTAMP | timestamps() | NULL |
| updated_at | TIMESTAMP | timestamps() | NULL |



## comments

| Поле | MySQL | Laravel | Ограничения |
|---|---|---|---|
| id | BIGINT UNSIGNED | id() | PK; NOT NULL |
| user_id | BIGINT UNSIGNED | foreignId('user_id') | NOT NULL; FK → users.id; ON DELETE CASCADE |
| post_id | BIGINT UNSIGNED | foreignId('post_id') | NOT NULL; FK → posts.id; ON DELETE CASCADE |
| body | TEXT | text('body') | NOT NULL |
| created_at | TIMESTAMP | timestamps() | NULL |
| updated_at | TIMESTAMP | timestamps() | NULL |



## likes

| Поле | MySQL | Laravel | Ограничения |
|---|---|---|---|
| id | BIGINT UNSIGNED | id() | PK; NOT NULL |
| user_id | BIGINT UNSIGNED | foreignId('user_id') | NOT NULL; FK → users.id; ON DELETE CASCADE; UNIQUE(user_id,post_id) |
| post_id | BIGINT UNSIGNED | foreignId('post_id') | NOT NULL; FK → posts.id; ON DELETE CASCADE; UNIQUE(user_id,post_id) |
| created_at | TIMESTAMP | timestamps() | NULL |
| updated_at | TIMESTAMP | timestamps() | NULL |
