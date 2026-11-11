# Postman / Newman — фактические HTTP-запросы

30 запросов, 35 проверок, ошибок нет. Выполнено runner Newman; токены в отчёт не записывались.

| Проверка | Метод | Запрос | Статус |
|---|---|---|---|
| 01 Health MySQL | GET | /api/health | 200 |
| 02 Without token | GET | /api/characters | 401 |
| 03 Creator login | POST | /api/login | 200 |
| 04 Create maker | POST | /api/makers | 201 |
| 05 Upload body | POST | /api/makers/12/assets | 201 |
| 05 Upload face | POST | /api/makers/12/assets | 201 |
| 06 Submit | POST | /api/makers/12/submit | 200 |
| 07 Repeated submit conflict | POST | /api/makers/12/submit | 409 |
| 08 Creator cannot moderate | POST | /api/admin/makers/12/review | 403 |
| 09 Admin login | POST | /api/login | 200 |
| 10 Missing review reason | POST | /api/admin/makers/12/review | 422 |
| 11 Return for revision | POST | /api/admin/makers/12/review | 200 |
| 12 Correct title | PATCH | /api/makers/12 | 200 |
| 13 Resubmit | POST | /api/makers/12/submit | 200 |
| 14 Approve | POST | /api/admin/makers/12/review | 200 |
| 15 Search and pagination | GET | /api/makers?search=PostmanMySQL1794414541206&category=body&per_page=2&page=1 | 200 |
| 16 User login | POST | /api/login | 200 |
| 17 Create character | POST | /api/characters | 201 |
| 18 Own character | GET | /api/characters/12 | 200 |
| 19 Foreign owner forbidden | DELETE | /api/characters/12 | 403 |
| 20 Publish PNG | POST | /api/posts | 201 |
| 21 Comment | POST | /api/posts/12/comments | 201 |
| 22 Like | PUT | /api/posts/12/like | 200 |
| 23 Repeated like idempotent | PUT | /api/posts/12/like | 200 |
| 24 Unlike | DELETE | /api/posts/12/like | 200 |
| 25 Statistics admin | GET | /api/admin/statistics | 200 |
| 26 Invalid filter | GET | /api/makers?sort=password&per_page=999 | 422 |
| 27 Missing maker | GET | /api/makers/9999999 | 404 |
| 28 Logout | POST | /api/logout | 204 |
| 29 Revoked token | GET | /api/user | 401 |
