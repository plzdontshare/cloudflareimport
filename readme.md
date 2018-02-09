Cloudflare Domain Import
=
Поможет импортировать ваши домены в Cloudflare

**Важно: прежде чем вы начнете работать, у всех доменов уже должны быть прописаны NS сервера Cloudflare**

Установка
--
Если вы знаете что такое [composer](http://getcomposer.org), то просто выполните команду:
```bash
$ composer install
```
Иначе распакуйте архив build.zip в отдельную папку и переходит к настройке.

Настройка
--
Все манипуляции проводятся в файле `config.php`:
```php
<?php

return [
    'cloudflare' => [
        'email' => '',
        'key'   => '',
    ],
    'wildcard'  => true,
    'server_ip' => '',
];
```

По пунктам:
- `email` - Это ваш email в Cloudflare. Обязателен.
- `key` - API ключ, который вы можете получить на странице https://www.cloudflare.com/a/profile нажав на кнопку "View API Key" напротив строчки "Global API Key".
- `wildcard` - Принимает значения `true/false`. Если выбрано `true`, то скрипт будет создавать Wildcard DNS записи, иначе не будет.
- `server_ip` - IP сервера на который вы прикрепляете ваши домены. Вписываем в формате `127.0.0.1`

После того как вы настроите `config.php`, вам необходимо добавить ваши домены в файл `domains.txt` (каждый с новой строки).
Теперь вам осталось только запустить скрипт командой:
```bash
$ php cloud.php
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

`dns_errors.txt`
```csv
Имя домена  Тип DNS Зоны    Описание ошибки
domain.com   A               Some error
domain2.com  *               Another error
```