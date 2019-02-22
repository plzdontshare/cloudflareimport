Cloudflare Domain Import
=
Поможет импортировать ваши домены в Cloudflare

Установка
--
Если вы знаете что такое [composer](http://getcomposer.org), то просто выполните команду:
```bash
$ composer install
```
Иначе распакуйте архив build.zip в отдельную папку и переходит к настройке.

Настройка
--
Для начала переименуйте файл `config.php.example` в `config.php`.

Все манипуляции проводятся в файле `config.php`:
```php
<?php

return [
    'cloudflare' => [
        'email' => '',
        'key'   => '',
    ],
];
```

По пунктам:
- `email` - Это ваш email в Cloudflare. Обязателен.
- `key` - API ключ, который вы можете получить на странице https://www.cloudflare.com/a/profile нажав на кнопку "View API Key" напротив строчки "Global API Key".

После настройки `config.php` можно начинать работать.
Скрипт запускается из консоли командой:
```
php cloud.php <command>
```

Где `<command>` - одна из доступных команд.

Доступны следующие команды:
- version - Показывает текущую версию скрипта
- show-domains - Возвращает список всех доменов добавленных в CloudFlare аккаунт
- add-domains - Добавить домены
- add-subdomains - Добавить сабдомены
- remove-domains - Удалить домены из аккаунта
- change-ip - Смена IP у добавленных доменов (у всех DNS записей для каждого домена)

### version
Пример запуска:
```
php cloud.php version
```

### show-domains
Доступные параметры:
- --save-to (-s) - позволяет указать имя файла куда будет записан результат (CSV файл)

Примеры запуска:
```
# Обычный
php cloud.php show-domains

# С сохранением в файл
php cloud.php show-domains --save-to my-domains.csv
```
### add-domains
Доступные аргументы:
- filename - имя файла в котором хранятся домены (по-умолчанию: domains.txt)

Доступные параметры:
- --ip - IP на который будут прикрепляться домены (не обязателен)
- --wildcard (-w) - Если указан, то для каждого домена будет добавлена wildcard (*) DNS запись
- --skip-existing (-s) - Если указан, то скрипт не будет пытаться добавить DNS записи для доменов которые уже добавлены в аккаунт
- --enable-proxy (-p) - Если указан, то скрипт будет включать проксирование для добавленных DNS записей
- --enable-always-online - Если указан, то скрипт не будет отключать AlwaysOnline для доменов. (по-умолчанию скрипт отключает эту настройку)

Если не указан параметр `--ip`, то IP должен быть указан для КАЖДОГО домена в файле с доменами (в формате `домен|ip`).
Пример такого файла:
```
domain1.com|127.0.0.1
domain2.com|127.0.0.1
domain3.com|127.0.0.2
```

Примеры запуска:
```
# Простой (домены лежат в файле domains.txt)
php cloud.php add-domains --enable-proxy --ip "127.0.0.1"

# С включенным wildcard
php cloud.php add-domains --enable-proxy -w
# или так
php cloud.php add-domains --enable-proxy --wildcard

# Кастомный файл с доменами
php cloud.php add-domains freenom-domains.txt 
``` 

### add-subdomains
Доступные аргументы:
- filename - имя файла в котором хранятся домены (по-умолчанию: domains.txt)

Доступные параметры:
- --ip - IP на который будут прикрепляться домены (не обязателен)
- --enable-proxy (-p) - Если указан, то скрипт будет включать проксирование для добавленных DNS записей

Если не указан параметр `--ip`, то действуют такие же правила как для команды `add-domains`.
Сабдомены так же указываются в файле с доменами через запятую:
```
domain1.com|127.0.0.1|www,mobile,network
domain2.com||wp,wordpress,www
domain3.com||www1,www2,www3
```

(если в файле с домена опущен IP, то должен быть обязательно указан параметр `--ip` при вызове скрипта)

Примеры запуска:
```
# Простой (домены лежат в файле domains.txt)
php cloud.php add-subdomains --enable-proxy

# С указание IP
php cloud.php add-subdomains --ip "127.0.0.1" --enable-proxy
``` 

### remove-domains
Доступные аргументы:
- filename - имя файла в котором хранятся домены (по-умолчанию: domains.txt)

Примеры запуска:
```
php cloud.php remove-domains domains.txt
```

### change-ip
Доступные аргументы:
- filename - имя файла в котором хранятся домены (по-умолчанию: domains.txt)

Доступные параметры:
- --ip - IP на который будут прикрепляться домены (не обязателен)

(для IP работают такие же правила как и в командах `add-domains` и `add-subdomains`)

Примеры запуска:
```
php cloud.php change-ip --ip "127.0.0.1"
```

