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
    'wildcard'             => true,
    'server_ip'            => '',
    'proxy'                => true,
    'do_not_skip_existing' => true,
];
```

По пунктам:
- `email` - Это ваш email в Cloudflare. Обязателен.
- `key` - API ключ, который вы можете получить на странице https://www.cloudflare.com/a/profile нажав на кнопку "View API Key" напротив строчки "Global API Key".
- `wildcard` - Принимает значения `true/false`. Если выбрано `true`, то скрипт будет создавать Wildcard DNS записи, иначе не будет.
- `server_ip` - IP сервера на который вы прикрепляете ваши домены. Вписываем в формате `127.0.0.1`
- `proxy` - Включить проксирование для основной записи (domain.com)? `true/faslse` - вкл/выкл. (для wildcard проксирование не работает на бесплатных аккаунтах, поэтому скрипт включает проксирование только для основного домена)
- `do_not_skip_existing` - Принимает значения `true/false`. Если `true`, то в случае, если домен уже добавлен, скрипт попробует добавить зоны к нему. При `false`, скрипт пропустить такой домен. (Полезно когда у вас были проблемные домены в zone_errors.csv и вы хотите попробовать добавить их еще раз)

После того как вы настроите `config.php`, вам необходимо добавить ваши домены в файл `domains.txt` (каждый с новой строки):
```
site1.com
site2.com
```

Теперь вам осталось только запустить скрипт командой:
```bash
$ php cloud.php
```

После того, как скрипт закончит свою работу, все успешные домены будут записаны в файл `success.csv`

`success.csv`
```csv
Имя Домена  Zone ID     Server IP   NS
domain.com  1234qwe     127.0.0.1   bob.ns.cloudflare.com,sally.ns.cloudflare.com
```

Ошибки
--
Во время работы в скрипте могут возникать ошибки.
Если ошибка происходит на этапе создания зоны (добавления домена), то этот домен запишется в файл `zone_errors.csv`.
Если же ошибка возникает во время добавления DNS записей, такой домен запишется в файл `dns_errors.csv`

Файлы `zone_errors.csv` и `dns_errors.csv` - это обычные CSV файлы с табом (\t) в качестве разделителя. 

`zone_errors.csv`
```csv
Имя домена  Описание ошибки
domain.com  Domain already exists
domain2.com  Invalid domain
```

`dns_errors.csv`
```csv
Имя домена  Тип DNS Зоны    Описание ошибки
domain.com   A               Some error
domain2.com  *               Another error
```