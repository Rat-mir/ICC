# Фактическая проверка

11 ноября2026. PHP8.4.19, Laravel13.40.0, PHPUnit12.5.43.

- SQLite-миграции и DatabaseSeeder выполнены успешно:10 users,10 makers,60 assets,10 characters,60 pivot,10 posts,10 comments,10 likes.
- 37 тестов,178 проверок: все пройдены. 33 Feature ICC,3 Unit Enum,1 проверка запуска.
- node --check client/app.js и client/api.js: ошибок нет.
- route:list выгружен;5 SELECT с JOIN выполнены на демонстрационной БД.

Фактическая успешная команда из корня:

    php -d auto_prepend_file=vendor/autoload.php -d sys_temp_dir=<папка проекта>/storage/framework vendor/phpunit/phpunit/phpunit -c phpunit.local.xml --colors=never --testdox --log-junit docs/test-results.xml

phpunit.local.xml отличается от стандартного только отсутствием bootstrap: is_readable в изолированной Windows-среде ошибочно считает доступные файлы нечитаемыми. Автозагрузчик подключён до PHPUnit. Исходники Laravel/PHPUnit не менялись.

Стандартный artisan test не завершился из-за временных файлов дочернего процесса Symfony. Успешный прогон выполнен напрямую PHPUnit. Исходный phpunit.xml сохранён для обычной среды.

Ограничения: сетевой доступ для loopback не предоставлен. PHP-серверы стартовали, но подключения заблокированы. Поэтому реальный браузерный прогон, DevTools Network, скриншоты страниц и ручной Postman не выполнены. JavaScript проверен синтаксически. Картинка test-output.png — рендер реального вывода тестов, не скриншот.

MySQL8.4.8 инициализирован отдельно, но перезапуск блокируется Windows-проверкой writable. DDL и миграции именно на MySQL не подтверждены; подготовленные SQL нужно прогнать в обычном Open Server. SQLite не подтверждает конкурентные блокировки InnoDB.

Коллекция Postman подготовлена, но не опубликована. Google Classroom не изменялся: файлы не загружались, задания не отмечались выполненными. Удалённый Git-репозиторий не публиковался.
