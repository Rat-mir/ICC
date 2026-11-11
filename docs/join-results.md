# SELECT с JOIN — MySQL 8.4.8

База icc_backend_ddl создана SQL-скриптом schema.mysql.sql и заполнена seed.mysql.sql.

## Запрос 1

~~~sql
SELECT m.id,m.title,u.name AS creator,COUNT(a.id) AS assets_count
FROM makers m JOIN users u ON u.id=m.user_id LEFT JOIN assets a ON a.maker_id=m.id
WHERE m.status='approved' GROUP BY m.id,m.title,u.name ORDER BY m.id;
~~~

| id | title | creator | assets_count |
|---|---|---|---|
| 1 | Лунный путешественник | Креатор ICC | 6 |
| 2 | Лесная мастерская | Креатор ICC | 6 |
| 3 | Сахарные облака | Креатор ICC | 6 |
| 4 | Золотой час | Креатор ICC | 6 |
| 5 | Морской ветер | Креатор ICC | 6 |
| 6 | Тихий сад | Креатор ICC | 6 |

## Запрос 2

~~~sql
SELECT c.id,c.name,m.title,a.name AS asset,a.category,ac.layer_order
FROM characters c JOIN makers m ON m.id=c.maker_id
JOIN asset_character ac ON ac.character_id=c.id JOIN assets a ON a.id=ac.asset_id
WHERE c.id=1 ORDER BY ac.layer_order;
~~~

| id | name | title | asset | category | layer_order |
|---|---|---|---|---|---|
| 1 | Герой 1 | Лунный путешественник | Фон | background | 0 |
| 1 | Герой 1 | Лунный путешественник | Основа | body | 1 |
| 1 | Герой 1 | Лунный путешественник | Одежда | clothes | 2 |
| 1 | Герой 1 | Лунный путешественник | Лицо | face | 3 |
| 1 | Герой 1 | Лунный путешественник | Причёска | hair | 4 |
| 1 | Герой 1 | Лунный путешественник | Очки | accessory | 5 |

## Запрос 3

~~~sql
SELECT p.id,p.title,u.name AS author,COUNT(l.id) AS likes_count
FROM posts p JOIN users u ON u.id=p.user_id LEFT JOIN likes l ON l.post_id=p.id
GROUP BY p.id,p.title,u.name ORDER BY p.id;
~~~

| id | title | author | likes_count |
|---|---|---|---|
| 1 | Мой герой: Лунный путешественник | Пользователь ICC | 1 |
| 2 | Мой герой: Лесная мастерская | Пользователь ICC | 1 |
| 3 | Мой герой: Сахарные облака | Пользователь ICC | 1 |
| 4 | Мой герой: Золотой час | Пользователь ICC | 1 |
| 5 | Мой герой: Морской ветер | Пользователь ICC | 1 |
| 6 | Мой герой: Тихий сад | Давид Романович Щукин | 1 |
| 7 | Мой герой: Лунный путешественник | Никонова Ксения Андреевна | 1 |
| 8 | Мой герой: Лесная мастерская | Злата Александровна Кононова | 1 |
| 9 | Мой герой: Сахарные облака | Леонид Сергеевич Орехов | 1 |
| 10 | Мой герой: Золотой час | Павел Александрович Моисеев | 1 |

## Запрос 4

~~~sql
SELECT m.id,m.title,creator.name AS creator,reviewer.name AS reviewer,m.review_comment
FROM makers m JOIN users creator ON creator.id=m.user_id LEFT JOIN users reviewer ON reviewer.id=m.reviewed_by
WHERE m.status='revision';
~~~

| id | title | creator | reviewer | review_comment |
|---|---|---|---|---|
| 9 | Звёздная сказка | Креатор ICC | Администратор ICC | Добавьте альтернативный вариант причёски. |

## Запрос 5

~~~sql
SELECT c.id,p.title,u.name,c.body FROM comments c JOIN posts p ON p.id=c.post_id JOIN users u ON u.id=c.user_id ORDER BY c.id;
~~~

| id | title | name | body |
|---|---|---|---|
| 1 | Мой герой: Лунный путешественник | Креатор ICC | Спасибо за работу! Образ отлично получился. |
| 2 | Мой герой: Лесная мастерская | Креатор ICC | Спасибо за работу! Образ отлично получился. |
| 3 | Мой герой: Сахарные облака | Креатор ICC | Спасибо за работу! Образ отлично получился. |
| 4 | Мой герой: Золотой час | Креатор ICC | Спасибо за работу! Образ отлично получился. |
| 5 | Мой герой: Морской ветер | Креатор ICC | Спасибо за работу! Образ отлично получился. |
| 6 | Мой герой: Тихий сад | Креатор ICC | Спасибо за работу! Образ отлично получился. |
| 7 | Мой герой: Лунный путешественник | Креатор ICC | Спасибо за работу! Образ отлично получился. |
| 8 | Мой герой: Лесная мастерская | Креатор ICC | Спасибо за работу! Образ отлично получился. |
| 9 | Мой герой: Сахарные облака | Креатор ICC | Спасибо за работу! Образ отлично получился. |
| 10 | Мой герой: Золотой час | Креатор ICC | Спасибо за работу! Образ отлично получился. |
